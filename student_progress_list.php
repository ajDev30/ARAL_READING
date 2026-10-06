<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

// Fetch all students who have at least one reading attempt
$stmt = $pdo->query("
    SELECT u.id, u.fname, u.lname, u.grade_level, u.section,
           COUNT(ra.id) as total_tests,
           MAX(ra.created_at) as last_activity
    FROM users u
    JOIN reading_attempts ra ON u.id = ra.user_id
    WHERE u.role = 'student'
    GROUP BY u.id
    ORDER BY last_activity DESC
");
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Progress Tracker</title>
    <script>
        const originalWarn = console.warn;
        console.warn = function() {
            if (arguments[0] && typeof arguments[0] === 'string' && arguments[0].includes('cdn.tailwindcss.com should not be used in production')) return;
            originalWarn.apply(console, arguments);
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .keep-colors { color-adjust: exact; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex overflow-hidden">
    
    <!-- Sidebar -->
    <?php include 'teacher_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative transition-all duration-300" id="mainContent">
        
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 shrink-0">
            <div class="flex items-center">
                <button onclick="window.toggleSidebar()" class="mr-4 text-slate-500 hover:text-slate-700 focus:outline-none md:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h1 class="font-bold text-lg text-slate-800">Student Progress Tracker</h1>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-8">
            <div class="max-w-6xl mx-auto">
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Pre-Test & Post-Test Results</h2>
                        <p class="text-sm text-slate-500">Track student improvement across all courses and assessments.</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <?php if(count($students) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 font-bold">
                                        <th class="py-3 px-4">Student Name</th>
                                        <th class="py-3 px-4">Grade & Section</th>
                                        <th class="py-3 px-4">Total Tests Taken</th>
                                        <th class="py-3 px-4">Last Activity</th>
                                        <th class="py-3 px-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php foreach($students as $s): ?>
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3 px-4 font-medium text-slate-800">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold mr-3">
                                                    <?php echo strtoupper(substr($s['fname'], 0, 1)); ?>
                                                </div>
                                                <?php echo htmlspecialchars($s['fname'] . ' ' . $s['lname']); ?>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($s['grade_level'] . ' - ' . $s['section']); ?></td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs font-bold"><?php echo $s['total_tests']; ?> Tests</span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-500 text-sm"><?php echo date('M d, Y h:i A', strtotime($s['last_activity'])); ?></td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="student_submissions.php?user_id=<?php echo $s['id']; ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white hover:bg-blue-700 rounded text-xs font-medium transition shadow-sm">
                                                <i class="fas fa-chart-line mr-1.5"></i> View Progress
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                                <i class="fas fa-users text-2xl text-slate-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-slate-800 mb-1">No Active Students</h3>
                            <p class="text-slate-500">When students take a Pre-Test or Post-Test, they will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
