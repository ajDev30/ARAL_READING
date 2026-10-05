<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

$level = $user['current_level']; // 'Pending', 'Independent', 'Instructional', 'Frustration'
$mname = $user['mname'];
$mi = !empty($mname) ? strtoupper(substr($mname, 0, 1)) . '.' : '';
$fullName = htmlspecialchars($user['fname'] . ' ' . $mi . ' ' . $user['lname']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Phil-IRI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
        .sidebar { background-color: #1a365d; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-slate-800">

    <!-- Sidebar -->
    <aside class="sidebar w-64 text-white flex flex-col shrink-0 relative">
        <div class="p-6">
            <img src="https://tibungcodistrict.wordpress.com/wp-content/uploads/2018/06/untitled-1.png?w=490" class="h-10 object-contain bg-white rounded p-1 mb-2">
            <h1 class="font-bold text-lg leading-tight">Phil-IRI</h1>
            <p class="text-xs text-blue-200">Student Portal</p>
        </div>
        
        <nav class="flex-1 mt-4">
            <ul class="space-y-1">
                <?php if($level !== 'Pending'): ?>
                    <li><a href="dashboard_student.php" class="flex items-center px-6 py-3 bg-blue-600 border-l-4 border-white"><i class="fas fa-home w-6"></i> Dashboard</a></li>
                    
                    <?php if($level !== 'Independent'): ?>
                        <li><a href="student_course.php" class="flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 transition"><i class="fas fa-book-reader w-6"></i> Course</a></li>
                    <?php endif; ?>
                <?php endif; ?>
                
                <li class="<?php echo ($level !== 'Pending') ? 'mt-8' : ''; ?>"><a href="#" class="flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 transition"><i class="fas fa-cog w-6"></i> Settings</a></li>
                <li><a href="logout.php" class="flex items-center px-6 py-3 text-red-300 hover:bg-blue-800 transition"><i class="fas fa-sign-out-alt w-6"></i> Logout</a></li>
            </ul>
        </nav>
        
        <div class="absolute bottom-0 left-0 w-full p-6 opacity-20 pointer-events-none">
            <svg viewBox="0 0 100 100" class="w-full h-auto fill-current"><path d="M0,50 Q25,25 50,50 T100,50 L100,100 L0,100 Z"></path></svg>
        </div>
        <div class="absolute bottom-8 left-6 text-xs text-blue-200 opacity-50 italic">"Better reading builds a brighter future."</div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Header -->
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0">
            <div>
                <h2 class="font-bold text-2xl text-slate-800">Hello, <?php echo $fullName; ?>!</h2>
                <p class="text-sm text-slate-500"><?php echo htmlspecialchars($user['grade_level']); ?> &middot; Section <?php echo htmlspecialchars($user['section']); ?></p>
            </div>
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                    <i class="fas fa-user"></i>
                </div>
                <span class="font-semibold text-sm"><?php echo $fullName; ?> <i class="fas fa-chevron-down text-xs ml-1 text-slate-400"></i></span>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto p-8 bg-slate-50">
            
            <?php if($level === 'Pending'): ?>
                <!-- PENDING STATE (Only screening allowed) -->
                <div class="max-w-3xl mx-auto">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center">
                        <img src="https://tibungcodistrict.wordpress.com/wp-content/uploads/2018/06/untitled-1.png?w=490" class="h-24 mx-auto mb-6">
                        <h2 class="text-2xl font-bold text-slate-800 mb-2">Welcome to your Reading Assessment</h2>
                        <p class="text-slate-600 mb-8 max-w-lg mx-auto">This test will help determine your current reading level. It is the first step in your reading journey.</p>
                        
                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 text-left flex items-center justify-between mb-8">
                            <div>
                                <h3 class="font-bold text-blue-800 text-lg">English GST (Group Screening Test)</h3>
                                <p class="text-sm text-blue-600 mt-1">Make sure you are in a quiet room and your microphone is working.</p>
                            </div>
                            <div class="text-4xl text-blue-300 ml-4"><i class="fas fa-microphone-alt"></i></div>
                        </div>

                        <a href="assessment.php" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition shadow-md text-lg">
                            Start English GST <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <!-- DASHBOARD STATE (Independent or Instructional/Frustration) -->
                
                <div class="grid grid-cols-12 gap-6 mb-6">
                    <!-- My Reading Level -->
                    <div class="col-span-4 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                        <h3 class="font-bold text-slate-700 mb-4 text-sm">My Reading Level (Current)</h3>
                        <?php 
                            $bgClass = $level == 'Independent' ? 'bg-emerald-50 border-emerald-100' : ($level == 'Instructional' ? 'bg-amber-50 border-amber-100' : 'bg-rose-50 border-rose-100');
                            $textClass = $level == 'Independent' ? 'text-emerald-700' : ($level == 'Instructional' ? 'text-amber-700' : 'text-rose-700');
                            $iconClass = $level == 'Independent' ? 'text-emerald-500' : ($level == 'Instructional' ? 'text-amber-500' : 'text-rose-500');
                        ?>
                        <div class="<?php echo $bgClass; ?> border rounded-xl p-5 flex items-start">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-xl shadow-sm mr-4 shrink-0 <?php echo $iconClass; ?>">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <div>
                                <h4 class="text-2xl font-bold <?php echo $textClass; ?>"><?php echo $level; ?></h4>
                                <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                                    You are currently in the <strong><?php echo $level; ?></strong> level.
                                    <?php if($level !== 'Independent'): ?>
                                        Keep reading and practicing to reach the <strong>Independent</strong> level!
                                    <?php else: ?>
                                        Excellent work! You can read and understand well on your own.
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- My Latest Assessment -->
                    <div class="col-span-4 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                        <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center"><i class="far fa-calendar-alt mr-2"></i> My Latest Assessment</h3>
                        
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                            <div>
                                <h4 class="font-bold text-sm text-slate-800">Pre-Test <span class="text-xs text-slate-400 font-normal">(Completed)</span></h4>
                                <p class="text-xs text-slate-500">Date: <?php echo date('M d, Y'); ?></p>
                            </div>
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-medium"><?php echo $level; ?></span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-sm text-slate-800">Post-Test <span class="text-xs text-slate-400 font-normal">(Not yet taken)</span></h4>
                                <p class="text-xs text-slate-500">Date: Pending</p>
                            </div>
                            <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-xs font-medium">Pending</span>
                        </div>
                    </div>

                    <!-- Reading Level Guide -->
                    <div class="col-span-4 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                        <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center"><i class="far fa-map mr-2"></i> Reading Level Guide</h3>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-emerald-50 rounded p-2 text-center">
                                <div class="text-[10px] font-bold text-emerald-700 mb-1"><i class="fas fa-book"></i> Independent</div>
                                <p class="text-[9px] text-slate-600 leading-tight">Can read and understand well on their own.</p>
                            </div>
                            <div class="bg-amber-50 rounded p-2 text-center">
                                <div class="text-[10px] font-bold text-amber-700 mb-1"><i class="fas fa-book-open"></i> Instructional</div>
                                <p class="text-[9px] text-slate-600 leading-tight">Needs some support and guidance.</p>
                            </div>
                            <div class="bg-rose-50 rounded p-2 text-center">
                                <div class="text-[10px] font-bold text-rose-700 mb-1"><i class="fas fa-exclamation-triangle"></i> Frustration</div>
                                <p class="text-[9px] text-slate-600 leading-tight">Finds reading very difficult.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if($level !== 'Independent'): ?>
                    <!-- "Course" Promo Area -->
                    <div class="bg-blue-600 rounded-xl shadow-sm p-8 text-white flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold mb-2">Ready to improve your reading?</h3>
                            <p class="text-blue-100">Access your personalized course materials, reading practice, and activities.</p>
                        </div>
                        <a href="student_course.php" class="bg-white text-blue-600 font-bold py-3 px-6 rounded-lg hover:bg-blue-50 transition shadow">
                            Go to Course <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="bg-emerald-600 rounded-xl shadow-sm p-8 text-white flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold mb-2">Congratulations on reaching Independent!</h3>
                            <p class="text-emerald-100">You do not need to take the intervention courses. Keep reading books you enjoy!</p>
                        </div>
                        <div class="text-5xl opacity-50"><i class="fas fa-medal"></i></div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        </main>
    </div>

</body>
</html>
