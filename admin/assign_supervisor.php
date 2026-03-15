<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ===============================
   GET ALL PROJECTS + SUPERVISOR NAME
================================ */
$projects = $pdo->query("
    SELECT projects.*, users.name AS student_name, sup.name AS supervisor_name
    FROM projects
    JOIN users ON projects.student_id = users.id
    LEFT JOIN users AS sup ON projects.supervisor_id = sup.id
")->fetchAll(PDO::FETCH_ASSOC);

/* GET SUPERVISORS */
$supervisors = $pdo->query("
    SELECT id, name FROM users WHERE role = 'supervisor'
")->fetchAll(PDO::FETCH_ASSOC);

/* ASSIGN SUPERVISOR */
if (isset($_POST['assign'])) {
    $project_id = $_POST['project_id'];
    $supervisor_id = $_POST['supervisor_id'];

    $stmt = $pdo->prepare("
        UPDATE projects SET supervisor_id = ? WHERE id = ?
    ");
    $stmt->execute([$supervisor_id, $project_id]);

    header("Location: assign_supervisor.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Supervisor</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Raspberry University Theme -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#C2185B',
                        primaryDark: '#880E4F',
                        softBg: '#FFF0F5'
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-softBg min-h-screen">

<div class="flex flex-col md:flex-row">

    <!-- ================= SIDEBAR ================= -->
    <aside class="w-full md:w-64 bg-primaryDark text-white p-6 shadow-xl">

        <h2 class="text-2xl font-bold mb-8 tracking-wide">
            University Admin
        </h2>

        <nav class="space-y-3 text-sm">
            <a href="dashboard.php"
               class="block px-4 py-3 rounded-lg hover:bg-primary transition">
                Dashboard
            </a>

            <a href="assign_supervisor.php"
               class="block px-4 py-3 rounded-lg bg-primary">
                Assign Supervisor
            </a>

            <a href="../logout.php"
               class="block px-4 py-3 rounded-lg bg-red-600 hover:bg-red-700 text-center mt-6">
                Logout
            </a>
        </nav>
    </aside>

    <!-- ================= MAIN CONTENT ================= -->
    <div class="flex-1 p-6 md:p-10">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                Assign Supervisor
            </h1>
            <p class="text-gray-500 mt-2">
                Academic Project Management System
            </p>
        </div>

        <!-- Modern Responsive Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden transition hover:shadow-xl">

            <div class="overflow-x-auto">

                <table class="min-w-full text-sm text-gray-700">

                    <thead class="bg-primary text-white text-left">
                        <tr>
                            <th class="px-6 py-4">Project Title</th>
                            <th class="px-6 py-4">Student</th>
                            <th class="px-6 py-4">Supervisor</th>
                            <th class="px-6 py-4">Assign</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">

                    <?php foreach ($projects as $project): ?>
                        <tr class="hover:bg-pink-50 transition duration-200">

                            <!-- Title -->
                            <td class="px-6 py-4 font-medium">
                                <?= htmlspecialchars($project['title']); ?>
                            </td>

                            <!-- Student -->
                            <td class="px-6 py-4">
                                <?= htmlspecialchars($project['student_name']); ?>
                            </td>

                            <!-- Supervisor -->
                            <td class="px-6 py-4">
                                <?php if ($project['supervisor_name']): ?>
                                    <span class="text-green-600 font-medium">
                                        <?= htmlspecialchars($project['supervisor_name']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-red-600 font-semibold">
                                        Not Assigned
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Assign Form -->
                            <td class="px-6 py-4">
                                <form method="POST" class="flex flex-col sm:flex-row gap-2">

                                    <input type="hidden"
                                           name="project_id"
                                           value="<?= $project['id']; ?>">

                                    <select name="supervisor_id"
                                            required
                                            class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none transition">

                                        <option value="">Select Supervisor</option>

                                        <?php foreach ($supervisors as $sup): ?>
                                            <option value="<?= $sup['id']; ?>">
                                                <?= htmlspecialchars($sup['name']); ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>

                                    <button name="assign"
                                            class="bg-primary hover:bg-primaryDark text-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition duration-200">
                                        Assign
                                    </button>

                                </form>
                            </td>

                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>

            </div>

        </div>

    </div>
</div>

</body>
</html>