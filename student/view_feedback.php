<?php
session_start();
require_once "../config/database.php";

if($_SESSION['role'] != 'student'){
    header("Location: dashboard.php");
    exit();
}

// Get student chapters + supervisor
$stmt = $pdo->prepare("
    SELECT c.*, u.name AS supervisor_name
    FROM chapters c
    JOIN projects p ON c.project_id = p.id
    JOIN users u ON p.supervisor_id = u.id
    WHERE p.student_id = ?
    ORDER BY c.uploaded_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$chapters = $stmt->fetchAll();
?>

<?php include "header.php"; ?>

<h2 class="text-2xl font-bold mb-6">Supervisor Feedback</h2>

<?php foreach($chapters as $chapter): ?>

<div class="bg-white p-6 rounded shadow mb-6">

    <h3 class="text-lg font-semibold">
        Chapter <?php echo $chapter['chapter_number']; ?>
    </h3>

    <p class="mt-2">
        Uploaded: <?php echo $chapter['uploaded_at']; ?>
    </p>

    <p class="mt-2">
        Supervisor: <?php echo htmlspecialchars($chapter['supervisor_name']); ?>
    </p>

    <p class="mt-2">
        Status:
        <?php if($chapter['status'] == 'reviewed'): ?>
            <span class="bg-green-500 text-white px-2 py-1 rounded text-sm">Reviewed</span>
        <?php else: ?>
            <span class="bg-yellow-500 text-white px-2 py-1 rounded text-sm">Pending</span>
        <?php endif; ?>
    </p>

    <?php if($chapter['feedback']): ?>
        <div class="mt-4 bg-gray-100 p-4 rounded">
            <strong>Feedback:</strong>
            <p class="mt-2"><?php echo htmlspecialchars($chapter['feedback']); ?></p>
        </div>
    <?php else: ?>
        <p class="mt-4 text-gray-500">No feedback yet.</p>
    <?php endif; ?>

</div>

<?php endforeach; ?>