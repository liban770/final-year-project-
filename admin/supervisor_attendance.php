<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";
$selected_date = $_POST['attendance_date'] ?? date('Y-m-d');

$supervisorsStmt = $pdo->query("
    SELECT id, name, email
    FROM users
    WHERE role = 'supervisor'
    ORDER BY name ASC
");
$supervisors = $supervisorsStmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['save_attendance']) && !empty($selected_date)) {
    $insertStmt = $pdo->prepare("
        INSERT INTO supervisor_attendance (supervisor_id, attendance_date, status, marked_by_admin_id)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE status = VALUES(status), marked_at = NOW(), marked_by_admin_id = VALUES(marked_by_admin_id)
    ");

    foreach ($supervisors as $supervisor) {
        $status = $_POST['status'][$supervisor['id']] ?? 'absent';
        $status = ($status === 'present') ? 'present' : 'absent';
        $insertStmt->execute([(int)$supervisor['id'], $selected_date, $status, (int)$_SESSION['user_id']]);
    }

    $message = "Supervisor attendance saved successfully.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Supervisor Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
<div class="max-w-6xl mx-auto p-8">
    <h1 class="text-3xl font-bold text-[#880E4F] mb-6">Take Supervisor Attendance</h1>
    <?php if ($message): ?>
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-6"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" class="bg-white rounded-xl shadow p-6">
        <label class="block text-sm mb-2">Date</label>
        <input type="date" name="attendance_date" value="<?= htmlspecialchars($selected_date); ?>" required class="border rounded px-3 py-2 mb-4">

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Supervisor</th>
                    <th class="text-left py-2">Email</th>
                    <th class="text-left py-2">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($supervisors as $supervisor): ?>
                    <tr class="border-b">
                        <td class="py-2"><?= htmlspecialchars($supervisor['name']); ?></td>
                        <td class="py-2"><?= htmlspecialchars($supervisor['email']); ?></td>
                        <td class="py-2">
                            <select name="status[<?= (int)$supervisor['id']; ?>]" class="border rounded px-2 py-1">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit" name="save_attendance" class="mt-4 bg-[#880E4F] text-white px-5 py-2 rounded">Save Attendance</button>
    </form>

    <a href="supervisor_attendance_report.php" class="inline-block mt-4 text-[#880E4F] underline">View Attendance Report</a>
</div>
</body>
</html>
