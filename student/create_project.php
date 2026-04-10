<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../login.php");
    exit();
}

$message = "";
$group = null;
$groupMembers = [];

// Check if student is already in a group
$studentStmt = $pdo->prepare("
    SELECT u.id, u.name, u.group_id, pg.group_name, pg.group_code, pg.member_names
    FROM users u
    LEFT JOIN project_groups pg ON u.group_id = pg.id
    WHERE u.id = ?
");
$studentStmt->execute([$_SESSION['user_id']]);
$studentData = $studentStmt->fetch(PDO::FETCH_ASSOC);

if ($studentData && !empty($studentData['group_id'])) {
    $group = $studentData;

    $membersStmt = $pdo->prepare("
        SELECT id, name, email
        FROM users
        WHERE role = 'student' AND group_id = ?
        ORDER BY name ASC
    ");
    $membersStmt->execute([$group['group_id']]);
    $groupMembers = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
}

// No dropdown needed anymore, handling explicit string names in the form

if (isset($_POST['create'])) {
    if ($group) {
        // Validation if already in group
        $message = "Your group already exists. Only one project per group allowed.";
    } else {
        $title = trim($_POST['title'] ?? "");
        $description = trim($_POST['description'] ?? "");
        $groupName = trim($_POST['group_name'] ?? "");
        $selectedMembers = $_POST['members'] ?? [];

        if ($title === "" || $description === "" || $groupName === "") {
            $message = "Project Title, Description, and Group Name are required.";
        } else {
            try {
                $pdo->beginTransaction();

                // 1. Create the Group
                $groupCode = 'GRP-' . strtoupper(substr(md5(uniqid()), 0, 6));
                $memberNames = trim($_POST['partner_names'] ?? "");

                $stmtGroup = $pdo->prepare("INSERT INTO project_groups (group_name, group_code, member_names) VALUES (?, ?, ?)");
                $stmtGroup->execute([$groupName, $groupCode, $memberNames]);
                $newGroupId = $pdo->lastInsertId();

                // 2. Assign the leader (current student) to group
                $stmtUser = $pdo->prepare("UPDATE users SET group_id = ? WHERE id = ?");
                $stmtUser->execute([$newGroupId, $_SESSION['user_id']]);

                // 3. (Optional) We no longer strictly associate other user accounts, since they typed names.

                // 4. Create the Project
                $stmtProject = $pdo->prepare("
                    INSERT INTO projects (group_id, student_id, title, description)
                    VALUES (?, ?, ?, ?)
                ");
                $stmtProject->execute([$newGroupId, $_SESSION['user_id'], $title, $description]);

                $pdo->commit();
                $message = "Group and Project submitted successfully!";
                
                // Refresh the page to show the new group
                header("Location: create_project.php");
                exit();
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $message = "Error submitting project: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Create Project</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

<div class="flex">

<!-- ================= SIDEBAR ================= -->
<aside class="w-64 bg-[#C2185B] text-white min-h-screen p-6 hidden md:block">

<h2 class="text-2xl font-bold mb-8">Student Panel</h2>

<nav class="space-y-3">

<a href="dashboard.php" class="block px-3 py-2 rounded-lg hover:bg-white/20">
Dashboard
</a>

<a href="create_project.php" class="block px-3 py-2 rounded-lg bg-white text-[#C2185B] font-semibold">
Create Project
</a>

<a href="../logout.php"
class="block px-3 py-2 rounded-lg bg-white text-[#C2185B] text-center mt-6">
Logout
</a>

</nav>
</aside>

<!-- ================= MAIN CONTENT ================= -->
<div class="flex-1 p-6">

<div class="mb-8">
<h1 class="text-3xl font-semibold text-[#C2185B]">Create Group Project</h1>
<p class="text-gray-500 text-sm">Submit your academic project proposal as a group</p>
</div>

<?php if ($message): ?>
<div class="bg-[#C2185B] text-white px-4 py-3 rounded-lg mb-6 shadow">
<?= htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<?php if ($group): ?>
<div class="bg-white shadow-lg rounded-xl p-6 mb-6 max-w-2xl">
<h2 class="text-lg font-semibold text-[#C2185B] mb-2">Group Information</h2>
<p class="text-sm text-gray-600">
    Group: <span class="font-semibold"><?= htmlspecialchars($group['group_name']); ?></span>
    (<?= htmlspecialchars($group['group_code']); ?>)
</p>
<p class="text-sm text-gray-600 mt-2">Members:</p>
<ul class="list-disc list-inside text-sm text-gray-700 mt-1">
    <?php foreach ($groupMembers as $member): ?>
        <li><?= htmlspecialchars($member['name']); ?> (Registered User)</li>
    <?php endforeach; ?>
    <?php if (!empty($group['member_names'])): ?>
        <li class="mt-2 text-gray-600 italic">Added Partners: <br> <?= nl2br(htmlspecialchars($group['member_names'])); ?></li>
    <?php endif; ?>
</ul>
</div>
<?php else: ?>
<div class="bg-blue-100 text-blue-800 px-4 py-3 rounded-lg mb-6 shadow max-w-2xl border-l-4 border-blue-500">
    You will automatically create a new group upon project submission.
</div>
<?php endif; ?>

<!-- ================= FORM CARD ================= -->
<div class="bg-white shadow-lg rounded-xl p-8 max-w-2xl">

<form method="POST" class="space-y-6">

<!-- Project Title -->
<div>
<label class="block text-sm font-medium text-gray-600 mb-2">
Project Title
</label>

<input 
type="text"
name="title"
id="projectTitle"
required
class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C2185B] focus:outline-none"
>

<!-- Buttons -->
<div class="flex gap-3 mt-3">

<button
type="button"
onclick="getSuggestions()"
class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-black">
Suggest Ideas
</button>

<button
type="button"
onclick="checkSimilarity()"
class="bg-orange-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-orange-600">
Check Similarity
</button>

</div>

</div>

<!-- AI Suggestions -->
<div id="suggestionsBox" class="hidden bg-gray-50 border rounded-lg p-4">

<h3 class="font-semibold text-gray-700 mb-2">
AI Suggested Project Ideas
</h3>

<ul id="suggestionsList" class="space-y-2 text-sm text-gray-700"></ul>

</div>

<!-- Similarity Results -->
<div id="similarityBox" class="hidden bg-red-50 border border-red-200 rounded-lg p-4">

<h3 class="font-semibold text-red-600 mb-2">
Similar Projects Found
</h3>

<ul id="similarityList" class="space-y-2 text-sm"></ul>

</div>

<!-- Description -->
<div>
<label class="block text-sm font-medium text-gray-600 mb-2">
Description
</label>

<textarea
name="description"
rows="5"
required
class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C2185B] focus:outline-none">
</textarea>
</div>

<?php if (!$group): ?>
<!-- Group Name -->
<div>
    <label class="block text-sm font-medium text-gray-600 mb-2">Group Name</label>
    <input type="text" name="group_name" required placeholder="e.g. Phoenix Team"
           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C2185B] focus:outline-none">
</div>

<!-- Write Members Names -->
<div>
    <label class="block text-sm font-medium text-gray-600 mb-2">Group Members Names</label>
    <textarea name="partner_names" rows="3" required placeholder="Write all group members here (e.g. John Doe, Sarah Smith, Michael Lee)"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C2185B] focus:outline-none"></textarea>
    <p class="text-xs text-gray-500 mt-1">Please enter the full names of all your group partners.</p>
</div>
<?php endif; ?>

<!-- Submit -->
<div class="pt-4">
    <button type="submit" name="create" class="bg-[#C2185B] hover:bg-[#A3154C] text-white px-6 py-2 rounded-lg transition shadow-md w-full sm:w-auto">
        <?= $group ? "Submit Project (As " . htmlspecialchars($group['group_name']) . ")" : "Create Group & Submit Project" ?>
    </button>
</div>

</form>

</div>

</div>

</div>

<!-- ================= JAVASCRIPT ================= -->

<script>

function getSuggestions(){

let title = document.getElementById("projectTitle").value;

if(title.trim()===""){
alert("Please enter project keywords first");
return;
}

fetch("get_ai_suggestions.php",{

method:"POST",
headers:{
"Content-Type":"application/x-www-form-urlencoded"
},
body:"keyword="+encodeURIComponent(title)

})

.then(res=>res.json())

.then(data=>{

let box=document.getElementById("suggestionsBox");
let list=document.getElementById("suggestionsList");

list.innerHTML="";

data.forEach(function(item){

let li=document.createElement("li");

li.className="cursor-pointer hover:text-[#C2185B]";
li.innerText=item;

li.onclick=function(){
document.getElementById("projectTitle").value=item;
};

list.appendChild(li);

});

box.classList.remove("hidden");

});

}

function checkSimilarity(){

let title=document.getElementById("projectTitle").value;

if(title.trim()===""){
alert("Enter project title first");
return;
}

fetch("check_similarity.php",{

method:"POST",
headers:{
"Content-Type":"application/x-www-form-urlencoded"
},
body:"title="+encodeURIComponent(title)

})

.then(res=>res.json())

.then(data=>{

let box=document.getElementById("similarityBox");
let list=document.getElementById("similarityList");

list.innerHTML="";

if(data.length===0){

list.innerHTML="<li class='text-green-600'>No similar projects found ✅</li>";

}

else{

data.forEach(function(project){

let li=document.createElement("li");

li.innerHTML=project.title+
" <span class='text-red-600'>("+project.similarity+"% similar)</span>";

list.appendChild(li);

});

}

box.classList.remove("hidden");

});

}

</script>

</body>
</html>