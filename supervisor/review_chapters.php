<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../login.php");
    exit();
}

include("../includes/header.php");

$supervisor_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT p.id, p.title, u.name AS student_name
    FROM projects p
    JOIN users u ON p.student_id = u.id
    WHERE p.supervisor_id = ?
");
$stmt->execute([$supervisor_id]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto px-6 py-10">

<h1 class="text-4xl font-bold text-slate-800 mb-10">
    📋 Review Student Chapters
</h1>

<?php if ($projects): ?>

<?php foreach ($projects as $project): ?>

<div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-8 mb-12">

    <!-- Project Header -->
    <div class="border-b border-slate-200 pb-6 mb-6">
        <h2 class="text-2xl font-semibold text-slate-800">
            <?= htmlspecialchars($project['title']); ?>
        </h2>
        <p class="text-gray-500 mt-1">
            👤 Student: 
            <span class="font-medium text-slate-700">
                <?= htmlspecialchars($project['student_name']); ?>
            </span>
        </p>
    </div>

<?php
$stmtC = $pdo->prepare("
    SELECT *
    FROM chapters
    WHERE project_id = ?
    ORDER BY chapter_number ASC
");
$stmtC->execute([$project['id']]);
$chapters = $stmtC->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($chapters): ?>

<?php foreach ($chapters as $chapter): ?>

<div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 mb-8 hover:shadow-md transition">

    <!-- Chapter Header -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center">

        <div>
            <h3 class="text-lg font-semibold text-slate-700">
                📘 Chapter <?= $chapter['chapter_number']; ?>
            </h3>

            <a href="../uploads/<?= htmlspecialchars($chapter['file_path']); ?>"
               target="_blank"
               class="text-blue-700 hover:underline text-sm mt-1 inline-block">
                📂 View Uploaded File
            </a>
        </div>

        <!-- Status Badge -->
        <div class="mt-3 md:mt-0">
            <?php 
            $status = $chapter['review_status'] ?? 'pending';
            $badge = $status === 'reviewed'
                ? 'bg-green-100 text-green-700'
                : 'bg-yellow-100 text-yellow-700';
            ?>
            <span class="<?= $badge ?> px-4 py-1 rounded-full text-xs font-semibold">
                <?= ucfirst($status); ?>
            </span>
        </div>

    </div>

    <!-- Previous Feedback -->
    <?php if (!empty($chapter['feedback'])): ?>
        <div class="mt-6 bg-blue-50 border-l-4 border-blue-600 p-4 rounded-lg">
            <p class="text-sm font-semibold text-slate-700 mb-2">
                Previous Feedback
            </p>
            <p class="text-slate-700 text-sm leading-relaxed">
                <?= nl2br(htmlspecialchars($chapter['feedback'])); ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- Review Form -->
    <form method="POST"
          action="save_feedback.php"
          class="mt-6">

        <input type="hidden"
               name="chapter_id"
               value="<?= $chapter['id']; ?>">

        <label class="block text-sm font-medium text-slate-700 mb-2">
            Write New Feedback
        </label>

        <textarea name="feedback"
                  class="w-full border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-blue-600 focus:outline-none transition"
                  placeholder="Write feedback here..."
                  rows="4"
                  required></textarea>

        <div class="mt-4 flex justify-end">
            <button type="submit"
                class="bg-blue-700 hover:bg-blue-800 text-white px-6 py-2 rounded-xl shadow-md transition duration-300">
                Submit Review
            </button>
        </div>

    </form>

</div>

<?php endforeach; ?>

<?php else: ?>

<div class="bg-slate-50 border border-slate-200 p-6 rounded-xl text-center">
    <p class="text-gray-500">
        No chapters uploaded yet.
    </p>
</div>

<?php endif; ?>

</div>

<?php endforeach; ?>

<?php else: ?>

<div class="bg-white p-12 rounded-2xl shadow-lg text-center">
    <p class="text-gray-600 text-lg">
        No assigned projects.
    </p>
</div>

<?php endif; ?>

</div>

<?php include("../includes/footer.php"); ?>