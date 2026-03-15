<?php
require_once "../config/database.php";

if (!isset($_POST['keyword'])) {
    echo json_encode([]);
    exit();
}

$keyword = strtolower(trim($_POST['keyword']));

$suggestions = [];

/* Simple AI Logic */
if (strpos($keyword, "ai") !== false || strpos($keyword, "machine learning") !== false) {
    $suggestions = [
        "AI Based Disease Prediction System",
        "AI Chatbot for Student Support",
        "Smart Attendance System using AI",
        "AI Resume Screening System",
        "AI Powered Recommendation System"
    ];
}

elseif (strpos($keyword, "web") !== false) {
    $suggestions = [
        "Online Learning Management System",
        "E-Commerce Recommendation Website",
        "Smart Job Portal with Skill Matching",
        "Online Voting System with Security",
        "Event Management Web Platform"
    ];
}

elseif (strpos($keyword, "security") !== false || strpos($keyword, "cyber") !== false) {
    $suggestions = [
        "Cyber Attack Detection System",
        "Secure File Sharing Platform",
        "Blockchain Based Data Security",
        "Network Intrusion Detection System",
        "Password Strength Analyzer"
    ];
}

else {
    $suggestions = [
        "Smart Task Management System",
        "Student Performance Prediction System",
        "Online Project Collaboration Platform",
        "Digital Library Management System",
        "Smart Campus Management System"
    ];
}

echo json_encode($suggestions);