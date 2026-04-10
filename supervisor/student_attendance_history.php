<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../login.php");
    exit();
}

$supervisor_id = (int)$_SESSION['user_id'];
$recordsStmt = $pdo->prepare("
    SELECT sa.attendance_date, sa.status, COALESCE(u.name, sa.student_name) AS student_name, pg.group_name, pg.group_code
    FROM student_attendance sa
    LEFT JOIN users u ON sa.student_id = u.id
    JOIN project_groups pg ON sa.group_id = pg.id
    WHERE sa.supervisor_id = ?
    ORDER BY sa.attendance_date DESC, pg.group_name ASC, student_name ASC
");
$recordsStmt->execute([$supervisor_id]);
$records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Attendance History</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FDE8EF] min-h-screen">
<div class="max-w-6xl mx-auto p-8">
    <h1 class="text-3xl font-bold text-[#9B0036] mb-6">Student Attendance History</h1>
    <div class="bg-white rounded-xl shadow p-6">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Date</th>
                    <th class="text-left py-2">Group</th>
                    <th class="text-left py-2">Student</th>
                    <th class="text-left py-2">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                    <tr class="border-b">
                        <td class="py-2"><?= htmlspecialchars($row['attendance_date']); ?></td>
                        <td class="py-2"><?= htmlspecialchars($row['group_name'] . ' (' . $row['group_code'] . ')'); ?></td>
                        <td class="py-2"><?= htmlspecialchars($row['student_name']); ?></td>
                        <td class="py-2">
                            <span class="<?= $row['status'] === 'present' ? 'text-green-600' : 'text-red-600'; ?>">
                                <?= htmlspecialchars(ucfirst($row['status'])); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
