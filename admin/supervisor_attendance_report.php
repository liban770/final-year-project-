<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$records = $pdo->query("
    SELECT sa.attendance_date, sa.status, u.name AS supervisor_name, a.name AS marked_by
    FROM supervisor_attendance sa
    JOIN users u ON sa.supervisor_id = u.id
    LEFT JOIN users a ON sa.marked_by_admin_id = a.id
    ORDER BY sa.attendance_date DESC, u.name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Supervisor Attendance Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
<div class="max-w-6xl mx-auto p-8">
    <h1 class="text-3xl font-bold text-[#880E4F] mb-6">Supervisor Attendance Report</h1>
    <div class="bg-white rounded-xl shadow p-6">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Date</th>
                    <th class="text-left py-2">Supervisor</th>
                    <th class="text-left py-2">Status</th>
                    <th class="text-left py-2">Marked By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                    <tr class="border-b">
                        <td class="py-2"><?= htmlspecialchars($row['attendance_date']); ?></td>
                        <td class="py-2"><?= htmlspecialchars($row['supervisor_name']); ?></td>
                        <td class="py-2">
                            <span class="<?= $row['status'] === 'present' ? 'text-green-600' : 'text-red-600'; ?>">
                                <?= htmlspecialchars(ucfirst($row['status'])); ?>
                            </span>
                        </td>
                        <td class="py-2"><?= htmlspecialchars($row['marked_by'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
