<?php
session_start();
require_once 'config.php';

// RBAC Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

$student_id = $_GET['user_id'] ?? 0;

// Fetch student details
$stmt = $pdo->prepare("SELECT fname, lname, grade_level, section FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Student not found.");
}

// Fetch all pre-assessment attempts for this student
$stmt = $pdo->prepare("
    SELECT * 
    FROM reading_attempts
    WHERE user_id = ? AND phase = 'Pre-Test'
    ORDER BY created_at DESC
");
$stmt->execute([$student_id]);
$attempts = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Submissions - Teacher Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="flex flex-col h-screen">
    
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0">
        <div class="flex items-center">
            <a href="assessment_result.php" class="text-slate-400 hover:text-slate-600 mr-4"><i class="fas fa-arrow-left"></i></a>
            <i class="fas fa-folder-open text-blue-500 text-2xl mr-3"></i>
            <h1 class="font-bold text-lg text-slate-800">Passage Attempts: <?php echo htmlspecialchars($student['fname'] . ' ' . $student['lname']); ?></h1>
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
            <h2 class="text-xl font-bold text-slate-800 mb-2">Individual Passages</h2>
            <p class="text-slate-500 mb-6">Review the specific audio recordings and transcripts that generated the student's profile.</p>
            
            <?php if (count($attempts) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="text-xs text-slate-500 bg-slate-50 border-y border-slate-200">
                            <tr>
                                <th class="py-3 px-4 font-medium">Passage</th>
                                <th class="py-3 px-4 font-medium">Date Submitted</th>
                                <th class="py-3 px-4 font-medium">Accuracy</th>
                                <th class="py-3 px-4 font-medium">Comprehension</th>
                                <th class="py-3 px-4 font-medium">System Classification</th>
                                <th class="py-3 px-4 font-medium text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach($attempts as $att): 
                                $date = date('M d, Y g:i A', strtotime($att['created_at']));
                                $profile = $att['oral_reading_profile'];
                                $profileClass = $profile == 'Independent' ? 'bg-emerald-100 text-emerald-800' : ($profile == 'Instructional' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800');
                            ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4 font-bold text-slate-800">Grade <?php echo htmlspecialchars($att['passage_grade']); ?></td>
                                <td class="py-3 px-4 text-slate-500"><?php echo $date; ?></td>
                                <td class="py-3 px-4 font-medium"><?php echo number_format($att['accuracy_score'], 1); ?>%</td>
                                <td class="py-3 px-4 font-medium"><?php echo number_format($att['comprehension_score'], 1); ?>%</td>
                                <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo $profileClass; ?>"><?php echo $profile; ?></span></td>
                                <td class="py-3 px-4 text-right">
                                    <a href="review_detail.php?id=<?php echo $att['id']; ?>&sid=<?php echo $student_id; ?>" class="inline-flex items-center px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded text-xs font-medium transition">
                                        <i class="fas fa-headphones mr-1.5"></i> Review Audio
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <p class="text-slate-500">No individual passages found for this student.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
