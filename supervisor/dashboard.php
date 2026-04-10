<?php
session_start();
require_once "../config/database.php";

/* =========================================
   AUTH CHECK
========================================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../login.php");
    exit();
}

$supervisor_id   = $_SESSION['user_id'];
$supervisor_name = $_SESSION['name'] ?? "Supervisor";

/* =========================================
   ADD FEEDBACK
========================================= */
if (isset($_POST['submit_feedback'])) {

    $chapter_id = $_POST['chapter_id'] ?? null;
    $feedback   = trim($_POST['feedback'] ?? '');

    if ($chapter_id && !empty($feedback)) {

        $stmt = $pdo->prepare("
            INSERT INTO chapter_feedback (chapter_id, supervisor_id, feedback, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$chapter_id, $supervisor_id, $feedback]);

        $pdo->prepare("UPDATE chapters SET status = 'reviewed' WHERE id = ?")
            ->execute([$chapter_id]);

        $studentStmt = $pdo->prepare("
            SELECT u.id
            FROM chapters c
            JOIN projects p ON c.project_id = p.id
            JOIN users u ON u.group_id = p.group_id AND u.role = 'student'
            WHERE c.id = ?
        ");
        $studentStmt->execute([$chapter_id]);
        $studentIds = $studentStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($studentIds as $student_id) {
            $pdo->prepare("
                INSERT INTO notifications (user_id, message, created_at, is_read)
                VALUES (?, ?, NOW(), 0)
            ")->execute([$student_id, "New feedback added to your chapter."]);
        }
    }
}

/* =========================================
   FETCH PROJECTS
========================================= */
$stmt = $pdo->prepare("
    SELECT p.*, pg.group_name, pg.group_code
    FROM projects p
    LEFT JOIN project_groups pg ON p.group_id = pg.id
    WHERE p.supervisor_id = ?
");
$stmt->execute([$supervisor_id]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Supervisor Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#FDE8EF]">

<div class="flex min-h-screen">

<!-- ================= SIDEBAR ================= -->
<div class="w-64 bg-[#C2185B] text-white p-6 flex flex-col justify-between">

    <div>
        <h2 class="text-2xl font-bold mb-8 text-center">
            Supervisor Panel
        </h2>

        <p class="text-sm text-pink-200 mb-6">
            Welcome,<br>
            <span class="font-semibold text-white">
                <?= htmlspecialchars($supervisor_name) ?>
            </span>
        </p>


        <a href="dashboard.php"
           class="block bg-[#7A002B] hover:bg-[#5F0021] px-4 py-2 rounded-lg mb-3 transition">
            Dashboard
        </a>
        <a href="notifications.php"
           class="block bg-[#E30B5C] hover:bg-[#c0094e] px-4 py-2 rounded-lg mb-3 transition">
            Notifications
        </a>
        <a href="student_attendance.php"
           class="block bg-[#E30B5C] hover:bg-[#c0094e] px-4 py-2 rounded-lg mb-3 transition">
            Take Attendance
        </a>
        <a href="student_attendance_history.php"
           class="block bg-[#E30B5C] hover:bg-[#c0094e] px-4 py-2 rounded-lg mb-3 transition">
            Attendance History
        </a>
        <a href="../logout.php"
           class="block bg-[#E30B5C] hover:bg-[#c0094e] text-center px-4 py-2 rounded-lg transition">
            Logout
        </a>
    </div>
</div>

<!-- ================= MAIN CONTENT ================= -->
<div class="flex-1 p-10">

<h1 class="text-3xl font-bold text-[#9B0036] mb-8">
    Welcome, <?= htmlspecialchars($supervisor_name) ?> 👨‍🏫
</h1>

<?php if ($projects): ?>
<?php foreach ($projects as $project): ?>

<div class="bg-white rounded-2xl shadow-lg p-6 mb-8 border border-pink-100">

    <div class="flex flex-col md:flex-row md:justify-between md:items-center">

        <div>
            <h2 class="text-xl font-semibold text-[#9B0036]">
                <?= htmlspecialchars($project['title']) ?>
            </h2>

            <p class="text-sm text-gray-500">
                Group: <?= htmlspecialchars(($project['group_name'] ?? 'N/A') . ' (' . ($project['group_code'] ?? '-') . ')') ?>
            </p>
        </div>

        <!-- DEADLINE -->
        <?php if (!empty($project['deadline'])): ?>
            <?php
                $today = new DateTime();
                $deadline = new DateTime($project['deadline']);
                $interval = $today->diff($deadline);
                $daysLeft = (int)$interval->format('%r%a');
            ?>

            <div class="mt-4 md:mt-0 text-right">
                <p class="text-sm text-gray-500">Deadline</p>
                <p class="font-medium text-[#9B0036]">
                    <?= date("d M Y", strtotime($project['deadline'])) ?>
                </p>

                <?php if ($daysLeft < 0): ?>
                    <span class="mt-2 inline-block bg-red-600 text-white px-3 py-1 rounded-full text-xs animate-pulse">
                        Overdue
                    </span>
                <?php elseif ($daysLeft <= 3): ?>
                    <span class="mt-2 inline-block bg-yellow-400 text-black px-3 py-1 rounded-full text-xs">
                        <?= $daysLeft ?> day(s) left
                    </span>
                <?php else: ?>
                    <span class="mt-2 inline-block bg-green-500 text-white px-3 py-1 rounded-full text-xs">
                        <?= $daysLeft ?> days remaining
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

<?php
$chapStmt = $pdo->prepare("
    SELECT * FROM chapters
    WHERE project_id = ?
    ORDER BY chapter_number ASC
");
$chapStmt->execute([$project['id']]);
$chapters = $chapStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($chapters): ?>
<?php foreach ($chapters as $chapter): ?>

<div class="mt-6 bg-[#FDE8EF] p-5 rounded-xl border border-pink-200">

    <div class="flex justify-between items-center">
        <h3 class="font-semibold text-[#9B0036]">
            Chapter <?= $chapter['chapter_number'] ?>
        </h3>

        <?php if (($chapter['status'] ?? '') === 'reviewed'): ?>
            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">
                Reviewed
            </span>
        <?php else: ?>
            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs">
                Pending
            </span>
        <?php endif; ?>
    </div>

    <a href="../uploads/chapters/<?= htmlspecialchars($chapter['file_path']) ?>"
       target="_blank"
       class="text-[#E30B5C] hover:underline text-sm mt-2 inline-block">
        View Uploaded File
    </a>

<?php
$fbStmt = $pdo->prepare("
    SELECT cf.*, u.name
    FROM chapter_feedback cf
    JOIN users u ON cf.supervisor_id = u.id
    WHERE cf.chapter_id = ?
    ORDER BY cf.created_at DESC
");
$fbStmt->execute([$chapter['id']]);
$feedbacks = $fbStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($feedbacks): ?>
<div class="mt-4">
<p class="text-sm font-semibold text-[#9B0036]">Feedback History</p>

<?php foreach ($feedbacks as $fb): ?>
<div class="mt-2 bg-pink-50 border-l-4 border-[#E30B5C] p-3 rounded">

<?= nl2br(htmlspecialchars($fb['feedback'])) ?>

<p class="text-xs text-gray-500 mt-1">
By <?= htmlspecialchars($fb['name']) ?>
| <?= date("d M Y H:i", strtotime($fb['created_at'])) ?>
</p>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" class="mt-4">
    <input type="hidden" name="chapter_id" value="<?= $chapter['id'] ?>">

    <textarea name="feedback"
        class="w-full border border-pink-300 rounded-lg p-2 focus:ring-2 focus:ring-[#E30B5C] focus:outline-none"
        placeholder="Write feedback..."
        required></textarea>

    <button type="submit"
        name="submit_feedback"
        class="mt-2 bg-[#E30B5C] hover:bg-[#c0094e] text-white px-4 py-2 rounded-lg text-sm transition">
        Add Feedback
    </button>
</form>

</div>
<?php endforeach; ?>
<?php else: ?>
<p class="text-gray-500 mt-4">No chapters uploaded.</p>
<?php endif; ?>

</div>
<?php endforeach; ?>
<?php else: ?>
<p class="text-gray-600">No projects assigned.</p>
<?php endif; ?>

</div>
</div>
</body>
</html>