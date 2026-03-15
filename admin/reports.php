<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ================= USERS ================= */
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$total_supervisors = $pdo->query("SELECT COUNT(*) FROM users WHERE role='supervisor'")->fetchColumn();

/* ================= PROJECTS ================= */
$total_projects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$approved_projects = $pdo->query("SELECT COUNT(*) FROM projects WHERE status='approved'")->fetchColumn();
$pending_projects = $pdo->query("SELECT COUNT(*) FROM projects WHERE status='pending'")->fetchColumn();
$rejected_projects = $pdo->query("SELECT COUNT(*) FROM projects WHERE status='rejected'")->fetchColumn();

/* ================= DEFENSE ================= */
$total_defense = $pdo->query("SELECT COUNT(*) FROM defense_schedule")->fetchColumn();

?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Reports</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

<div class="flex flex-col md:flex-row">

<!-- SIDEBAR -->
<aside class="w-full md:w-64 bg-[#880E4F] text-white p-6">

<h2 class="text-2xl font-bold mb-8 border-b pb-3">

🎓 University Admin
</h2>

<nav class="space-y-3">
    

<a href="dashboard.php" class="block px-3 py-2 rounded hover:bg-[#C2185B]">
Dashboard
</a>

<a href="reports.php" class="block px-3 py-2 rounded bg-[#C2185B] font-semibold">
Reports
</a>

<a href="download_report.php"
class="block px-3 py-2 rounded bg-green-600 hover:bg-green-700 text-center">
⬇ Download PDF Report
</a>

<a href="../logout.php"
class="block px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-center">
Logout
</a>

</nav>

</aside>

<!-- MAIN CONTENT -->
<div class="flex-1 p-6 md:p-10">

<h1 class="text-3xl font-bold text-[#880E4F] mb-2">
University Project System Report
</h1>

<p class="text-gray-500 mb-10">
Academic overview of students, supervisors and final year projects.
</p>

<!-- SUMMARY -->
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

<div class="bg-white rounded-xl shadow p-6 text-center">
<p class="text-gray-500">Total Users</p>
<h2 class="text-4xl font-bold text-[#880E4F] mt-2">
<?= $total_users ?>
</h2>
</div>

<div class="bg-white rounded-xl shadow p-6 text-center">
<p class="text-gray-500">Students</p>
<h2 class="text-4xl font-bold text-green-600 mt-2">
<?= $total_students ?>
</h2>
</div>

<div class="bg-white rounded-xl shadow p-6 text-center">
<p class="text-gray-500">Supervisors</p>
<h2 class="text-4xl font-bold text-blue-600 mt-2">
<?= $total_supervisors ?>
</h2>
</div>

<div class="bg-white rounded-xl shadow p-6 text-center">
<p class="text-gray-500">Defense Scheduled</p>
<h2 class="text-4xl font-bold text-purple-600 mt-2">
<?= $total_defense ?>
</h2>
</div>

</div>

<!-- PROJECT REPORT -->
<div class="bg-white rounded-xl shadow p-8">

<h2 class="text-xl font-semibold text-[#880E4F] mb-6">
Project Status Analysis
</h2>

<div class="grid md:grid-cols-3 gap-6">

<div class="bg-green-50 p-6 rounded-lg text-center">
<p class="text-green-700 font-medium">Approved Projects</p>
<h3 class="text-3xl font-bold text-green-600 mt-2">
<?= $approved_projects ?>
</h3>
</div>

<div class="bg-yellow-50 p-6 rounded-lg text-center">
<p class="text-yellow-700 font-medium">Pending Projects</p>
<h3 class="text-3xl font-bold text-yellow-600 mt-2">
<?= $pending_projects ?>
</h3>
</div>

<div class="bg-red-50 p-6 rounded-lg text-center">
<p class="text-red-700 font-medium">Rejected Projects</p>
<h3 class="text-3xl font-bold text-red-600 mt-2">
<?= $rejected_projects ?>
</h3>
</div>

</div>

</div>

<!-- ACADEMIC SUMMARY -->
<div class="bg-white rounded-xl shadow p-8 mt-10">

<h2 class="text-xl font-semibold text-[#880E4F] mb-4">
Academic Summary
</h2>

<p class="text-gray-600 leading-relaxed">
This report provides an overview of the Final Year Project Management
System. It summarizes the number of registered users including students
and supervisors, the total projects submitted, and the current approval
status of each project.

The system also tracks defense schedules to ensure smooth project
presentations and evaluation. These statistics help the university
administration monitor academic progress and improve project
management efficiency.
</p>

</div>

</div>
</div>

</body>
</html>