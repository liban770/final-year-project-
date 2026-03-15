<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $project_id = $_POST['project_id'];
    $file = $_FILES['proposal'];

    if ($file['error'] === 0) {

        $allowed = ['pdf','docx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            die("Only PDF and DOCX allowed.");
        }

        $filename = time() . "_" . basename($file['name']);
        $target = "../uploads/proposals/" . $filename;

        move_uploaded_file($file['tmp_name'], $target);

        $stmt = $pdo->prepare("
            INSERT INTO proposals (project_id, file_path, status, submitted_at)
            VALUES (?, ?, 'pending', NOW())
        ");
        $stmt->execute([$project_id, $filename]);

        echo "Proposal submitted successfully!";
    }
}
?>