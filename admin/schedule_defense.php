<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* Handle Schedule Submission */
if (isset($_POST['schedule'])) {

    $project_id = $_POST['project_id'];
    $defense_date = $_POST['defense_date'];
    $room = $_POST['room'];
    $panel_members = $_POST['panel_members'];

    $stmt = $pdo->prepare("
        INSERT INTO defense_schedule 
        (project_id, defense_date, room, panel_members)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$project_id, $defense_date, $room, $panel_members]);

    header("Location: schedule_defense.php");
    exit();
}

/* Get All Projects */
$projects = $pdo->query("
    SELECT projects.id, projects.title, users.name AS student_name
    FROM projects
    JOIN users ON projects.student_id = users.id
    ORDER BY projects.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* Get Existing Schedules */
$schedules = $pdo->query("
    SELECT defense_schedule.*, projects.title
    FROM defense_schedule
    JOIN projects ON defense_schedule.project_id = projects.id
    ORDER BY defense_schedule.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Schedule Defense</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- University Raspberry Theme -->
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

    <!-- SIDEBAR -->
    <aside class="w-full md:w-64 bg-primaryDark text-white p-6 shadow-xl">

        <h2 class="text-2xl font-bold mb-8">University Admin</h2>

        <nav class="space-y-3 text-sm">
            <a href="dashboard.php" class="block px-4 py-3 rounded-lg hover:bg-primary transition">
                Dashboard
            </a>

          

          

            <a href="schedule_defense.php" class="block px-4 py-3 rounded-lg bg-primary">
                Schedule Defense
            </a>

            <a href="../logout.php"
               class="block px-4 py-3 rounded-lg bg-red-600 hover:bg-red-700 text-center mt-6">
                Logout
            </a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex-1 p-6 md:p-10">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                Schedule Defense
            </h1>
            <p class="text-gray-500 mt-2">
                Create and manage project defense schedules
            </p>
        </div>

        <!-- FORM CARD -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-10 hover:shadow-xl transition">

            <h2 class="text-xl font-semibold text-gray-800 mb-6">
                Create Defense Schedule
            </h2>

            <form method="POST" class="space-y-6">

                <!-- Project -->
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">
                        Project
                    </label>
                    <select name="project_id" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none transition">
                        <option value="">Select Project</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?= $project['id']; ?>">
                                <?= htmlspecialchars($project['title']); ?>
                                (<?= htmlspecialchars($project['student_name']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Date + Room -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">
                            Defense Date
                        </label>
                        <input type="date" name="defense_date" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">
                            Room
                        </label>
                        <input type="text" name="room"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none transition">
                    </div>

                </div>

                <!-- Panel Members -->
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">
                        Panel Members
                    </label>
                    <textarea name="panel_members" rows="4"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none transition"></textarea>
                </div>

                <button type="submit" name="schedule"
                        class="bg-primary hover:bg-primaryDark text-white px-8 py-3 rounded-xl shadow-md hover:shadow-lg transition">
                    Schedule Defense
                </button>

            </form>

        </div>

        <!-- SCHEDULE TABLE -->
        <div class="bg-white shadow-lg rounded-2xl overflow-x-auto">

            <table class="min-w-full text-sm text-gray-700">

                <thead class="bg-primary text-white">
                    <tr>
                        <th class="px-6 py-4 text-left">Project</th>
                        <th class="px-6 py-4 text-left">Date</th>
                        <th class="px-6 py-4 text-left">Room</th>
                        <th class="px-6 py-4 text-left">Panel Members</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                <?php foreach ($schedules as $schedule): ?>
                <tr class="hover:bg-pink-50 transition duration-200">

                    <td class="px-6 py-4 font-medium">
                        <?= htmlspecialchars($schedule['title']); ?>
                    </td>

                    <td class="px-6 py-4">
                        <?= htmlspecialchars($schedule['defense_date']); ?>
                    </td>

                    <td class="px-6 py-4">
                        <?= htmlspecialchars($schedule['room']); ?>
                    </td>

                    <td class="px-6 py-4">
                        <?= nl2br(htmlspecialchars($schedule['panel_members'])); ?>
                    </td>

                </tr>
                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>