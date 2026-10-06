<?php
session_start();
require_once 'config.php';

// RBAC Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

// Fetch real data
$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'student' ORDER BY fname ASC");
$stmt->execute();
$students = $stmt->fetchAll();

$total_students = count($students);
$independent = 0; $instructional = 0; $frustration = 0; $pending = 0;

foreach($students as $s) {
    if($s['current_level'] == 'Independent') $independent++;
    elseif($s['current_level'] == 'Instructional') $instructional++;
    elseif($s['current_level'] == 'Frustration') $frustration++;
    else $pending++;
}

$pct_ind = $total_students > 0 ? round(($independent/$total_students)*100, 1) : 0;
$pct_ins = $total_students > 0 ? round(($instructional/$total_students)*100, 1) : 0;
$pct_fru = $total_students > 0 ? round(($frustration/$total_students)*100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Phil-IRI</title>
    <script>
        const originalWarn = console.warn;
        console.warn = function() {
            if (arguments[0] && typeof arguments[0] === 'string' && arguments[0].includes('cdn.tailwindcss.com should not be used in production')) return;
            originalWarn.apply(console, arguments);
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
        .sidebar { background-color: #1a365d; }
        .card { background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #E2E8F0; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
            <?php $is_dark = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark'; ?>
        <?php if($is_dark): ?>
        /* Refined Slate Dark Mode */
        body { background-color: #0f172a !important; color: #f8fafc !important; }
        .bg-white, .bg-slate-50 { background-color: #1e293b !important; border-color: #334155 !important; color: #f8fafc !important; }
        
        .text-slate-800, .text-slate-700 { color: #f1f5f9 !important; }
        .text-slate-600, .text-slate-500, .text-slate-400 { color: #cbd5e1 !important; }
        .border-slate-200, .border-slate-100, .border-b, .border-l { border-color: #334155 !important; }
        .border-slate-300 { border-color: #475569 !important; }
        .sidebar { background-color: #0b1120 !important; border-right: 1px solid #1e293b !important; }
        input, select, textarea { background-color: #0f172a !important; color: white !important; border-color: #475569 !important; }
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.5) !important; }

        /* Colored Badges / Cards Fixes */
        .bg-blue-50, .bg-blue-100 { background-color: rgba(59, 130, 246, 0.2) !important; color: #93c5fd !important; }
        .text-blue-600, .text-blue-700, .text-blue-800 { color: #60a5fa !important; }
        .border-blue-100, .border-blue-200, .border-l-blue-500 { border-color: rgba(59, 130, 246, 0.3) !important; }

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

        /* Bug Fixes for hover states and cards */
        .bg-slate-100, .bg-slate-200 { background-color: #334155 !important; color: #e2e8f0 !important; }
        .hover\:bg-slate-50:hover, tr:hover { background-color: #334155 !important; }
        .card { background-color: #1e293b !important; border-color: #334155 !important; }
        
        /* Logo Fix */
        .sidebar img { background-color: transparent !important; filter: drop-shadow(0px 0px 2px rgba(255,255,255,0.5)) !important; }
        <?php endif; ?>
    </style>


    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <meta name="htmx-config" content='{"globalViewTransitions":true}'>
</head>
<body class="flex h-screen overflow-hidden text-slate-800">

    <!-- Sidebar -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden md:hidden" onclick="toggleSidebar()"></div>
    <aside id="appSidebar" class="sidebar w-64 fixed inset-y-0 left-0 z-50 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out text-white flex flex-col shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-white/10">
            <img src="https://tibungcodistrict.wordpress.com/wp-content/uploads/2018/06/untitled-1.png?w=490" class="h-8 object-contain mr-3 bg-white rounded p-1">
            <div>
                <h1 class="font-bold text-lg leading-tight">Phil-IRI</h1>
                <p class="text-[10px] text-blue-200 uppercase tracking-wider">Teacher Portal</p>
            </div>
        </div>
        <nav class="flex-1 py-4 overflow-y-auto" hx-boost="true">
            <ul class="space-y-1">
                <li><a href="dashboard_teacher.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_teacher.php' ? 'flex items-center px-6 py-3 bg-blue-600 text-white font-medium border-l-4 border-white' : 'flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 transition'; ?>"><i class="fas fa-border-all w-6"></i> Dashboard</a></li>
                
                <li><a href="manage_preassessment.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_preassessment.php' ? 'flex items-center px-6 py-3 bg-blue-600 text-white font-medium border-l-4 border-white' : 'flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 transition'; ?>"><i class="fas fa-book w-6"></i> Graded Reading Passages</a></li>
                
                <li><a href="manage_gst.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_gst.php' ? 'flex items-center px-6 py-3 bg-blue-600 text-white font-medium border-l-4 border-white' : 'flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 transition'; ?>"><i class="fas fa-file-alt w-6"></i> Manage GST</a></li>
                
                <li><a href="assessment_result.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'assessment_result.php' ? 'flex items-center px-6 py-3 bg-blue-600 text-white font-medium border-l-4 border-white' : 'flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 transition'; ?>"><i class="fas fa-microphone-alt w-6"></i> Assessment Results</a></li>
                
                <li><a href="course.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'course.php' ? 'flex items-center px-6 py-3 bg-blue-600 text-white font-medium border-l-4 border-white' : 'flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 transition'; ?>"><i class="fas fa-book-reader w-6"></i> Courses (Pre/Post)</a></li>
                
                <li class="mt-8"><a href="teacher_profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'teacher_profile.php' ? 'flex items-center px-6 py-3 bg-blue-600 text-white font-medium border-l-4 border-white' : 'flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 transition'; ?>"><i class="fas fa-user-circle w-6"></i> Profile</a></li>
                
                <li><a href="teacher_settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'teacher_settings.php' ? 'flex items-center px-6 py-3 bg-blue-600 text-white font-medium border-l-4 border-white' : 'flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 transition'; ?>"><i class="fas fa-cog w-6"></i> Settings</a></li>
                
                <li><a href="logout.php" hx-boost="false" class="flex items-center px-6 py-3 text-red-300 hover:bg-blue-800 transition"><i class="fas fa-sign-out-alt w-6"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 shrink-0">
            <div class="flex items-center">
                <button onclick="toggleSidebar()" class="md:hidden mr-4 text-slate-500 hover:text-blue-600 focus:outline-none"><i class="fas fa-bars text-xl"></i></button>
                <div>
                <h2 class="font-bold text-lg text-slate-800">Welcome, <?php echo htmlspecialchars($_SESSION['fname']); ?>!</h2>
            </div></div>
            <div class="flex items-center space-x-3 border-l pl-4 border-slate-200">
                <?php 
                $avatarPath = 'uploads/profiles/user_' . $_SESSION['user_id'] . '.jpg';
                if (file_exists($avatarPath)): 
                ?>
                    <img src="<?php echo $avatarPath; ?>?t=<?php echo time(); ?>" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                        <?php echo substr($_SESSION['fname'], 0, 1); ?>
                    </div>
                <?php endif; ?>
                <div class="leading-tight">
                    <p class="font-semibold text-sm"><?php echo htmlspecialchars($_SESSION['fname']); ?></p>
                    <p class="text-xs text-slate-500 capitalize">Teacher</p>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 bg-slate-50">
            <h2 class="text-2xl font-bold text-slate-800 mb-6">Dashboard Overview</h2>

            <!-- Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="card p-4 flex flex-col md:flex-row md:items-center justify-between border-l-4 border-l-blue-500">
                    <div>
                        <p class="text-sm font-semibold text-blue-600 mb-1">Total Students</p>
                        <h3 class="text-3xl font-bold"><?php echo $total_students; ?></h3>
                    </div>
                    <div class="text-3xl text-blue-100"><i class="fas fa-users"></i></div>
                </div>
                <div class="card p-4 flex flex-col md:flex-row md:items-center justify-between border-l-4 border-l-emerald-500 bg-emerald-50/30">
                    <div>
                        <p class="text-sm font-semibold text-emerald-600 mb-1">Independent</p>
                        <h3 class="text-3xl font-bold"><?php echo $independent; ?></h3>
                        <p class="text-xs text-emerald-600 font-medium"><?php echo $pct_ind; ?>%</p>
                    </div>
                </div>
                <div class="card p-4 flex flex-col md:flex-row md:items-center justify-between border-l-4 border-l-amber-500 bg-amber-50/30">
                    <div>
                        <p class="text-sm font-semibold text-amber-600 mb-1">Instructional</p>
                        <h3 class="text-3xl font-bold"><?php echo $instructional; ?></h3>
                        <p class="text-xs text-amber-600 font-medium"><?php echo $pct_ins; ?>%</p>
                    </div>
                </div>
                <div class="card p-4 flex flex-col md:flex-row md:items-center justify-between border-l-4 border-l-rose-500 bg-rose-50/30">
                    <div>
                        <p class="text-sm font-semibold text-rose-600 mb-1">Frustration</p>
                        <h3 class="text-3xl font-bold"><?php echo $frustration; ?></h3>
                        <p class="text-xs text-rose-600 font-medium"><?php echo $pct_fru; ?>%</p>
                    </div>
                </div>
            </div>

            <!-- Masterlist -->
            <div class="card p-6 h-96 flex flex-col">
                <h3 class="font-bold text-slate-800 mb-4 text-sm border-b pb-2">Master Student List</h3>
                <div class="overflow-y-auto flex-1">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="text-xs text-slate-500 bg-slate-50 sticky top-0">
                            <tr>
                                <th class="py-2 px-3">Student Name</th>
                                <th class="py-2 px-3">LRN</th>
                                <th class="py-2 px-3">Grade & Sec</th>
                                <th class="py-2 px-3">Level</th>
                                <th class="py-2 px-3">Phase</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php 
                            if(count($students) > 0) {
                                foreach($students as $s):
                                    $lvl = $s['current_level'] ?? 'Pending';
                                    $stat = $s['test_status'] ?? 'Pending';
                                    $mi = !empty($s['mname']) ? strtoupper(substr($s['mname'], 0, 1)) . '.' : '';
                                    $name = htmlspecialchars($s['fname'] . ' ' . $mi . ' ' . $s['lname']);
                                    $lc = 'bg-slate-100 text-slate-600';
                                    if($lvl=='Independent') $lc='bg-emerald-100 text-emerald-800';
                                    if($lvl=='Instructional') $lc='bg-amber-100 text-amber-800';
                                    if($lvl=='Frustration') $lc='bg-rose-100 text-rose-800';
                                    $sc = $stat=='Pending' ? 'bg-slate-100 text-slate-600' : 'bg-blue-100 text-blue-800';
                            ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-2 px-3 font-medium text-slate-800"><?php echo $name; ?></td>
                                <td class="py-2 px-3 text-slate-500"><?php echo htmlspecialchars($s['lrn']); ?></td>
                                <td class="py-2 px-3 text-slate-500"><?php echo htmlspecialchars($s['grade_level'].' - '.$s['section']); ?></td>
                                <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo $lc; ?>"><?php echo $lvl; ?></span></td>
                                <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo $sc; ?>"><?php echo $stat; ?></span></td>
                            </tr>
                            <?php endforeach; } else { echo "<tr><td colspan='5' class='text-center py-4'>No students found.</td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>
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
