<?php
require_once "../config/database.php";

if (!isset($_POST['title'])) {
    echo json_encode([]);
    exit();
}

$title = strtolower(trim($_POST['title']));

$stmt = $pdo->query("SELECT title FROM projects");

$similar_projects = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $existing = strtolower($row['title']);

    similar_text($title, $existing, $percent);

    if ($percent > 40) { // threshold

        $similar_projects[] = [
            "title" => $row['title'],
            "similarity" => round($percent, 2)
        ];
    }
}

echo json_encode($similar_projects);