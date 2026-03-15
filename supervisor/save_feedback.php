<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $chapter_id = $_POST['chapter_id'];
    $feedback = $_POST['feedback'];
    $supervisor_id = $_SESSION['user_id'];

    // Update chapter
    $stmt = $pdo->prepare("
        UPDATE chapters
        SET feedback = ?,
            feedback_date = NOW(),
            reviewed_by = ?,
            review_status = 'reviewed'
        WHERE id = ?
    ");
    $stmt->execute([$feedback, $supervisor_id, $chapter_id]);

    // Get student id
    $stmt2 = $pdo->prepare("
        SELECT p.student_id
        FROM chapters c
        JOIN projects p ON c.project_id = p.id
        WHERE c.id = ?
    ");
    $stmt2->execute([$chapter_id]);
    $student_id = $stmt2->fetchColumn();

    // Send notification
    $stmt3 = $pdo->prepare("
        INSERT INTO notifications (user_id, message, is_read, created_at)
        VALUES (?, ?, 0, NOW())
    ");
    $stmt3->execute([
        $student_id,
        "Your chapter has been reviewed. Please check feedback."
    ]);

    header("Location: review_chapters.php");
    exit();
}