<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ================= SEARCH FUNCTION ================= */

$search = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);

    $stmt = $pdo->prepare("
        SELECT id, name, email, role 
        FROM users 
        WHERE name LIKE ?
        ORDER BY id DESC
    ");
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $pdo->query("
        SELECT id, name, email, role 
        FROM users 
        ORDER BY id DESC
    ");
}

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

<div class="flex">

    <!-- ================= SIDEBAR ================= -->
    <aside class="w-64 bg-[#880E4F] text-white min-h-screen p-6 hidden md:block shadow-lg">

        <h2 class="text-2xl font-bold mb-8 border-b border-pink-300 pb-3">
            🎓 University Admin
        </h2>

        <nav class="space-y-3">
            <a href="dashboard.php" class="block px-3 py-2 rounded-lg hover:bg-[#C2185B] transition">
                Dashboard
            </a>

            <a href="manage_projects.php" class="block px-3 py-2 rounded-lg hover:bg-[#C2185B] transition">
                Manage Projects
            </a>

            <a href="manage_users.php" class="block px-3 py-2 rounded-lg bg-[#C2185B] font-semibold shadow">
                Manage Users
            </a>

            <a href="../logout.php" 
               class="block px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-center mt-6">
                Logout
            </a>
        </nav>
    </aside>

    <!-- ================= MAIN CONTENT ================= -->
    <div class="flex-1 p-6">

        <div class="mb-6">
            <h1 class="text-3xl font-semibold text-[#880E4F]">
                Manage Users
            </h1>
            <p class="text-gray-500 text-sm">
                View and manage all registered users
            </p>
        </div>

        <!-- ================= SEARCH BAR ================= -->
        <div class="bg-white shadow-md rounded-xl p-6 mb-6 border-t-4 border-[#C2185B]">

            <form method="GET" class="flex flex-col md:flex-row gap-4">

                <input type="text" name="search"
                    value="<?= htmlspecialchars($search); ?>"
                    placeholder="Search by user name..."
                    class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#C2185B] focus:outline-none">

                <button type="submit"
                    class="bg-[#880E4F] hover:bg-[#C2185B] text-white px-6 py-2 rounded-lg transition">
                    Search
                </button>

                <?php if ($search): ?>
                    <a href="manage_users.php"
                       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition text-center">
                        Reset
                    </a>
                <?php endif; ?>

            </form>

        </div>

        <!-- ================= TABLE CARD ================= -->
        <div class="bg-white shadow-lg rounded-xl overflow-x-auto">

            <table class="min-w-full text-sm text-gray-700">

                <thead class="bg-[#880E4F] text-white">
                    <tr>
                        <th class="px-6 py-4 text-left">ID</th>
                        <th class="px-6 py-4 text-left">Name</th>
                        <th class="px-6 py-4 text-left">Email</th>
                        <th class="px-6 py-4 text-left">Role</th>
                        <th class="px-6 py-4 text-left">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                <?php foreach ($users as $user): ?>
                <tr class="hover:bg-[#F8BBD0]/20">

                    <td class="px-6 py-4">
                        <?= $user['id']; ?>
                    </td>

                    <td class="px-6 py-4 font-medium">
                        <?= htmlspecialchars($user['name']); ?>
                    </td>

                    <td class="px-6 py-4">
                        <?= htmlspecialchars($user['email']); ?>
                    </td>

                    <td class="px-6 py-4">
                        <?php if ($user['role'] == 'admin'): ?>
                            <span class="bg-[#880E4F] text-white px-3 py-1 rounded-full text-xs font-semibold">
                                Admin
                            </span>
                        <?php elseif ($user['role'] == 'student'): ?>
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                Student
                            </span>
                        <?php else: ?>
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">
                                Supervisor
                            </span>
                        <?php endif; ?>
                    </td>

                    <td class="px-6 py-4">
                        <?php if ($user['role'] != 'admin'): ?>
                            <a href="delete_user.php?id=<?= $user['id']; ?>" 
                               class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm transition"
                               onclick="return confirm('Are you sure you want to delete this user?');">
                               Delete
                            </a>
                        <?php else: ?>
                            <span class="text-gray-400">---</span>
                        <?php endif; ?>
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