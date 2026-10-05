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
    </style>
</head>
<body class="flex h-screen overflow-hidden text-slate-800">

    <!-- Sidebar -->
    <aside class="sidebar w-64 text-white flex flex-col shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-white/10">
            <img src="https://tibungcodistrict.wordpress.com/wp-content/uploads/2018/06/untitled-1.png?w=490" class="h-8 object-contain mr-3 bg-white rounded p-1">
            <div>
                <h1 class="font-bold text-lg leading-tight">Phil-IRI</h1>
                <p class="text-[10px] text-blue-200 uppercase tracking-wider">Teacher Portal</p>
            </div>
        </div>
        <nav class="flex-1 py-4 overflow-y-auto">
            <ul class="space-y-1">
                <li><a href="dashboard_teacher.php" class="flex items-center px-6 py-3 bg-blue-600 text-white font-medium border-l-4 border-white"><i class="fas fa-border-all w-6"></i> Dashboard</a></li>
                <li><a href="manage_preassessment.php" class="flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 transition"><i class="fas fa-book w-6"></i> Graded Reading Passages</a>
                <a href="manage_gst.php" class="flex items-center text-slate-300 hover:text-white hover:bg-slate-800 rounded px-3 py-2 transition"><i class="fas fa-file-alt w-6"></i> Manage GST</a>
                <li><a href="assessment_result.php" class="flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 transition"><i class="fas fa-microphone-alt w-6"></i> Assessment Results</a></li>
                <li><a href="course.php" class="flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 transition"><i class="fas fa-book-reader w-6"></i> Courses (Pre/Post)</a></li>
                <li class="mt-8"><a href="teacher_profile.php" class="flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 transition"><i class="fas fa-user-circle w-6"></i> Profile</a></li>
                <li><a href="teacher_settings.php" class="flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 transition"><i class="fas fa-cog w-6"></i> Settings</a></li>
                <li><a href="logout.php" class="flex items-center px-6 py-3 text-red-300 hover:bg-blue-800 transition"><i class="fas fa-sign-out-alt w-6"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0">
            <div>
                <h2 class="font-bold text-lg text-slate-800">Welcome, <?php echo htmlspecialchars($_SESSION['fname']); ?>!</h2>
            </div>
            <div class="flex items-center space-x-3 border-l pl-4 border-slate-200">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                    <?php echo substr($_SESSION['fname'], 0, 1); ?>
                </div>
                <div class="leading-tight">
                    <p class="font-semibold text-sm"><?php echo htmlspecialchars($_SESSION['fname']); ?></p>
                    <p class="text-xs text-slate-500 capitalize">Teacher</p>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 bg-slate-50">
            <h2 class="text-2xl font-bold text-slate-800 mb-6">Dashboard Overview</h2>

            <!-- Metrics -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="card p-4 flex items-center justify-between border-l-4 border-l-blue-500">
                    <div>
                        <p class="text-sm font-semibold text-blue-600 mb-1">Total Students</p>
                        <h3 class="text-3xl font-bold"><?php echo $total_students; ?></h3>
                    </div>
                    <div class="text-3xl text-blue-100"><i class="fas fa-users"></i></div>
                </div>
                <div class="card p-4 flex items-center justify-between border-l-4 border-l-emerald-500 bg-emerald-50/30">
                    <div>
                        <p class="text-sm font-semibold text-emerald-600 mb-1">Independent</p>
                        <h3 class="text-3xl font-bold"><?php echo $independent; ?></h3>
                        <p class="text-xs text-emerald-600 font-medium"><?php echo $pct_ind; ?>%</p>
                    </div>
                </div>
                <div class="card p-4 flex items-center justify-between border-l-4 border-l-amber-500 bg-amber-50/30">
                    <div>
                        <p class="text-sm font-semibold text-amber-600 mb-1">Instructional</p>
                        <h3 class="text-3xl font-bold"><?php echo $instructional; ?></h3>
                        <p class="text-xs text-amber-600 font-medium"><?php echo $pct_ins; ?>%</p>
                    </div>
                </div>
                <div class="card p-4 flex items-center justify-between border-l-4 border-l-rose-500 bg-rose-50/30">
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
</body>
</html>
