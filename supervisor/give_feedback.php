<?php
session_start();
require_once "../config/database.php";

if ($_SESSION['role'] != 'supervisor') {
    header("Location: dashboard.php");
    exit();
}

/* ================= FETCH CHAPTERS ================= */
$stmt = $pdo->prepare("
    SELECT c.*, p.student_id, u.name AS student_name
    FROM chapters c
    JOIN projects p ON c.project_id = p.id
    JOIN users u ON p.student_id = u.id
    WHERE p.supervisor_id = ?
    ORDER BY c.uploaded_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$chapters = $stmt->fetchAll();

/* ================= SUBMIT FEEDBACK ================= */
if (isset($_POST['submit_feedback'])) {

    $chapter_id = $_POST['chapter_id'];
    $feedback   = trim($_POST['feedback']);

    $update = $pdo->prepare("
        UPDATE chapters 
        SET feedback = ?, status = 'reviewed'
        WHERE id = ?
    ");
    $update->execute([$feedback, $chapter_id]);

    $getStudent = $pdo->prepare("
        SELECT p.student_id 
        FROM chapters c
        JOIN projects p ON c.project_id = p.id
        WHERE c.id = ?
    ");
    $getStudent->execute([$chapter_id]);
    $student = $getStudent->fetchColumn();

    $notify = $pdo->prepare("
        INSERT INTO notifications (user_id, message)
        VALUES (?, ?)
    ");
    $notify->execute([$student, "Your chapter has been reviewed by supervisor."]);

    header("Location: give_feedback.php");
    exit();
}
?>

<?php include "header.php"; ?>

<div class="max-w-6xl mx-auto px-6 py-10">

<h1 class="text-3xl font-bold text-slate-800 mb-10">
    📚 Review Chapters
</h1>

<?php if ($chapters): ?>
<?php foreach ($chapters as $chapter): ?>

<div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-6 mb-8 transition hover:shadow-xl">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center">

        <div>
            <h2 class="text-lg font-semibold text-slate-800">
                <?= htmlspecialchars($chapter['student_name']); ?>
            </h2>
            <p class="text-sm text-gray-500">
                Chapter <?= $chapter['chapter_number']; ?>
            </p>
        </div>

        <!-- STATUS BADGE -->
        <div class="mt-3 md:mt-0">
            <?php if ($chapter['status'] == 'reviewed'): ?>
                <span class="bg-green-100 text-green-700 px-4 py-1 rounded-full text-xs font-medium">
                    Reviewed
                </span>
            <?php else: ?>
                <span class="bg-yellow-100 text-yellow-700 px-4 py-1 rounded-full text-xs font-medium">
                    Pending Review
                </span>
            <?php endif; ?>
        </div>

    </div>

    <!-- FILE LINK -->
    <div class="mt-4">
        <a href="../uploads/<?= htmlspecialchars($chapter['file_path']); ?>"
           target="_blank"
           class="text-blue-700 font-medium hover:underline">
           📂 View Uploaded File
        </a>
    </div>

    <!-- FEEDBACK FORM -->
    <form method="POST" class="mt-6">

        <input type="hidden" name="chapter_id" value="<?= $chapter['id']; ?>">

        <label class="block text-sm font-medium text-slate-700 mb-2">
            Supervisor Feedback
        </label>

        <textarea name="feedback"
                  required
                  rows="4"
                  class="w-full border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-blue-600 focus:outline-none transition"
                  placeholder="Write feedback..."><?= htmlspecialchars($chapter['feedback']); ?></textarea>

        <button type="submit"
                name="submit_feedback"
                class="mt-4 bg-blue-700 hover:bg-blue-800 text-white px-6 py-2 rounded-xl transition shadow-md">
            Submit Feedback
        </button>

    </form>

</div>

<?php endforeach; ?>
<?php else: ?>

<div class="bg-white p-10 rounded-2xl shadow-lg text-center">
    <p class="text-gray-600 text-lg">
        No chapters available for review.
    </p>
</div>

<?php endif; ?>

</div>