<?php
session_start();
require_once 'config.php';

// RBAC Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

// Fetch real data
$stmt = $pdo->prepare("
    SELECT u.*, 
           p.independent_grade, p.instructional_grade, p.frustration_grade,
           g.needs_individual_assessment
    FROM users u
    LEFT JOIN reading_profiles p ON u.id = p.user_id
    LEFT JOIN (
        SELECT user_id, needs_individual_assessment
        FROM gst_results
        WHERE id IN (SELECT MAX(id) FROM gst_results GROUP BY user_id)
    ) g ON u.id = g.user_id
    WHERE u.role = 'student' 
    ORDER BY u.fname ASC
");
$stmt->execute();
$students = $stmt->fetchAll();

$total_students = count($students);
$independent = 0; $instructional = 0; $frustration = 0; $non_reader = 0; $pending = 0;

foreach($students as &$s) {
    $enrolled_grade = intval(preg_replace('/[^0-9]/', '', $s['grade_level']));
    $final_status = 'Pending';
    
    if (isset($s['needs_individual_assessment']) && $s['needs_individual_assessment'] == 0) {
        $final_status = 'Independent';
    } elseif ($s['independent_grade'] !== null || $s['instructional_grade'] !== null || $s['frustration_grade'] !== null) {
        if ($s['independent_grade'] !== null && $enrolled_grade <= intval($s['independent_grade'])) {
            $final_status = 'Independent';
        } elseif ($s['instructional_grade'] !== null && $enrolled_grade <= intval($s['instructional_grade'])) {
            $final_status = 'Instructional';
        } else {
            if ($s['frustration_grade'] !== null && intval($s['frustration_grade']) <= 4 && $s['instructional_grade'] === null && $s['independent_grade'] === null) {
                $final_status = 'Non-Reader';
            } else {
                $final_status = 'Frustration';
            }
        }
    }
    
    $s['oral_reading_profile'] = $final_status;
    
    if($final_status === 'Independent') $independent++;
    elseif($final_status === 'Instructional') $instructional++;
    elseif($final_status === 'Frustration') $frustration++;
    elseif($final_status === 'Non-Reader') $non_reader++;
    else $pending++;
}
unset($s);

$pct_ind = $total_students > 0 ? round(($independent/$total_students)*100, 1) : 0;
$pct_ins = $total_students > 0 ? round(($instructional/$total_students)*100, 1) : 0;
$pct_fru = $total_students > 0 ? round(($frustration/$total_students)*100, 1) : 0;
$pct_non = $total_students > 0 ? round(($non_reader/$total_students)*100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="https://tibungcodistrict.wordpress.com/wp-content/uploads/2018/06/untitled-1.png">
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
    <?php include 'teacher_sidebar.php'; ?>

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
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="card p-4 flex flex-col md:flex-row md:items-center justify-between border-l-4 border-l-blue-500">
                    <div>
                        <p class="text-sm font-semibold text-blue-600 mb-1">Total</p>
                        <h3 class="text-3xl font-bold"><?php echo $total_students; ?></h3>
                    </div>
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
                <div class="card p-4 flex flex-col md:flex-row md:items-center justify-between border-l-4 border-l-slate-500 bg-slate-50/30">
                    <div>
                        <p class="text-sm font-semibold text-slate-600 mb-1">Non-Reader</p>
                        <h3 class="text-3xl font-bold"><?php echo $non_reader; ?></h3>
                        <p class="text-xs text-slate-600 font-medium"><?php echo $pct_non; ?>%</p>
                    </div>
                </div>
            </div>

            <!-- Masterlist -->
            <div class="card p-6 h-auto flex flex-col mb-10">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center border-b pb-2 mb-4 gap-2">
                    <h3 class="font-bold text-slate-800 text-sm">Master Student List</h3>
                    <div class="flex items-center gap-2">
                        <select id="masterlistProfileFilter" class="border border-slate-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-blue-500 bg-white">
                            <option value="">All Profiles</option>
                            <option value="Independent">Independent</option>
                            <option value="Instructional">Instructional</option>
                            <option value="Frustration">Frustration</option>
                            <option value="Non-Reader">Non-Reader</option>
                            <option value="Pending">Pending</option>
                        </select>
                        <input type="text" id="masterlistSearch" placeholder="Search students..." class="border border-slate-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-blue-500">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table id="masterlistTable" class="w-full text-sm text-left border-collapse">
                        <thead class="text-xs text-slate-500 bg-slate-50">
                            <tr>
                                <th class="py-2 px-3">Student Name</th>
                                <th class="py-2 px-3">LRN</th>
                                <th class="py-2 px-3">Grade & Sec</th>
                                <th class="py-2 px-3">Oral Reading Profile</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php 
                            if(count($students) > 0) {
                                foreach($students as $s):
                                    $lvl = $s['oral_reading_profile'] ?? 'Pending';
                                    $mi = !empty($s['mname']) ? strtoupper(substr($s['mname'], 0, 1)) . '.' : '';
                                    $name = htmlspecialchars($s['fname'] . ' ' . $mi . ' ' . $s['lname']);
                                    $lc = 'bg-slate-100 text-slate-600';
                                    if($lvl=='Independent') $lc='bg-emerald-100 text-emerald-800';
                                    if($lvl=='Instructional') $lc='bg-amber-100 text-amber-800';
                                    if($lvl=='Frustration') $lc='bg-rose-100 text-rose-800';
                                    if($lvl=='Non-Reader') $lc='bg-slate-700 text-white';
                            ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-2 px-3 font-medium text-slate-800"><?php echo $name; ?></td>
                                <td class="py-2 px-3 text-slate-500"><?php echo htmlspecialchars($s['lrn']); ?></td>
                                <td class="py-2 px-3 text-slate-500"><?php echo htmlspecialchars($s['grade_level'].' - '.$s['section']); ?></td>
                                <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo $lc; ?>"><?php echo $lvl; ?></span></td>
                            </tr>
                            <?php endforeach; } else { echo "<tr><td colspan='4' class='text-center py-4'>No students found.</td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Custom Table Filter -->
    <script src="v536/public/table_filter.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            initTailwindTable('masterlistTable', 'masterlistSearch', 'masterlistProfileFilter', 3);
        });
    </script>

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
