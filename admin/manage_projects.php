<?php
session_start();
require_once "../config/database.php";

/* =========================================
   AUTH CHECK
========================================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = "";

/* ===============================
   HANDLE APPROVE / REJECT / DELETE
==================================*/
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'], $_GET['id'])) {

    $id = intval($_GET['id']);
    $action = $_GET['action'];

    $stmt = $pdo->prepare("SELECT group_id, supervisor_id, title FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($project) {

        if ($action === 'approve') {

            $pdo->prepare("UPDATE projects SET status = 'approved' WHERE id = ?")
                ->execute([$id]);

            $studentIdsStmt = $pdo->prepare("SELECT id FROM users WHERE role = 'student' AND group_id = ?");
            $studentIdsStmt->execute([$project['group_id']]);
            $studentIds = $studentIdsStmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($studentIds as $studentId) {
                $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")
                    ->execute([$studentId, "🎉 Your group project '{$project['title']}' has been approved!"]);
            }

            if (!empty($project['supervisor_id'])) {
                $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")
                    ->execute([$project['supervisor_id'], "📢 Project '{$project['title']}' has been approved."]);
            }

            $success = "Project approved successfully.";
        }

        if ($action === 'reject') {

            $pdo->prepare("UPDATE projects SET status = 'rejected' WHERE id = ?")
                ->execute([$id]);

            $studentIdsStmt = $pdo->prepare("SELECT id FROM users WHERE role = 'student' AND group_id = ?");
            $studentIdsStmt->execute([$project['group_id']]);
            $studentIds = $studentIdsStmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($studentIds as $studentId) {
                $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")
                    ->execute([$studentId, "❌ Your group project '{$project['title']}' has been rejected."]);
            }

            $success = "Project rejected successfully.";
        }

        if ($action === 'delete') {

            $pdo->prepare("DELETE FROM projects WHERE id = ?")
                ->execute([$id]);

            $success = "Project deleted successfully.";
        }
    }
}

/* ===============================
   HANDLE DEADLINE UPDATE
==================================*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_deadline'])) {

    $project_id = intval($_POST['project_id']);
    $deadline = $_POST['deadline'];

    $stmt = $pdo->prepare("UPDATE projects SET deadline = ? WHERE id = ?");
    $stmt->execute([$deadline, $project_id]);

    $success = "Deadline updated successfully.";
}

/* ===============================
   FETCH PROJECTS
==================================*/
$projects = $pdo->query("
    SELECT projects.*, 
           pg.group_name,
           pg.group_code,
           sp.name AS supervisor_name
    FROM projects
    LEFT JOIN project_groups pg ON projects.group_id = pg.id
    LEFT JOIN users sp ON projects.supervisor_id = sp.id
    ORDER BY projects.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Project Management Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>

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

            <a href="manage_projects.php" class="block px-4 py-3 rounded-lg bg-primary">
                Manage Projects
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
                Project Management Panel
            </h1>
            <p class="text-gray-500 mt-2">
                Review, approve, reject and manage deadlines
            </p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-600 text-white px-4 py-3 rounded-lg mb-6 shadow animate-pulse">
                <?= htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- SEARCH + FILTER -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <input type="text"
                       id="searchInput"
                       placeholder="Search by title, student or supervisor..."
                       class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none">

                <select id="statusFilter"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        <!-- TABLE -->
        <div class="bg-white shadow-lg rounded-2xl overflow-x-auto">
            <table class="min-w-full table-auto text-sm text-gray-700" id="projectTable">

                <thead class="bg-primary text-white">
                    <tr>
                        <th class="px-6 py-4 text-left">Title</th>
                        <th class="px-6 py-4 text-left">Group</th>
                        <th class="px-6 py-4 text-left">Supervisor</th>
                        <th class="px-6 py-4 text-left">Deadline</th>
                        <th class="px-6 py-4 text-left">Status</th>
                        <th class="px-6 py-4 text-left w-72">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                <?php foreach ($projects as $project): ?>
                <tr class="hover:bg-pink-50 transition duration-200">

                    <td class="px-6 py-4 font-medium">
                        <?= htmlspecialchars($project['title']); ?>
                    </td>

                    <td class="px-6 py-4">
                        <?= htmlspecialchars(($project['group_name'] ?? 'N/A') . ' (' . ($project['group_code'] ?? '-') . ')'); ?>
                    </td>

                    <td class="px-6 py-4">
                        <?= $project['supervisor_name']
                            ? htmlspecialchars($project['supervisor_name'])
                            : '<span class="text-red-600 font-medium">Not Assigned</span>'; ?>
                    </td>

                    <td class="px-6 py-4">
                        <form method="POST" class="flex flex-wrap gap-2 items-center">
                            <input type="hidden" name="project_id" value="<?= $project['id']; ?>">
                            <input type="date" name="deadline"
                                   value="<?= $project['deadline']; ?>"
                                   class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary">
                            <button name="update_deadline"
                                    class="bg-primary hover:bg-primaryDark text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                Save
                            </button>
                        </form>
                    </td>

                    <!-- STATUS BADGE -->
                    <td class="px-6 py-4 status-cell">
                        <?php
                            $status = $project['status'];
                            $color = match($status) {
                                'approved' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                default => 'bg-yellow-100 text-yellow-700'
                            };
                        ?>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $color ?>">
                            <?= htmlspecialchars(ucfirst($status)); ?>
                        </span>
                    </td>

                    <!-- ACTION BUTTONS -->
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-2">

                            <a href="?action=approve&id=<?= $project['id']; ?>"
                               class="flex-1 min-w-[90px] text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                               Approve
                            </a>

                            <a href="?action=reject&id=<?= $project['id']; ?>"
                               class="flex-1 min-w-[90px] text-center bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                               Reject
                            </a>

                            <a href="?action=delete&id=<?= $project['id']; ?>"
                               onclick="return confirm('Are you sure you want to delete this project?');"
                               class="flex-1 min-w-[90px] text-center bg-gray-700 hover:bg-black text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                               Delete
                            </a>

                        </div>
                    </td>

                </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const tableRows = document.querySelectorAll("#projectTable tbody tr");

function filterTable() {
    const searchValue = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value;

    tableRows.forEach(row => {
        const rowText = row.innerText.toLowerCase();
        const status = row.querySelector(".status-cell").innerText.toLowerCase();

        const matchesSearch = rowText.includes(searchValue);
        const matchesStatus = statusValue === "all" || status === statusValue;

        row.style.display = (matchesSearch && matchesStatus) ? "" : "none";
    });
}

searchInput.addEventListener("keyup", filterTable);
statusFilter.addEventListener("change", filterTable);
</script>

</body>
</html>