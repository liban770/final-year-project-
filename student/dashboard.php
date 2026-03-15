<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

include("../includes/header.php");

$student_id = $_SESSION['user_id'];
$project = null;
$schedule = null;
$chapters = [];
$progress = 0;
$gradeData = null;

/* ================= PROJECT ================= */
$stmt = $pdo->prepare("
    SELECT 
        p.id,
        p.title,
        p.description,
        p.status,
        p.deadline,
        u.name AS supervisor_name
    FROM projects p
    LEFT JOIN users u ON p.supervisor_id = u.id
    WHERE p.student_id = ?
");
$stmt->execute([$student_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if ($project) {

    $stmt2 = $pdo->prepare("
        SELECT defense_date, room, panel_members
        FROM defense_schedule
        WHERE project_id = ?
    ");
    $stmt2->execute([$project['id']]);
    $schedule = $stmt2->fetch(PDO::FETCH_ASSOC);

    $stmtC = $pdo->prepare("
        SELECT *
        FROM chapters
        WHERE project_id = ?
        ORDER BY chapter_number ASC
    ");
    $stmtC->execute([$project['id']]);
    $chapters = $stmtC->fetchAll(PDO::FETCH_ASSOC);

    $totalChapters = count($chapters);
    $reviewedChapters = 0;

    foreach ($chapters as $ch) {
        if (($ch['status'] ?? '') === 'reviewed') {
            $reviewedChapters++;
        }
    }

    $progress = $totalChapters > 0
        ? round(($reviewedChapters / $totalChapters) * 100)
        : 0;
}
?>
    <title>Student Dashboard</title>

<div class="max-w-7xl mx-auto px-6 py-10">

<h1 class="text-4xl font-bold text-[#C2185B] mb-10">
    🎓 Student Dashboard
</h1>

<?php if ($project): ?>

<!-- ================= PROJECT CARD ================= -->
<div class="bg-white rounded-2xl shadow-lg p-8 border border-[#C2185B]/20 mb-10">

    <h2 class="text-2xl font-semibold text-[#C2185B] mb-6">
        📘 Project Information
    </h2>

    <div class="grid md:grid-cols-2 gap-6">

        <div>
            <p class="text-sm text-gray-500">Project Title</p>
            <p class="text-lg font-semibold text-gray-800">
                <?= htmlspecialchars($project['title']); ?>
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Supervisor</p>
            <p class="text-lg font-medium text-gray-700">
                <?= $project['supervisor_name']
                    ? htmlspecialchars($project['supervisor_name'])
                    : '<span class="text-gray-400">Not Assigned</span>'; ?>
            </p>
        </div>
    </div>

    <div class="mt-6">
        <p class="text-sm text-gray-500 mb-2">Description</p>
        <div class="bg-[#C2185B]/5 p-4 rounded-lg text-gray-700">
            <?= nl2br(htmlspecialchars($project['description'])); ?>
        </div>
    </div>

    <!-- STATUS & DEADLINE -->
    <div class="mt-6 flex flex-wrap gap-4 items-center">

        <?php
        $status = $project['status'] ?? 'pending';
        $statusColor = $status === 'approved' ? 'bg-green-100 text-green-700'
                      : ($status === 'rejected' ? 'bg-red-100 text-red-700'
                      : 'bg-yellow-100 text-yellow-700');
        ?>

        <span class="px-4 py-1 rounded-full text-sm font-medium <?= $statusColor ?>">
            <?= ucfirst($status); ?>
        </span>

        <?php if (!empty($project['deadline'])):
            $deadlinePassed = strtotime($project['deadline']) < time();
        ?>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Deadline:</span>
                <span class="font-medium text-gray-800">
                    <?= htmlspecialchars($project['deadline']); ?>
                </span>

                <?php if ($deadlinePassed): ?>
                    <span class="px-3 py-1 rounded-full bg-red-600 text-white text-xs animate-pulse">
                        ⚠ Late
                    </span>
                <?php else: ?>
                    <span class="px-3 py-1 rounded-full bg-[#C2185B]/10 text-[#C2185B] text-xs">
                        Upcoming
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- ================= UPLOAD CHAPTER ================= -->
<div class="bg-white rounded-2xl shadow-lg p-8 border border-[#C2185B]/20 mb-10">

    <h2 class="text-2xl font-semibold text-[#C2185B] mb-6">
        📤 Upload Chapter
    </h2>

    <form action="upload_chapter.php" method="POST" enctype="multipart/form-data"
          class="grid md:grid-cols-3 gap-6 items-end">

        <input type="hidden" name="project_id" value="<?= $project['id']; ?>">

        <div>
            <label class="text-sm text-gray-500">Chapter Number</label>
            <input type="number" name="chapter_number"
                   class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[#C2185B] focus:border-[#C2185B]"
                   required>
        </div>

        <div>
            <label class="text-sm text-gray-500">Upload File (PDF/DOC)</label>
            <input type="file" name="chapter_file"
                   class="w-full p-3 border rounded-lg bg-white"
                   required>
        </div>

        <div>
            <button type="submit"
                class="w-full bg-[#C2185B] text-white p-3 rounded-xl hover:bg-[#A3154C] transition shadow-md">
                Upload
            </button>
        </div>

    </form>

</div>

<!-- ================= PROGRESS ================= -->
<div class="bg-white rounded-2xl shadow-lg p-8 border border-[#C2185B]/20 mb-10">

    <h2 class="text-2xl font-semibold text-[#C2185B] mb-6">
        📊 Project Progress
    </h2>

    <div class="w-full bg-gray-200 rounded-full h-7 overflow-hidden">
        <div class="bg-[#C2185B] h-7 text-white text-sm text-center leading-7 font-medium transition-all duration-500"
             style="width: <?= $progress ?>%;">
            <?= $progress ?>%
        </div>
    </div>

    <p class="mt-3 text-gray-600 text-sm">
        <?= $reviewedChapters ?> of <?= $totalChapters ?> chapters reviewed
    </p>

</div>

<!-- ================= DEFENSE ================= -->
<div class="bg-white rounded-2xl shadow-lg p-8 border border-[#C2185B]/20">

    <h2 class="text-2xl font-semibold text-[#C2185B] mb-6">
        🗓 Defense Schedule
    </h2>

    <?php if ($schedule): ?>
        <div class="grid md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-500">Date</p>
                <p class="font-medium text-gray-800">
                    <?= htmlspecialchars($schedule['defense_date']); ?>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Room</p>
                <p class="font-medium text-gray-800">
                    <?= htmlspecialchars($schedule['room']); ?>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Panel Members</p>
                <p class="font-medium text-gray-800">
                    <?= nl2br(htmlspecialchars($schedule['panel_members'])); ?>
                </p>
            </div>
        </div>
    <?php else: ?>
        <p class="text-gray-500">Defense not scheduled yet.</p>
    <?php endif; ?>

</div>

<?php else: ?>

<div class="bg-white rounded-2xl shadow-lg p-12 text-center border border-[#C2185B]/20">
    <h2 class="text-2xl font-semibold text-[#C2185B] mb-4">
        📌 No Project Created
    </h2>
    <p class="text-gray-600 mb-6">
        You have not created your final year project yet.
    </p>
    <a href="create_project.php"
       class="bg-[#C2185B] text-white px-8 py-3 rounded-xl hover:bg-[#A3154C] transition shadow-md">
        Create Project
    </a>
</div>

<?php endif; ?>

</div>

<?php include("../includes/footer.php"); ?>