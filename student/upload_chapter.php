<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $project_id = $_POST['project_id'] ?? null;
    $chapter_number = $_POST['chapter_number'] ?? null;

    if (!$project_id || !$chapter_number) {
        die("Invalid request.");
    }

    if (!isset($_FILES['chapter_file'])) {
        die("No file uploaded.");
    }

    $file = $_FILES['chapter_file'];

    if ($file['error'] !== 0) {
        die("File upload error.");
    }

    // Allowed extensions
    $allowed = ['pdf', 'docx'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        die("Only PDF and DOCX files are allowed.");
    }

    // Create upload directory if not exists
    $uploadDir = "../uploads/chapters/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = time() . "_" . basename($file['name']);
    $target = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {

        $stmt = $pdo->prepare("
            INSERT INTO chapters (project_id, chapter_number, file_path, review_status, uploaded_at)
            VALUES (?, ?, ?, 'pending', NOW())
        ");

        $stmt->execute([$project_id, $chapter_number, $filename]);

        header("Location: dashboard.php");
        exit();

    } else {
        die("Failed to upload file.");
    }
}
?>