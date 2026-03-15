<?php
session_start();

if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] == "admin") {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['role'] == "supervisor") {
        header("Location: supervisor/dashboard.php");
    } else {
        header("Location: student/dashboard.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>UMS Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* Smooth fade-in animation */
        .fade-in {
            animation: fadeIn 1.2s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Floating effect for heading */
        .float {
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center text-white 
             bg-gradient-to-br from-[#9B0036] via-[#C2185B] to-[#E30B5C]">

    <div class="text-center max-w-3xl px-6 fade-in">

        <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight float">
            Final Year Project <br>
            <span class="text-[#FDE8EF]">Management System</span>
        </h1>

        <p class="text-lg text-pink-100 mb-10 transition duration-700 hover:text-white">
            A centralized platform to manage student projects, supervisors, 
            defense scheduling and academic progress efficiently.
        </p>

        <div class="space-x-4">

            <a href="login.php"
               class="bg-white text-[#9B0036] hover:bg-[#FDE8EF] 
                      px-8 py-3 rounded-lg font-semibold 
                      transition duration-300 shadow-xl 
                      hover:scale-105 hover:shadow-2xl inline-block">
               Login
            </a>

            <a href="about.php"
               class="border border-white px-8 py-3 rounded-lg font-semibold 
                      hover:bg-white hover:text-[#9B0036] 
                      transition duration-300 
                      hover:scale-105 inline-block">
               Learn More
            </a>

        </div>

    </div>

</body>
</html>