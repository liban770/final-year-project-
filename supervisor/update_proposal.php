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

    // Notify all students in the project group
    $stmt2 = $pdo->prepare("
        SELECT u.id 
        FROM projects
        JOIN proposals ON proposals.project_id = projects.id
        JOIN users u ON u.group_id = projects.group_id AND u.role = 'student'
        WHERE proposals.id = ?
    ");
    $stmt2->execute([$proposal_id]);
    $students = $stmt2->fetchAll(PDO::FETCH_COLUMN);

    if ($students) {
        foreach ($students as $studentId) {
            $pdo->prepare("
                INSERT INTO notifications (user_id, message)
                VALUES (?, ?)
            ")->execute([
                $studentId,
                "Your proposal has been $status by your supervisor."
            ]);
        }
    }

    header("Location: dashboard.php");
    exit();
}
?>