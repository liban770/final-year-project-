<?php
session_start();
require_once "../config/database.php";
include("../includes/header.php");

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// Mark all as read
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")
    ->execute([$_SESSION['user_id']]);
?>

<h1 class="text-2xl font-bold mb-6">Notifications</h1>

<div class="bg-white p-6 rounded shadow">

<?php if(count($notifications) > 0): ?>
    <?php foreach($notifications as $note): ?>
        <div class="border-b py-4">
            <p><?php echo htmlspecialchars($note['message']); ?></p>
            <small class="text-gray-500">
                <?php echo $note['created_at']; ?>
            </small>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No notifications available.</p>
<?php endif; ?>

</div>

<?php include("../includes/footer.php"); ?>