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

$sections = [];
$grade_levels = [];
foreach($students as $st) {
    $sec = preg_replace('/^Grade\s+\d+\s+/i', '', trim($st['section'] ?? ''));
    if (!empty($sec) && !in_array($sec, $sections)) {
        $sections[] = $sec;
    }
    $gl = trim($st['grade_level'] ?? '');
    if (!empty($gl) && !in_array($gl, $grade_levels)) {
        $grade_levels[] = $gl;
    }
}
sort($sections);
sort($grade_levels);
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


    <script src="js/htmx.min.js"></script>
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
                <div class="flex flex-col md:flex-row md:justify-between md:items-center border-b pb-3 mb-4 gap-3">
                    <div class="flex items-center gap-3">
                        <h3 class="font-bold text-slate-800 text-base">Master Student List</h3>
                        <button onclick="openReportModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            <i class="fas fa-file-alt"></i> Generate Report (.txt)
                        </button>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Grade Level Filter -->
                        <select id="masterlistGradeFilter" onchange="applyMasterlistFilters()" class="border border-slate-300 rounded px-2.5 py-1 text-xs focus:outline-none focus:border-blue-500 bg-white font-medium">
                            <option value="">All Grades</option>
                            <?php foreach($grade_levels as $gl): ?>
                                <option value="<?php echo htmlspecialchars($gl); ?>"><?php echo htmlspecialchars($gl); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Section Filter -->
                        <select id="masterlistSectionFilter" onchange="applyMasterlistFilters()" class="border border-slate-300 rounded px-2.5 py-1 text-xs focus:outline-none focus:border-blue-500 bg-white font-medium">
                            <option value="">All Sections</option>
                            <?php foreach($sections as $sec): ?>
                                <option value="<?php echo htmlspecialchars($sec); ?>"><?php echo htmlspecialchars($sec); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Profile Filter -->
                        <select id="masterlistProfileFilter" onchange="applyMasterlistFilters()" class="border border-slate-300 rounded px-2.5 py-1 text-xs focus:outline-none focus:border-blue-500 bg-white font-medium">
                            <option value="">All Profiles</option>
                            <option value="Independent">Independent</option>
                            <option value="Instructional">Instructional</option>
                            <option value="Frustration">Frustration</option>
                            <option value="Non-Reader">Non-Reader</option>
                            <option value="Pending">Pending</option>
                        </select>

                        <!-- Search -->
                        <input type="text" id="masterlistSearch" onkeyup="applyMasterlistFilters()" placeholder="Search students..." class="border border-slate-300 rounded px-3 py-1 text-xs focus:outline-none focus:border-blue-500 w-44">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table id="masterlistTable" class="w-full text-sm text-left border-collapse">
                        <thead class="text-xs text-slate-500 bg-slate-50">
                            <tr>
                                <th class="py-2.5 px-3">Student Name</th>
                                <th class="py-2.5 px-3">LRN</th>
                                <th class="py-2.5 px-3">Grade & Sec</th>
                                <th class="py-2.5 px-3">Oral Reading Profile</th>
                                <th class="py-2.5 px-3">Status Remarks</th>
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

                                    $clean_sec = preg_replace('/^Grade\s+\d+\s+/i', '', $s['section']);

                                    $remarks = 'Awaiting Assessment / GST';
                                    if ($lvl === 'Independent') $remarks = 'Mastery / Grade-Ready';
                                    elseif ($lvl === 'Instructional') $remarks = 'Recommended for Guided Reading Practice';
                                    elseif ($lvl === 'Frustration') $remarks = 'Requires Targeted Reading Support';
                                    elseif ($lvl === 'Non-Reader') $remarks = 'Urgent: Individual Reading Intervention';
                            ?>
                            <tr class="hover:bg-slate-50 transition"
                                data-grade="<?php echo htmlspecialchars($s['grade_level']); ?>" 
                                data-section="<?php echo htmlspecialchars($clean_sec); ?>" 
                                data-profile="<?php echo htmlspecialchars($lvl); ?>">
                                <td class="py-2.5 px-3 font-medium text-slate-800"><?php echo $name; ?></td>
                                <td class="py-2.5 px-3 text-slate-500 font-mono text-xs"><?php echo htmlspecialchars($s['lrn']); ?></td>
                                <td class="py-2.5 px-3 text-slate-600"><?php echo htmlspecialchars($s['grade_level'] . ' - ' . $clean_sec); ?></td>
                                <td class="py-2.5 px-3"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo $lc; ?>"><?php echo $lvl; ?></span></td>
                                <td class="py-2.5 px-3 text-slate-600 text-xs font-medium"><?php echo htmlspecialchars($remarks); ?></td>
                            </tr>
                            <?php endforeach; } else { echo "<tr><td colspan='5' class='text-center py-4 text-slate-500'>No students found.</td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Generate Report Modal -->
    <div id="reportModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-lg text-slate-800 flex items-center">
                    <i class="fas fa-file-download text-blue-600 mr-2"></i> Generate Report (.txt)
                </h3>
                <button type="button" onclick="closeReportModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            <p class="text-xs text-slate-500 mb-4">Export a formatted text summary and student roster based on Phil-IRI assessment data.</p>
            
            <form method="GET" action="export_teacher_report.php" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Year / Grade Level</label>
                    <select name="grade" id="reportGrade" class="w-full border border-slate-300 rounded px-3 py-2 text-sm bg-white focus:outline-none focus:border-blue-500">
                        <option value="">All Year Levels</option>
                        <?php foreach($grade_levels as $gl): ?>
                            <option value="<?php echo htmlspecialchars($gl); ?>"><?php echo htmlspecialchars($gl); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Section</label>
                    <select name="section" id="reportSection" class="w-full border border-slate-300 rounded px-3 py-2 text-sm bg-white focus:outline-none focus:border-blue-500">
                        <option value="">All Sections</option>
                        <?php foreach($sections as $sec): ?>
                            <option value="<?php echo htmlspecialchars($sec); ?>"><?php echo htmlspecialchars($sec); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status / Reading Profile</label>
                    <select name="status" id="reportStatus" class="w-full border border-slate-300 rounded px-3 py-2 text-sm bg-white focus:outline-none focus:border-blue-500">
                        <option value="">All Statuses / Profiles</option>
                        <option value="Independent">Independent</option>
                        <option value="Instructional">Instructional</option>
                        <option value="Frustration">Frustration</option>
                        <option value="Non-Reader">Non-Reader</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
                
                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeReportModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition">Cancel</button>
                    <button type="submit" onclick="setTimeout(closeReportModal, 500)" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold transition shadow-sm flex items-center">
                        <i class="fas fa-download mr-1.5"></i> Download .txt Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom Table Filter & Modal Logic -->
    <script src="v536/public/table_filter.js"></script>
    <script>
        function openReportModal() {
            document.getElementById('reportModal').classList.remove('hidden');
        }
        function closeReportModal() {
            document.getElementById('reportModal').classList.add('hidden');
        }

        function applyMasterlistFilters() {
            const q = (document.getElementById('masterlistSearch')?.value || '').toLowerCase();
            const grade = document.getElementById('masterlistGradeFilter')?.value || '';
            const sec = document.getElementById('masterlistSectionFilter')?.value || '';
            const prof = document.getElementById('masterlistProfileFilter')?.value || '';
            
            const rows = document.querySelectorAll('#masterlistTable tbody tr');
            rows.forEach(row => {
                if (row.cells.length <= 1) return;
                const rowGrade = row.getAttribute('data-grade') || '';
                const rowSec = row.getAttribute('data-section') || '';
                const rowProf = row.getAttribute('data-profile') || '';
                const rowText = row.innerText.toLowerCase();
                
                const matchQ = !q || rowText.includes(q);
                const matchGrade = !grade || rowGrade.toLowerCase() === grade.toLowerCase();
                const matchSec = !sec || rowSec.toLowerCase() === sec.toLowerCase();
                const matchProf = !prof || rowProf.toLowerCase() === prof.toLowerCase();
                
                row.style.display = (matchQ && matchGrade && matchSec && matchProf) ? '' : 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Initialize header sorting for columns
            const headers = document.querySelectorAll('#masterlistTable thead th');
            headers.forEach((header, index) => {
                header.style.cursor = 'pointer';
                header.innerHTML += ' <i class="fas fa-sort text-slate-300 ml-1 text-[10px]"></i>';
                let asc = true;
                header.addEventListener('click', () => {
                    const tbody = document.querySelector('#masterlistTable tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    
                    headers.forEach(h => {
                        const icon = h.querySelector('i.fa-sort, i.fa-sort-up, i.fa-sort-down');
                        if(icon) { icon.className = 'fas fa-sort text-slate-300 ml-1 text-[10px]'; }
                    });
                    
                    const currentIcon = header.querySelector('i');
                    if (currentIcon) {
                        currentIcon.className = asc ? 'fas fa-sort-up text-blue-500 ml-1 text-[10px]' : 'fas fa-sort-down text-blue-500 ml-1 text-[10px]';
                    }

                    rows.sort((a, b) => {
                        if (a.cells.length <= 1 || b.cells.length <= 1) return 0;
                        const aCol = a.cells[index].textContent.trim();
                        const bCol = b.cells[index].textContent.trim();
                        const aNum = parseFloat(aCol.replace(/[^0-9.-]/g, ''));
                        const bNum = parseFloat(bCol.replace(/[^0-9.-]/g, ''));
                        if (!isNaN(aNum) && !isNaN(bNum)) {
                            return asc ? aNum - bNum : bNum - aNum;
                        }
                        return asc ? aCol.localeCompare(bCol) : bCol.localeCompare(aCol);
                    });

                    rows.forEach(row => tbody.appendChild(row));
                    asc = !asc;
                });
            });
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
