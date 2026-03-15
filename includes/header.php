<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";

// Get unread notifications count (if logged in)
$unreadCount = 0;

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $unreadCount = $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard </title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <div class="w-64 bg-[#C2185B] text-white p-6 relative">
        <h2 class="text-2xl font-bold mb-8">Student Panel</h2>

        <ul class="space-y-4">

            <li>
                <a href="dashboard.php"
                   class="block p-2 rounded hover:bg-[#A3154C]">
                   Dashboard
                </a>
            </li>

            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>

                <li>
                    <a href="manage_users.php"
                       class="block p-2 rounded hover:bg-[#A3154C]">
                       Manage Users
                    </a>
                </li>

                <li>
                    <a href="manage_projects.php"
                       class="block p-2 rounded hover:bg-[#A3154C]">
                       Manage Projects
                    </a>
                </li>

                <li>
                    <a href="assign_supervisor.php"
                       class="block p-2 rounded hover:bg-[#A3154C]">
                       Assign Supervisor
                    </a>
                </li>

                <li>
                    <a href="schedule_defense.php"
                       class="block p-2 rounded hover:bg-[#A3154C]">
                       Schedule Defense
                    </a>
                </li>

                <li>
                    <a href="send_notification.php"
                       class="block p-2 rounded hover:bg-[#A3154C]">
                       Send Notification
                    </a>
                </li>

            <?php endif; ?>

            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'student'): ?>
                <li>
                    <a href="create_project.php"
                       class="block p-2 rounded hover:bg-[#A3154C]">
                       Create Project
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <a href="../logout.php"
                   class="block p-2 rounded bg-red-600 hover:bg-red-700">
                   Logout
                </a>
            </li>

        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1">

        <!-- Topbar -->
        <div class="bg-white shadow p-4 flex justify-between items-center">

            <h1 class="text-lg font-semibold">
                Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>
            </h1>

            <!-- Notification Bell -->
            <div class="relative">
                <a href="notifications.php" class="text-2xl relative text-[#C2185B]">
                    🔔

                    <?php if($unreadCount > 0): ?>
                        <span class="absolute -top-2 -right-3 bg-[#C2185B] text-white text-xs px-2 py-1 rounded-full">
                            <?php echo $unreadCount; ?>
                        </span>
                    <?php endif; ?>

                </a>
            </div>

        </div>

        <!-- Page Content -->
        <div class="p-10">