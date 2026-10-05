<?php
session_start();
require_once 'config.php';

// RBAC Check for Teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

// Fetch all students who have a finalized reading profile
$stmt = $pdo->query("
    SELECT rp.*, u.id as student_id, u.fname, u.lname, u.grade_level, u.section 
    FROM reading_profiles rp
    JOIN users u ON rp.user_id = u.id
    ORDER BY rp.updated_at DESC
");
$profiles = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Results - Teacher Dashboard</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="flex flex-col h-screen">
    
    <!-- Top Navbar -->
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0">
        <div class="flex items-center">
            <a href="dashboard_teacher.php" class="text-slate-400 hover:text-slate-600 mr-4"><i class="fas fa-arrow-left"></i></a>
            <i class="fas fa-users text-rose-500 text-2xl mr-3"></i>
            <h1 class="font-bold text-lg text-slate-800">Student Profiles</h1>
        </div>
        <div class="flex items-center space-x-4">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                <?php echo substr($_SESSION['fname'], 0, 1); ?>
            </div>
            <div class="leading-tight">
                <p class="font-semibold text-sm"><?php echo htmlspecialchars($_SESSION['fname']); ?></p>
                <p class="text-xs text-slate-500 capitalize"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
            </div>
        </div>
    </header>

    <main class="flex-1 p-8 max-w-6xl mx-auto w-full overflow-y-auto">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-xl font-bold text-slate-800 mb-6">Completed Pre-Assessments</h2>
            
            <?php if (count($profiles) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="text-xs text-slate-500 bg-slate-50 border-y border-slate-200">
                            <tr>
                                <th class="py-3 px-4 font-medium">Student</th>
                                <th class="py-3 px-4 font-medium">Grade & Section</th>
                                <th class="py-3 px-4 font-medium">Date Completed</th>
                                <th class="py-3 px-4 font-medium text-emerald-600">Independent</th>
                                <th class="py-3 px-4 font-medium text-amber-600">Instructional</th>
                                <th class="py-3 px-4 font-medium text-rose-600">Frustration</th>
                                <th class="py-3 px-4 font-medium text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach($profiles as $prof): 
                                $date = date('M d, Y g:i A', strtotime($prof['updated_at']));
                            ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4 font-medium text-slate-800"><?php echo htmlspecialchars($prof['fname'] . ' ' . $prof['lname']); ?></td>
                                <td class="py-3 px-4 text-slate-500"><?php echo htmlspecialchars($prof['grade_level'] . ' - ' . $prof['section']); ?></td>
                                <td class="py-3 px-4 text-slate-500"><?php echo $date; ?></td>
                                <td class="py-3 px-4 font-medium"><?php echo $prof['independent_grade'] ? 'Grade ' . $prof['independent_grade'] : '—'; ?></td>
                                <td class="py-3 px-4 font-medium"><?php echo $prof['instructional_grade'] ? 'Grade ' . $prof['instructional_grade'] : '—'; ?></td>
                                <td class="py-3 px-4 font-medium"><?php echo $prof['frustration_grade'] ? 'Grade ' . $prof['frustration_grade'] : '—'; ?></td>
                                <td class="py-3 px-4 text-right">
                                    <a href="student_submissions.php?user_id=<?php echo $prof['student_id']; ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded text-xs font-medium transition">
                                        <i class="fas fa-folder-open mr-1.5"></i> View Submissions
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
                        <i class="fas fa-inbox text-2xl text-slate-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-slate-800 mb-1">No assessments yet</h3>
                    <p class="text-slate-500">When students complete their pre-assessment, their final profiles will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
