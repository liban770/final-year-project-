<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../login.php");
    exit();
}

$supervisor_id = (int)$_SESSION['user_id'];
$message = "";
$selected_group_id = isset($_POST['group_id']) ? (int)$_POST['group_id'] : 0;
$selected_date = $_POST['attendance_date'] ?? date('Y-m-d');

$groupsStmt = $pdo->prepare("
    SELECT DISTINCT pg.id, pg.group_name, pg.group_code
    FROM projects p
    JOIN project_groups pg ON p.group_id = pg.id
    WHERE p.supervisor_id = ?
    ORDER BY pg.group_name ASC
");
$groupsStmt->execute([$supervisor_id]);
$groups = $groupsStmt->fetchAll(PDO::FETCH_ASSOC);

$students = [];
if ($selected_group_id > 0) {
    // Fetch Registered
    $studentsStmt = $pdo->prepare("
        SELECT id, name, email
        FROM users
        WHERE role = 'student' AND group_id = ?
        ORDER BY name ASC
    ");
    $studentsStmt->execute([$selected_group_id]);
    $registered = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($registered as $reg) {
        $students[] = [
            'type' => 'registered',
            'id' => $reg['id'],
            'name' => $reg['name'],
            'email' => $reg['email']
        ];
    }
    
    // Fetch Unregistered
    $groupInfoStmt = $pdo->prepare("SELECT member_names FROM project_groups WHERE id = ?");
    $groupInfoStmt->execute([$selected_group_id]);
    $groupInfo = $groupInfoStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!empty($groupInfo['member_names'])) {
        $normalizedNames = str_replace(array("\r\n", "\r", "\n", ";"), ',', $groupInfo['member_names']);
        $typedNames = explode(',', $normalizedNames);
        foreach ($typedNames as $index => $rawName) {
            $cleanName = trim($rawName);
            if (!empty($cleanName)) {
                $students[] = [
                    'type' => 'unregistered',
                    'id' => 'unreg_' . md5($cleanName . $index),
                    'name' => $cleanName,
                    'email' => 'N/A'
                ];
            }
        }
    }
}

if (isset($_POST['save_attendance']) && $selected_group_id > 0 && !empty($selected_date)) {
    $checkGroupStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM projects
        WHERE group_id = ? AND supervisor_id = ?
    ");
    $checkGroupStmt->execute([$selected_group_id, $supervisor_id]);
    if ((int)$checkGroupStmt->fetchColumn() === 0) {
        $message = "Invalid group selected.";
    } else {
        $insertRegistered = $pdo->prepare("
            INSERT INTO student_attendance (student_id, supervisor_id, group_id, attendance_date, status)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status), marked_at = NOW()
        ");
        
        $delUnregistered = $pdo->prepare("
            DELETE FROM student_attendance 
            WHERE student_name = ? AND attendance_date = ? AND group_id = ?
        ");
        
        $insertUnregistered = $pdo->prepare("
            INSERT INTO student_attendance (student_name, supervisor_id, group_id, attendance_date, status)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($students as $student) {
            $status = $_POST['status'][$student['id']] ?? 'absent';
            $status = ($status === 'present') ? 'present' : 'absent';
            
            if ($student['type'] === 'registered') {
                $insertRegistered->execute([$student['id'], $supervisor_id, $selected_group_id, $selected_date, $status]);
            } else {
                $delUnregistered->execute([$student['name'], $selected_date, $selected_group_id]);
                $insertUnregistered->execute([$student['name'], $supervisor_id, $selected_group_id, $selected_date, $status]);
            }
        }
        $message = "Attendance saved successfully.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FDE8EF] min-h-screen">
<div class="flex min-h-screen">
    <div class="w-64 bg-[#C2185B] text-white p-6">
        <h2 class="text-2xl font-bold mb-8">Supervisor Panel</h2>
        <a href="dashboard.php" class="block bg-[#7A002B] px-4 py-2 rounded mb-3">Dashboard</a>
        <a href="student_attendance.php" class="block bg-[#E30B5C] px-4 py-2 rounded mb-3">Take Attendance</a>
        <a href="student_attendance_history.php" class="block bg-[#E30B5C] px-4 py-2 rounded mb-3">Attendance History</a>
        <a href="../logout.php" class="block bg-red-600 px-4 py-2 rounded text-center">Logout</a>
    </div>
    <div class="flex-1 p-8">
        <h1 class="text-3xl font-bold text-[#9B0036] mb-6">Take Student Attendance</h1>
        <?php if ($message): ?>
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-6"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" class="bg-white p-6 rounded-xl shadow mb-6">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-2">Group</label>
                    <select name="group_id" required class="w-full border rounded px-3 py-2">
                        <option value="">Select Group</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= (int)$group['id']; ?>" <?= $selected_group_id === (int)$group['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($group['group_name'] . ' (' . $group['group_code'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-2">Date</label>
                    <input type="date" name="attendance_date" value="<?= htmlspecialchars($selected_date); ?>" required class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <button type="submit" class="mt-4 bg-[#C2185B] text-white px-5 py-2 rounded">Load Students</button>
        </form>

        <?php if ($selected_group_id > 0 && $students): ?>
            <form method="POST" class="bg-white p-6 rounded-xl shadow">
                <input type="hidden" name="group_id" value="<?= (int)$selected_group_id; ?>">
                <input type="hidden" name="attendance_date" value="<?= htmlspecialchars($selected_date); ?>">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Student</th>
                            <th class="text-left py-2">Email</th>
                            <th class="text-left py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr class="border-b">
                                <td class="py-2"><?= htmlspecialchars($student['name']); ?></td>
                                <td class="py-2"><?= htmlspecialchars($student['email']); ?></td>
                                <td class="py-2">
                                    <select name="status[<?= htmlspecialchars($student['id']); ?>]" class="border rounded px-2 py-1">
                                        <option value="present">Present</option>
                                        <option value="absent">Absent</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="save_attendance" class="mt-4 bg-[#9B0036] text-white px-5 py-2 rounded">Save Attendance</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
