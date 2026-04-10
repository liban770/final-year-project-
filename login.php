<?php
session_start();
require_once "config/database.php";

$error = "";

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        if(isset($_POST['remember'])){
            setcookie("user_email", $email, time() + (86400 * 30), "/");
        }

        header("Location: index.php");
        exit();

    } 
    else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - ThesisTrack</title>
<script src="https://cdn.tailwindcss.com"></script>

<style>
.fade-in {
    animation: fadeIn 1.2s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.float {
    animation: float 4s ease-in-out infinite;
}
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-6px); }
    100% { transform: translateY(0px); }
}
</style>

</head>

<body class="min-h-screen flex items-center justify-center text-white
             bg-gradient-to-br from-[#9B0036] via-[#C2185B] to-[#E30B5C]">

<div class="bg-white/10 backdrop-blur-md p-8 rounded-2xl shadow-2xl w-96 fade-in border border-white/20">

    <!-- University Logo -->
    <div class="text-center mb-6 float">
        <img src="assets/logo.jpg" class="w-20 mx-auto mb-3 rounded-full shadow-lg">
        <h2 class="text-2xl font-bold text-white">ATU Login</h2>
    </div>

    <?php if($error): ?>
        <p class="bg-red-500/20 text-red-200 p-2 rounded text-center mb-4">
            <?php echo $error; ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <input type="email" name="email" placeholder="Email"
        value="<?php echo $_COOKIE['user_email'] ?? ''; ?>"
        class="w-full p-3 mb-4 rounded-lg bg-white/20 border border-white/30 
               placeholder-white/70 text-white focus:ring-2 focus:ring-white focus:outline-none"
        required>

        <div class="relative mb-4">
            <input type="password" name="password" id="password"
            placeholder="Password"
            class="w-full p-3 rounded-lg bg-white/20 border border-white/30 
                   placeholder-white/70 text-white focus:ring-2 focus:ring-white focus:outline-none"
            required>

            <button type="button" onclick="togglePassword()"
            class="absolute right-3 top-3 text-white/70 hover:text-white">
                👁
            </button>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center mb-4 text-sm">
            <input type="checkbox" name="remember" class="mr-2 accent-[#E30B5C]">
            <label>Remember Me</label>
        </div>

        <button type="submit" name="login"
        class="w-full bg-white text-[#9B0036] p-3 rounded-lg font-semibold
               hover:bg-[#FDE8EF] transition duration-300 shadow-lg hover:scale-105">
            Login
        </button>

        <p class="mt-4 text-center text-sm">
            Don't have an account?
            <a href="register.php" class="text-white font-bold hover:underline">
                Register
            </a>
        </p>

    </form>

</div>

<script>
function togglePassword(){
    const pass = document.getElementById("password");
    pass.type = pass.type === "password" ? "text" : "password";
}
</script>

</body>
</html>