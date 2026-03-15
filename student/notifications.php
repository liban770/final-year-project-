<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ===============================
   HANDLE ACTIONS
================================ */

// Mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $stmt = $pdo->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
}

// Delete single notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("
        DELETE FROM notifications
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$_POST['delete_id'], $user_id]);
}

// Delete all notifications
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    $stmt = $pdo->prepare("
        DELETE FROM notifications
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
}

include("../includes/header.php");

/* ===============================
   GET USER NOTIFICATIONS
================================ */
$stmt = $pdo->prepare("
    SELECT *
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 20
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-3xl mx-auto">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">🔔 Notifications</h2>

        <?php if ($notifications): ?>
            <div class="flex gap-4">
                <!-- Mark all as read -->
                <form method="POST">
                    <button 
                        type="submit" 
                        name="mark_read"
                        class="text-center bg-blue-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        Mark all as read
                    </button>
                </form>

                <!-- Delete all -->
                <form method="POST" onsubmit="return confirm('Delete all notifications?');">
                    <button 
                        type="submit" 
                        name="delete_all"
                        class="text-center bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        Delete all
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($notifications): ?>
        <ul class="bg-white p-6 rounded shadow">
            <?php foreach ($notifications as $note): ?>
                <li class="mb-4 border-b pb-3 last:border-b-0 
                    <?= $note['is_read'] == 0 ? 'font-bold bg-gray-100 p-3 rounded' : ''; ?>">

                    <div class="flex justify-between items-start">

                        <div>
                            <p>
                                <?= htmlspecialchars($note['message']); ?>
                            </p>

                            <small class="text-gray-500 block mt-1">
                                <?= date("d M Y - h:i A", strtotime($note['created_at'])); ?>
                            </small>
                        </div>

                        <!-- Delete single -->
                        <form method="POST" 
                              onsubmit="return confirm('Delete this notification?');">
                            <input type="hidden" name="delete_id" 
                                   value="<?= $note['id']; ?>">
                            <button 
                                type="submit"
                                class="text-center bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                Delete
                            </button>
                        </form>

                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="bg-white p-6 rounded shadow text-gray-500">
            No notifications found.
        </div>
    <?php endif; ?>

</div>

<?php include("../includes/footer.php"); ?>