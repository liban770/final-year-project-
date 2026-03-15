<?php
session_start();
require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $proposal_id = $_POST['proposal_id'];
    $status = $_POST['status'];
    $feedback = $_POST['feedback'];

    // Update proposal
    $stmt = $pdo->prepare("
        UPDATE proposals 
        SET status = ?, feedback = ?
        WHERE id = ?
    ");
    $stmt->execute([$status, $feedback, $proposal_id]);

    // Get student id for notification
    $stmt2 = $pdo->prepare("
        SELECT projects.student_id 
        FROM projects
        JOIN proposals ON proposals.project_id = projects.id
        WHERE proposals.id = ?
    ");
    $stmt2->execute([$proposal_id]);
    $student = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $pdo->prepare("
            INSERT INTO notifications (user_id, message)
            VALUES (?, ?)
        ")->execute([
            $student['student_id'],
            "Your proposal has been $status by your supervisor."
        ]);
    }

    header("Location: dashboard.php");
    exit();
}
?>