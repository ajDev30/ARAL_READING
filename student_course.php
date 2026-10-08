<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$level = $user['current_level'];

if ($level === 'Independent') {
    die("You have reached Independent level and do not need to take these courses.");
}

// Fetch student's target grade from profile
$stmt = $pdo->prepare("SELECT * FROM reading_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

$target_grade = null;
$student_profile_type = null;
if ($profile) {
    if ($profile['instructional_grade'] !== null) {
        $target_grade = $profile['instructional_grade'];
        $student_profile_type = 'Instructional';
    } elseif ($profile['frustration_grade'] !== null) {
        $target_grade = $profile['frustration_grade'];
        $student_profile_type = 'Frustration';
    }
}

// Fetch published course assessments matching the target grade
$stmt = $pdo->query("SELECT * FROM course_assessments WHERE status = 'Published' ORDER BY created_at ASC");
$all_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$courses = [];
if ($target_grade !== null && $student_profile_type !== null) {
    foreach ($all_courses as $c) {
        if ($c['target_profile'] === $student_profile_type && preg_match('/GRADE ' . $target_grade . '\b/i', $c['title'])) {
            $courses[] = $c;
        }
    }
}

// Fetch student's attempts for course assessments
$stmt = $pdo->prepare("SELECT passage_id, phase, accuracy_score, comprehension_score, oral_reading_profile, created_at FROM reading_attempts WHERE user_id = ? AND phase IN ('Course-Pre-Test', 'Course-Post-Test')");
$stmt->execute([$user_id]);
$attemptsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

$attempts = [];
foreach($attemptsRaw as $att) {
    // Key by passage_id (which corresponds to course_assessments.id)
    $attempts[$att['passage_id']] = $att;
}


$mname = $user['mname'] ?? '';
$mi = !empty($mname) ? strtoupper(substr($mname, 0, 1)) . '.' : '';
$fullName = htmlspecialchars($user['fname'] . ' ' . $mi . ' ' . $user['lname']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="https://tibungcodistrict.wordpress.com/wp-content/uploads/2018/06/untitled-1.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Course - Phil-IRI</title>
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
                <h2 class="font-bold text-2xl text-slate-800">My Course</h2>
                <p class="text-sm text-slate-500">Reading Intervention Materials</p>
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

        <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-slate-50">

            <div class="max-w-4xl mx-auto space-y-6">
                
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-8 text-center">
                    <h2 class="text-2xl font-bold text-slate-800 mb-2">Welcome to your Course!</h2>
                    <p class="text-slate-500">Complete your Pre-Test, study the modules, and then take your Post-Test to see how much you've improved.</p>
                </div>

                <?php foreach($courses as $course): 
                    $cid = $course['id'];
                    $is_taken = isset($attempts[$cid]);
                    $att = $is_taken ? $attempts[$cid] : null;
                ?>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col md:flex-row">
                    <div class="w-2 bg-blue-500 shrink-0"></div>
                    <div class="p-6 flex-1 flex flex-col md:flex-row items-start md:items-center justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="text-xl font-bold text-slate-800"><?php echo htmlspecialchars($course['title']); ?></h3>
                                <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-xs font-bold uppercase"><?php echo $course['test_type']; ?></span>
                            </div>
                            <p class="text-slate-500 text-sm">Target: <?php echo $course['target_profile']; ?> Level</p>
                        </div>
                        <div class="mt-4 md:mt-0 flex flex-col items-end">
                            <?php if ($is_taken): ?>
                                <div class="text-right">
                                    <div class="text-emerald-600 font-bold mb-1"><i class="fas fa-check-circle mr-1"></i> Completed</div>
                                    <div class="text-sm text-slate-500 bg-slate-50 px-3 py-2 rounded border">
                                        <span class="font-bold">Acc:</span> <?php echo number_format($att['accuracy_score'], 1); ?>% | 
                                        <span class="font-bold">Comp:</span> <?php echo number_format($att['comprehension_score'], 1); ?>%
                                        <br>
                                        <span class="font-bold text-blue-600"><?php echo $att['oral_reading_profile']; ?></span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <a href="take_course_test.php?id=<?php echo $cid; ?>" hx-boost="false" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow">
                                    Take Test <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
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
