<?php
session_start();
require_once "config/database.php";

$error = "";
$success = false;

if (isset($_POST['register'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {

        $check = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $check->execute([':email' => $email]);

        if ($check->rowCount() > 0) {
            $error = "Email already registered!";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role)
                                   VALUES (:name, :email, :password, :role)");

            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashed_password,
                ':role' => $role
            ]);

            $success = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - UMS</title>
<script src="https://cdn.tailwindcss.com"></script>

<style>
.fade-in { animation: fadeIn 1s ease-in-out; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.float { animation: float 4s ease-in-out infinite; }
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-6px); }
    100% { transform: translateY(0px); }
}
.popup-show { animation: popup 0.5s ease forwards; }
@keyframes popup {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
}
</style>

</head>

<body class="min-h-screen flex items-center justify-center text-white
             bg-gradient-to-br from-[#9B0036] via-[#C2185B] to-[#E30B5C]">

<div class="bg-white/10 backdrop-blur-md p-8 rounded-2xl shadow-2xl w-96 fade-in border border-white/20">

    <div class="text-center mb-6 float">
        <img src="logo.jpg" class="w-20 mx-auto mb-3 rounded-full shadow-lg">
        <h2 class="text-2xl font-bold text-white">Create Account</h2>
        <p class="text-pink-100 text-sm">University Project Management System</p>
    </div>

    <?php if($error): ?>
        <p class="bg-red-500/20 text-red-200 p-2 rounded text-center mb-4">
            <?= $error ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <input type="text" name="name" placeholder="Full Name"
        class="w-full p-3 mb-4 rounded-lg bg-white/20 border border-white/30 
               placeholder-white/70 text-white focus:ring-2 focus:ring-white focus:outline-none"
        required>

        <input type="email" name="email" placeholder="Email"
        class="w-full p-3 mb-4 rounded-lg bg-white/20 border border-white/30 
               placeholder-white/70 text-white focus:ring-2 focus:ring-white focus:outline-none"
        required>

        <div class="relative mb-4">
            <input type="password" name="password" id="password"
            placeholder="Password"
            class="w-full p-3 rounded-lg bg-white/20 border border-white/30 
                   placeholder-white/70 text-white focus:ring-2 focus:ring-white focus:outline-none"
            required>
            <button type="button" onclick="togglePassword('password')"
            class="absolute right-3 top-3 text-white/70 hover:text-white">👁</button>
        </div>

        <div class="relative mb-4">
            <input type="password" name="confirm_password" id="confirm_password"
            placeholder="Confirm Password"
            class="w-full p-3 rounded-lg bg-white/20 border border-white/30 
                   placeholder-white/70 text-white focus:ring-2 focus:ring-white focus:outline-none"
            required>
            <button type="button" onclick="togglePassword('confirm_password')"
            class="absolute right-3 top-3 text-white/70 hover:text-white">👁</button>
        </div>

        <select name="role"
        class="w-full p-3 mb-6 rounded-lg bg-white/20 border border-white/30 
               text-white focus:ring-2 focus:ring-white focus:outline-none"
        required>
            <option value="" class="text-black">Select Role</option>
            <option value="student" class="text-black">Student</option>
            <option value="supervisor" class="text-black">Supervisor</option>
        </select>

        <button type="submit" name="register"
        class="w-full bg-white text-[#9B0036] p-3 rounded-lg font-semibold
               hover:bg-[#FDE8EF] transition duration-300 shadow-lg hover:scale-105">
            Register
        </button>

    </form>

    <p class="mt-4 text-center text-sm">
        Already have an account?
        <a href="login.php" class="text-white font-bold hover:underline">
            Login
        </a>
    </p>

</div>

<?php if($success): ?>
<!-- SUCCESS POPUP -->
<div id="successPopup"
     class="fixed inset-0 bg-black/60 flex items-center justify-center">

    <div class="bg-white text-[#9B0036] p-8 rounded-2xl shadow-2xl text-center popup-show">
        <div class="text-4xl mb-4">✅</div>
        <h3 class="text-xl font-bold mb-2">Registration Successful!</h3>
        <p class="text-gray-600 mb-4">
            Redirecting to login page...
        </p>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div id="progressBar"
                 class="bg-[#E30B5C] h-2 rounded-full"
                 style="width:0%"></div>
        </div>
    </div>
</div>

<script>
let width = 0;
let interval = setInterval(function(){
    width += 2;
    document.getElementById("progressBar").style.width = width + "%";
    if(width >= 100){
        clearInterval(interval);
        window.location.href = "login.php";
    }
}, 60);
</script>
<?php endif; ?>

<script>
function togglePassword(fieldId){
    const field = document.getElementById(fieldId);
    field.type = field.type === "password" ? "text" : "password";
}
</script>

</body>
</html>