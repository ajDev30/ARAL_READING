<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Anti-SQL Injection: Using prepared statements
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['fname'] = $user['fname'];

        // Role-Based Access Control (RBAC)
        if ($user['role'] === 'teacher') {
            header("Location: dashboard_teacher.php");
        } else {
            header("Location: dashboard_student.php");
        }
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Phil-IRI System</title>
    <!-- Tailwind CSS -->
    <script>
        const originalWarn = console.warn;
        console.warn = function() {
            if (arguments[0] && typeof arguments[0] === 'string' && arguments[0].includes('cdn.tailwindcss.com should not be used in production')) return;
            originalWarn.apply(console, arguments);
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
    .sidebar { background-color: #1a365d; }
    </style>

    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <meta name="htmx-config" content='{"globalViewTransitions":true}'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-8">
        
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <img src="https://tibungcodistrict.wordpress.com/wp-content/uploads/2018/06/untitled-1.png?w=490" alt="Logo" class="mx-auto h-24 mb-4 object-contain">
            <h2 class="text-2xl font-bold text-slate-800">Welcome Back</h2>
            <p class="text-sm text-slate-500 mt-1">Sign in to the Phil-IRI Assessment System</p>
        </div>

        <!-- Error Message -->
        <?php if($error): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-lg mb-6 text-sm flex items-start">
            <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST" action="" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-user"></i>
                    </div>
                    <input type="text" name="username" required 
                        class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition">
                </div>
            </div>
            
            <div>
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-slate-700">Password</label>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" name="password" required 
                        class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition">
                </div>
            </div>
            
            <button type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition duration-150 shadow-sm">
                Sign In
            </button>
        </form>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-slate-500">
            Don't have an account? 
            <a href="register.php" class="text-blue-600 hover:text-blue-700 font-medium hover:underline">Register here</a>
        </div>
    </div>


    <script>
        window.toggleSidebar = function() {
            const sidebar = document.getElementById('appSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if(sidebar) sidebar.classList.toggle('-translate-x-full');
            if(overlay) overlay.classList.toggle('hidden');
        }
    </script>
</body>
</html>
