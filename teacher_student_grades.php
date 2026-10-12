<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

$student_id = $_GET['user_id'] ?? 0;

$stmt = $pdo->prepare("SELECT fname, lname, grade_level, section FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Student not found.");
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Grades - <?php echo htmlspecialchars($student['fname']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #F8FAFC; }
        .sidebar { background-color: #1a365d; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-slate-800">
    <?php include 'teacher_sidebar.php'; ?>
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 shrink-0">
            <div class="flex items-center">
                <button onclick="toggleSidebar()" class="md:hidden mr-4 text-slate-500 hover:text-blue-600 focus:outline-none"><i class="fas fa-bars text-xl"></i></button>
                <a href="teacher_course_grades.php" class="text-slate-400 hover:text-slate-600 mr-4"><i class="fas fa-arrow-left"></i></a>
                <h1 class="font-bold text-lg text-slate-800">Course Grades: <?php echo htmlspecialchars($student['fname'] . ' ' . $student['lname']); ?></h1>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto max-w-6xl mx-auto w-full">
            <?php 
                $target_user_id = $student_id;
                include 'comparison_widget.php';
            ?>
        </main>
    </div>
    <script>
        window.toggleSidebar = function() {
            const sidebar = document.getElementById('appSidebar');
            if(sidebar) sidebar.classList.toggle('-translate-x-full');
        }
    </script>
</body>
</html>
