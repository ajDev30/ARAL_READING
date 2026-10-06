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

$level = $user['current_level']; // 'Pending', 'Independent', 'Instructional', 'Frustration', 'Completed Profile'
$mname = $user['mname'];

// Fetch Phil-IRI Assessment Status
$stmt = $pdo->prepare("SELECT created_at FROM reading_attempts WHERE user_id = ? AND phase = 'Pre-Test' ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$phil_iri = $stmt->fetch();
$phil_iri_date = $phil_iri ? date('M d, Y', strtotime($phil_iri['created_at'])) : 'Pending';
$phil_iri_badge = $phil_iri ? 'Completed' : 'Pending';

// Fetch Course Pre-Test
$stmt = $pdo->prepare("SELECT created_at, oral_reading_profile FROM reading_attempts WHERE user_id = ? AND phase = 'Course-Pre-Test' ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$course_pre = $stmt->fetch();
$course_pre_date = $course_pre ? date('M d, Y', strtotime($course_pre['created_at'])) : 'Pending';
$course_pre_badge = $course_pre ? $course_pre['oral_reading_profile'] : 'Not Taken';

// Fetch Course Post-Test
$stmt = $pdo->prepare("SELECT created_at, oral_reading_profile FROM reading_attempts WHERE user_id = ? AND phase = 'Course-Post-Test' ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$course_post = $stmt->fetch();
$course_post_date = $course_post ? date('M d, Y', strtotime($course_post['created_at'])) : 'Pending';
$course_post_badge = $course_post ? $course_post['oral_reading_profile'] : 'Not Taken';

$mi = !empty($mname) ? strtoupper(substr($mname, 0, 1)) . '.' : '';
$fullName = htmlspecialchars($user['fname'] . ' ' . $mi . ' ' . $user['lname']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Phil-IRI</title>
    <script>
        const originalWarn = console.warn;
        console.warn = function() {
            if (arguments[0] && typeof arguments[0] === 'string' && arguments[0].includes('cdn.tailwindcss.com should not be used in production')) return;
            originalWarn.apply(console, arguments);
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
        .sidebar { background-color: #1a365d; }
    
        
                <?php $is_dark = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark'; ?>
        <?php if($is_dark): ?>
        /* Refined Slate Dark Mode */
        body { background-color: #0f172a !important; color: #f8fafc !important; }
        .bg-white, .bg-slate-50 { background-color: #1e293b !important; border-color: #334155 !important; color: #f8fafc !important; }
        
        .text-slate-800, .text-slate-700 { color: #f1f5f9 !important; }
        .text-slate-600, .text-slate-500, .text-slate-400 { color: #cbd5e1 !important; }
        .border-slate-200, .border-slate-100, .border-b { border-color: #334155 !important; }
        .border-slate-300 { border-color: #475569 !important; }
        .sidebar { background-color: #0b1120 !important; border-right: 1px solid #1e293b !important; }
        input, select, textarea { background-color: #0f172a !important; color: white !important; border-color: #475569 !important; }
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.5) !important; }

        /* Colored Badges / Cards Fixes */
        .bg-blue-50, .bg-blue-100 { background-color: rgba(59, 130, 246, 0.2) !important; color: #93c5fd !important; }
        .text-blue-600, .text-blue-700, .text-blue-800 { color: #60a5fa !important; }
        .border-blue-100, .border-blue-200 { border-color: rgba(59, 130, 246, 0.3) !important; }

        .bg-emerald-50, .bg-emerald-100 { background-color: rgba(16, 185, 129, 0.2) !important; color: #6ee7b7 !important; }
        .text-emerald-600, .text-emerald-700, .text-emerald-800 { color: #34d399 !important; }
        .border-emerald-100, .border-emerald-200 { border-color: rgba(16, 185, 129, 0.3) !important; }

        .bg-amber-50, .bg-amber-100 { background-color: rgba(245, 158, 11, 0.2) !important; color: #fcd34d !important; }
        .text-amber-600, .text-amber-700, .text-amber-800 { color: #fbbf24 !important; }
        .border-amber-100, .border-amber-200 { border-color: rgba(245, 158, 11, 0.3) !important; }

        .bg-rose-50, .bg-rose-100 { background-color: rgba(244, 63, 94, 0.2) !important; color: #fda4af !important; }
        .text-rose-600, .text-rose-700, .text-rose-800 { color: #fb7185 !important; }
        .border-rose-100, .border-rose-200 { border-color: rgba(244, 63, 94, 0.3) !important; }
        
        .bg-purple-50, .bg-purple-100 { background-color: rgba(168, 85, 247, 0.2) !important; color: #d8b4fe !important; }
        .text-purple-600, .text-purple-700, .text-purple-800 { color: #c084fc !important; }
        
        
        /* Bug Fixes for "Not Taken", "Pre-Test", and Table Hovers */
        .bg-slate-100, .bg-slate-200 { background-color: #334155 !important; color: #e2e8f0 !important; }
        .hover\:bg-slate-50:hover { background-color: #334155 !important; }
        tr:hover { background-color: #334155 !important; }
/* Logo Fix */
        .sidebar img { background-color: transparent !important; filter: drop-shadow(0px 0px 2px rgba(255,255,255,0.5)) !important; }
        <?php endif; ?>
    </style>




    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <meta name="htmx-config" content='{"globalViewTransitions":true}'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="flex h-screen overflow-hidden text-slate-800">
    <?php include 'student_sidebar.php'; ?>

    <!-- Sidebar -->
    
    

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Header -->
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 shrink-0">
            <div class="flex items-center">
                <button onclick="toggleSidebar()" class="md:hidden mr-4 text-slate-500 hover:text-blue-600 focus:outline-none"><i class="fas fa-bars text-xl"></i></button>
                <div>
                <h2 class="font-bold text-2xl text-slate-800">Hello, <?php echo $fullName; ?>!</h2>
                <p class="text-sm text-slate-500"><?php echo htmlspecialchars($user['grade_level']); ?> &middot; Section <?php echo htmlspecialchars($user['section']); ?></p>
            </div>
            </div>
            <div class="flex items-center space-x-3">
                <?php 
                $avatarPath = 'uploads/profiles/user_' . $_SESSION['user_id'] . '.jpg';
                if (file_exists($avatarPath)): 
                ?>
                    <img src="<?php echo $avatarPath; ?>?t=<?php echo time(); ?>" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 keep-colors font-bold">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <span class="font-semibold text-sm"><?php echo $fullName; ?> <i class="fas fa-chevron-down text-xs ml-1 text-slate-400"></i></span>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-slate-50">
            
            <?php if($level === 'Pending'): ?>
                <!-- PENDING STATE (Only screening allowed) -->
                <div class="max-w-3xl mx-auto">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-8 text-center">
                        <img src="https://tibungcodistrict.wordpress.com/wp-content/uploads/2018/06/untitled-1.png?w=490" class="h-24 mx-auto mb-6">
                        <h2 class="text-2xl font-bold text-slate-800 mb-2">Welcome to your Reading Assessment</h2>
                        <p class="text-slate-600 mb-8 max-w-lg mx-auto">This test will help determine your current reading level. It is the first step in your reading journey.</p>
                        
                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 text-left flex flex-col md:flex-row md:items-center justify-between mb-8">
                            <div>
                                <h3 class="font-bold text-blue-800 text-lg">English GST (Group Screening Test)</h3>
                                <p class="text-sm text-blue-600 mt-1">Make sure you are in a quiet room and your microphone is working.</p>
                            </div>
                            <div class="text-4xl text-blue-300 ml-4"><i class="fas fa-microphone-alt"></i></div>
                        </div>

                        <a href="assessment.php" hx-boost="false" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 md:px-8 rounded-lg transition shadow-md text-lg">
                            Start English GST <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <!-- DASHBOARD STATE (Independent or Instructional/Frustration) -->
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
                    <!-- My Reading Level -->
                    <div class="col-span-1 lg:col-span-4 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
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
                    
                    <!-- Assessment History -->
                    <div class="col-span-1 lg:col-span-4 bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
                        <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center"><i class="far fa-calendar-alt mr-2"></i> Assessment History</h3>
                        
                        <div class="space-y-3">
                            <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-100 pb-3">
                                <div>
                                    <h4 class="font-bold text-sm text-slate-800">Phil-IRI Pre-Assessment</h4>
                                    <p class="text-xs text-slate-500">Date: <?php echo $phil_iri_date; ?></p>
                                </div>
                                <span class="px-3 py-1 <?php echo $phil_iri ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'; ?> rounded-full text-xs font-medium"><?php echo $phil_iri_badge; ?></span>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-100 pb-3">
                                <div>
                                    <h4 class="font-bold text-sm text-slate-800">Course Pre-Test</h4>
                                    <p class="text-xs text-slate-500">Date: <?php echo $course_pre_date; ?></p>
                                </div>
                                <span class="px-3 py-1 <?php echo $course_pre ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500'; ?> rounded-full text-xs font-medium"><?php echo htmlspecialchars($course_pre_badge); ?></span>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-center justify-between">
                                <div>
                                    <h4 class="font-bold text-sm text-slate-800">Course Post-Test</h4>
                                    <p class="text-xs text-slate-500">Date: <?php echo $course_post_date; ?></p>
                                </div>
                                <span class="px-3 py-1 <?php echo $course_post ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-500'; ?> rounded-full text-xs font-medium"><?php echo htmlspecialchars($course_post_badge); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Reading Level Guide -->
                    <div class="col-span-1 lg:col-span-4 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                        <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center"><i class="far fa-map mr-2"></i> Reading Level Guide</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
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
                    <div class="bg-blue-600 rounded-xl shadow-sm p-4 md:p-8 text-white flex flex-col md:flex-row md:items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold mb-2">Ready to improve your reading?</h3>
                            <p class="text-blue-100">Access your personalized course materials, reading practice, and activities.</p>
                        </div>
                        <a href="student_course.php" class="bg-white text-blue-600 font-bold py-3 px-6 rounded-lg hover:bg-blue-50 transition shadow">
                            Go to Course <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="bg-emerald-600 rounded-xl shadow-sm p-4 md:p-8 text-white flex flex-col md:flex-row md:items-center justify-between">
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
