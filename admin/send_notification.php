<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";
$success = "";

// When form submitted
if (isset($_POST['send'])) {

    $message = trim($_POST['message']);
    $target = $_POST['target'];

    if (!empty($message)) {

        if ($target == "students") {

            $stmt = $pdo->query("SELECT id FROM users WHERE role='student'");
            $users = $stmt->fetchAll();

        } elseif ($target == "supervisors") {

            $stmt = $pdo->query("SELECT id FROM users WHERE role='supervisor'");
            $users = $stmt->fetchAll();

        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE id=?");
            $stmt->execute([$target]);
            $users = $stmt->fetchAll();
        }

        foreach ($users as $user) {
            $insert = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $insert->execute([$user['id'], $message]);
        }

        $success = "Notification sent successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Notification</title>
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

          

           

          

            <a href="send_notification.php" class="block px-3 py-2 rounded-lg bg-[#C2185B] font-semibold shadow">
                Send Notification
            </a>

            <a href="../logout.php" 
               class="block px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-center mt-6 transition">
                Logout
            </a>
        </nav>
    </aside>

    <!-- ================= MAIN CONTENT ================= -->
    <div class="flex-1 p-6">

        <div class="mb-8">
            <h1 class="text-3xl font-semibold text-[#880E4F]">
                Send Notification
            </h1>
            <p class="text-gray-500 text-sm">
                Send announcements to students or supervisors
            </p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-600 text-white px-4 py-3 rounded-lg mb-6 shadow">
                <?= $success; ?>
            </div>
        <?php endif; ?>

        <!-- ================= FORM CARD ================= -->
        <div class="bg-white shadow-lg rounded-xl p-8 max-w-2xl border-t-4 border-[#C2185B]">

            <form method="POST" class="space-y-6">

                <div>
                    <label class="block text-sm font-medium text-[#880E4F] mb-2">
                        Message
                    </label>
                    <textarea name="message" required rows="4"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C2185B] focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#880E4F] mb-2">
                        Send To
                    </label>
                    <select name="target"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C2185B] focus:outline-none">

                        <option value="students">All Students</option>
                        <option value="supervisors">All Supervisors</option>

                        <option disabled>────────────</option>

                        <?php
                        $allUsers = $pdo->query("SELECT id, name, role FROM users WHERE role!='admin'");
                        foreach ($allUsers as $u) {
                            echo "<option value='{$u['id']}'>" .
                                 htmlspecialchars($u['name']) .
                                 " (" . htmlspecialchars($u['role']) . ")</option>";
                        }
                        ?>

                    </select>
                </div>

                <div>
                    <button type="submit" name="send"
                        class="bg-[#880E4F] hover:bg-[#C2185B] text-white px-6 py-2 rounded-lg transition shadow">
                        Send Notification
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>