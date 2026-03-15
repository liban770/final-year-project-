<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../login.php");
    exit();
}

$message = "";

if (isset($_POST['create'])) {

    $student_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    $stmt = $pdo->prepare("
        INSERT INTO projects (student_id, title, description)
        VALUES (?, ?, ?)
    ");

    if ($stmt->execute([$student_id, $title, $description])) {
        $message = "Project created successfully!";
    } else {
        $message = "Something went wrong.";
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
<h1 class="text-3xl font-semibold text-[#C2185B]">Create Project</h1>
<p class="text-gray-500 text-sm">Submit your academic project proposal</p>
</div>

<?php if ($message): ?>
<div class="bg-[#C2185B] text-white px-4 py-3 rounded-lg mb-6 shadow">
<?= htmlspecialchars($message); ?>
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

<!-- Submit -->
<div>
<button
type="submit"
name="create"
class="bg-[#C2185B] hover:bg-[#A3154C] text-white px-6 py-2 rounded-lg transition">
Create Project
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