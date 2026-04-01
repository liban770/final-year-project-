<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../login.php");
    exit();
}

$supervisor_id = $_SESSION['user_id'];

// Fetch notifications for this supervisor
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 30");
$stmt->execute([$supervisor_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark all as read
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")
    ->execute([$supervisor_id]);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Supervisor Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FDE8EF] min-h-screen">
<div class="flex min-h-screen">
    <div class="w-64 bg-[#C2185B] text-white p-6">
        <h2 class="text-2xl font-bold mb-8 text-center">Supervisor Panel</h2>
        <a href="dashboard.php" class="block bg-[#7A002B] hover:bg-[#5F0021] px-4 py-2 rounded-lg mb-3 transition">Dashboard</a>
        <a href="notifications.php" class="block bg-[#E30B5C] hover:bg-[#c0094e] px-4 py-2 rounded-lg mb-3 transition">Notifications</a>
        <a href="../logout.php" class="block bg-[#E30B5C] hover:bg-[#c0094e] text-center px-4 py-2 rounded-lg transition">Logout</a>
    </div>
    <div class="flex-1 p-10">
        <h1 class="text-3xl font-bold text-[#9B0036] mb-8">Notifications</h1>
        <div class="bg-white rounded-2xl shadow-lg p-8 max-w-2xl mx-auto">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $note): ?>
                    <div class="border-b py-4">
                        <p><?= htmlspecialchars($note['message']) ?></p>
                        <small class="text-gray-500">
                            <?= date('d M Y H:i', strtotime($note['created_at'])) ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500">No notifications available.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
