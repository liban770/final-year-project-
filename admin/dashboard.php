<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

/* ===============================
   AUTH CHECK
================================ */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ===============================
   DATABASE CONNECTION
================================ */
require_once "../config/database.php";

if (!isset($pdo)) {
    die("Database connection failed.");
}

/* ===============================
   FETCH COUNTS
================================ */

// General Counts
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$projectCount = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$supervisorCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role='supervisor'")->fetchColumn();

// Project Status Counts
$pending = $pdo->query("SELECT COUNT(*) FROM projects WHERE status='pending'")->fetchColumn();
$approved = $pdo->query("SELECT COUNT(*) FROM projects WHERE status='approved'")->fetchColumn();
$rejected = $pdo->query("SELECT COUNT(*) FROM projects WHERE status='rejected'")->fetchColumn();

// User Role Counts
$students = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$admins = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();

$adminName = htmlspecialchars($_SESSION['name'] ?? 'Admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Raspberry University Theme -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#C2185B',
                        primaryDark: '#880E4F',
                        primaryLight: '#F8BBD0',
                        softBg: '#FFF0F5'
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-softBg min-h-screen">

<div class="flex">

    <!-- ================= SIDEBAR ================= -->
    <aside class="w-64 bg-primaryDark text-white min-h-screen p-6 hidden md:block shadow-xl">

        <h2 class="text-2xl font-bold mb-8 tracking-wide">
          🎓 University Admin
        </h2>

        <nav class="space-y-3 text-sm">

            <a href="dashboard.php" class="block px-4 py-3 rounded-lg bg-primary">
                Dashboard
            </a>

            <a href="assign_supervisor.php" class="block px-4 py-3 rounded-lg hover:bg-primary transition">
                Assign Supervisor
            </a>

            <a href="manage_projects.php" class="block px-4 py-3 rounded-lg hover:bg-primary transition">
                Manage Projects
            </a>

            <a href="schedule_defense.php" class="block px-4 py-3 rounded-lg hover:bg-primary transition">
                Schedule Defense
            </a>

            <a href="send_notification.php" class="block px-4 py-3 rounded-lg hover:bg-primary transition">
                Send Notification
            </a>

            <a href="manage_users.php" class="block px-4 py-3 rounded-lg hover:bg-primary transition">
                Manage Users
            </a>

            <a href="reports.php" class="block px-4 py-3 rounded-lg hover:bg-primary transition">
                Reports
            </a>
            <a href="supervisor_attendance.php" class="block px-4 py-3 rounded-lg hover:bg-primary transition">
                Supervisor Attendance
            </a>
            <a href="supervisor_attendance_report.php" class="block px-4 py-3 rounded-lg hover:bg-primary transition">
                Attendance Report
            </a>

            <a href="../logout.php"
               class="block px-4 py-3 rounded-lg bg-red-600 hover:bg-red-700 text-center mt-6">
                Logout
            </a>

        </nav>
    </aside>

    <!-- ================= MAIN CONTENT ================= -->
    <div class="flex-1 p-8">

        <!-- Header -->
        <div class="mb-10">
            <h1 class="text-3xl font-bold text-gray-800">
                Welcome Admin, <?= $adminName; ?> 👋
            </h1>
            <p class="text-gray-500 mt-2">
                Academic Project Management Dashboard
            </p>
        </div>

        <!-- ================= STAT CARDS ================= -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">

            <div class="bg-white rounded-2xl p-6 shadow-md border-t-4 border-primary hover:shadow-xl transition">
                <h2 class="text-gray-500 text-sm uppercase tracking-wide">Total Users</h2>
                <p class="text-4xl font-bold text-primary mt-4">
                    <?= $userCount; ?>
                </p>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-md border-t-4 border-green-500 hover:shadow-xl transition">
                <h2 class="text-gray-500 text-sm uppercase tracking-wide">Total Projects</h2>
                <p class="text-4xl font-bold text-green-600 mt-4">
                    <?= $projectCount; ?>
                </p>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-md border-t-4 border-indigo-500 hover:shadow-xl transition">
                <h2 class="text-gray-500 text-sm uppercase tracking-wide">Supervisors</h2>
                <p class="text-4xl font-bold text-indigo-600 mt-4">
                    <?= $supervisorCount; ?>
                </p>
            </div>

        </div>

        <!-- ================= CHARTS ================= -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <div class="bg-white shadow-md rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">
                    Projects by Status
                </h2>
                <canvas id="projectChart"></canvas>
            </div>

            <div class="bg-white shadow-md rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">
                    Users by Role
                </h2>
                <canvas id="userChart"></canvas>
            </div>

        </div>

        <!-- Reports Button -->
        <div class="mt-12">
            <a href="reports.php"
               class="inline-block bg-primary hover:bg-primaryDark text-white px-8 py-3 rounded-xl shadow-lg transition">
                View Full Reports
            </a>
        </div>

    </div>
</div>

<!-- ================= CHART JS ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const projectChart = new Chart(document.getElementById('projectChart'), {
    type: 'bar',
    data: {
        labels: ['Pending', 'Approved', 'Rejected'],
        datasets: [{
            label: 'Projects',
            data: [<?= $pending ?>, <?= $approved ?>, <?= $rejected ?>],
            backgroundColor: ['#FACC15', '#22C55E', '#EF4444'],
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});

const userChart = new Chart(document.getElementById('userChart'), {
    type: 'doughnut',
    data: {
        labels: ['Students', 'Supervisors', 'Admins'],
        datasets: [{
            data: [<?= $students ?>, <?= $supervisorCount ?>, <?= $admins ?>],
            backgroundColor: ['#C2185B', '#7C3AED', '#F97316']
        }]
    },
    options: {
        responsive: true
    }
});
</script>

</body>
</html>