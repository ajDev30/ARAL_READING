<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

$student_id = $_GET['user_id'] ?? 0;

$stmt = $pdo->prepare("SELECT fname, lname, grade_level, section FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Student not found.");
}

// Fetch all course tests
$stmt = $pdo->prepare("
    SELECT a.*, c.title as course_title, c.questions_json 
    FROM reading_attempts a
    JOIN course_assessments c ON a.passage_id = c.id
    WHERE a.user_id = ? AND a.phase IN ('Course-Pre-Test', 'Course-Post-Test')
    ORDER BY a.created_at ASC
");
$stmt->execute([$student_id]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$modules = [];
foreach($attempts as $att) {
    if (preg_match('/GRADE (\d+)/i', $att['course_title'], $matches)) {
        $grade = $matches[1];
        if(!isset($modules[$grade])) {
            $modules[$grade] = ['pre' => null, 'post' => null];
        }
        
        $totalQuestions = 10;
        if (!empty($att['questions_json'])) {
            $q = json_decode($att['questions_json'], true);
            if ($q) {
                $totalQuestions = 0;
                foreach($q as $item) {
                    if ($item['type'] === 'multichoice' || !isset($item['type']) || isset($item['options'])) {
                        $totalQuestions += 1;
                    } else if ($item['type'] === 'truefalse') {
                        $totalQuestions += 1;
                    } else if ($item['type'] === 'enumeration') {
                        $expectedCount = (isset($item['answers']) && count($item['answers']) > 0) ? count($item['answers']) : (intval($item['count'] ?? 1));
                        $totalQuestions += $expectedCount;
                    } else if ($item['type'] === 'essay') {
                        $pts = intval($item['points'] ?? 1);
                        $totalQuestions += $pts;
                    }
                }
            }
        }
        $att['total_questions'] = $totalQuestions;
        $att['comp_raw'] = round(($att['comprehension_score'] / 100) * $totalQuestions);
        
        // Calculate WCPM
        $wcpm = 0;
        if (!empty($att['evaluation_data'])) {
            $eval = json_decode($att['evaluation_data'], true);
            if ($eval && isset($eval['assessment'])) {
                $words = count($eval['assessment']['referenceWords'] ?? []);
                $miscues = 0;
                $counts = $eval['assessment']['counts'] ?? [];
                $miscues += ($counts['mispronunciation'] ?? 0);
                $miscues += ($counts['omission'] ?? 0);
                $miscues += ($counts['substitution'] ?? 0);
                $miscues += ($counts['insertion'] ?? 0);
                
                $correct = max(0, $words - $miscues);
                $mins = $att['reading_time'] / 60;
                if ($mins > 0) {
                    $wcpm = round($correct / $mins);
                }
            }
        }
        $att['wcpm'] = $wcpm;

        if ($att['phase'] === 'Course-Pre-Test') {
            $modules[$grade]['pre'] = $att;
        } else {
            $modules[$grade]['post'] = $att;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Grades - <?php echo htmlspecialchars($student['fname']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #F8FAFC; }
        .sidebar { background-color: #1a365d; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-slate-800">
    <?php include 'teacher_sidebar.php'; ?>
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 shrink-0">
            <div class="flex items-center">
                <button onclick="toggleSidebar()" class="md:hidden mr-4 text-slate-500 hover:text-blue-600 focus:outline-none"><i class="fas fa-bars text-xl"></i></button>
                <a href="teacher_course_grades.php" class="text-slate-400 hover:text-slate-600 mr-4"><i class="fas fa-arrow-left"></i></a>
                <h1 class="font-bold text-lg text-slate-800">Course Grades: <?php echo htmlspecialchars($student['fname'] . ' ' . $student['lname']); ?></h1>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto max-w-6xl mx-auto w-full">
            <?php if (empty($modules)): ?>
                <div class="bg-white p-8 rounded-xl border border-slate-200 text-center">
                    <p class="text-slate-500">This student has not taken any Course Pre-Tests or Post-Tests yet.</p>
                </div>
            <?php else: ?>
                <div class="space-y-8">
                    <?php foreach($modules as $grade => $tests): 
                        $pre = $tests['pre'];
                        $post = $tests['post'];
                        
                        $prePct = $pre ? floatval($pre['comprehension_score']) : 0;
                        $postPct = $post ? floatval($post['comprehension_score']) : 0;
                        
                        $preRaw = $pre ? $pre['comp_raw'] : 0;
                        $postRaw = $post ? $post['comp_raw'] : 0;
                        
                        $diffRaw = ($pre && $post) ? ($postRaw - $preRaw) : 0;
                        
                        if (!$post) {
                            $resultText = 'In Progress';
                            $resultColor = 'text-blue-600';
                            $resultBg = 'bg-blue-50 border-blue-200';
                            $icon = 'fa-spinner';
                            $rec = "The student has completed the Pre-Test. Waiting for Post-Test.";
                        } else if (!$pre) {
                            $resultText = 'Post-Test Only';
                            $resultColor = 'text-slate-600';
                            $resultBg = 'bg-slate-50 border-slate-200';
                            $icon = 'fa-check';
                            $rec = "The student completed the Post-Test without a recorded Pre-Test.";
                        } else if ($postPct > $prePct) {
                            $resultText = 'Improved';
                            $resultColor = 'text-emerald-600';
                            $resultBg = 'bg-emerald-50 border-emerald-200';
                            $icon = 'fa-arrow-trend-up';
                            $rec = "The student demonstrated improvement! Great job.";
                        } else if ($postPct == $prePct && $postPct >= 90) {
                            $resultText = 'Consistent Mastery';
                            $resultColor = 'text-purple-600';
                            $resultBg = 'bg-purple-50 border-purple-200';
                            $icon = 'fa-star';
                            $rec = "Maintained excellent scores across both assessments. Strong mastery.";
                        } else if ($postPct == $prePct) {
                            $resultText = 'No Improvement';
                            $resultColor = 'text-amber-600';
                            $resultBg = 'bg-amber-50 border-amber-200';
                            $icon = 'fa-minus';
                            $rec = "No measurable improvement in comprehension. Additional support recommended.";
                        } else {
                            $resultText = 'Declined';
                            $resultColor = 'text-rose-600';
                            $resultBg = 'bg-rose-50 border-rose-200';
                            $icon = 'fa-arrow-trend-down';
                            $rec = "Performance decreased. Review student needs and provide support.";
                        }
                    ?>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                            <h2 class="text-lg font-bold text-slate-800">Grade <?php echo $grade; ?> Reading Module</h2>
                            <div class="flex items-center gap-2 px-3 py-1 rounded-full <?php echo $resultBg; ?> border">
                                <i class="fas <?php echo $icon; ?> <?php echo $resultColor; ?>"></i>
                                <span class="text-sm font-bold <?php echo $resultColor; ?>"><?php echo $resultText; ?></span>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                
                                <!-- Pre Test Card -->
                                <div class="border border-slate-200 rounded-lg p-5 <?php echo $pre ? 'bg-white' : 'bg-slate-50'; ?>">
                                    <h3 class="font-bold text-slate-700 uppercase tracking-wider text-sm mb-4 border-b pb-2">Pre-Test</h3>
                                    <?php if ($pre): ?>
                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1">Comprehension</div>
                                                <div class="text-2xl font-black text-blue-600"><?php echo $pre['comp_raw']; ?>/<?php echo $pre['total_questions']; ?></div>
                                                <div class="text-sm font-medium text-slate-500"><?php echo number_format($prePct, 1); ?>%</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1">Reading Accuracy</div>
                                                <div class="text-2xl font-black text-emerald-600"><?php echo number_format($pre['accuracy_score'], 1); ?>%</div>
                                                <div class="text-sm font-medium text-slate-500">Profile: <?php echo $pre['oral_reading_profile']; ?></div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1">Reading Speed</div>
                                                <div class="text-xl font-bold text-slate-700"><?php echo round($pre['reading_speed']); ?> <span class="text-sm font-normal text-slate-500">WPM</span></div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1">Correct Words</div>
                                                <div class="text-xl font-bold text-slate-700"><?php echo $pre['wcpm']; ?> <span class="text-sm font-normal text-slate-500">WCPM</span></div>
                                            </div>
                                        </div>
                                        <div class="mt-4 pt-4 border-t border-slate-100 text-center">
                                            <a href="review_detail.php?id=<?php echo $pre['id']; ?>&sid=<?php echo $student_id; ?>&return=grades" class="inline-block px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition">
                                                <i class="fas fa-headphones mr-2"></i> Review Audio & Transcript
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-8 text-slate-400">
                                            <i class="fas fa-times-circle text-3xl mb-2 opacity-50"></i>
                                            <p class="font-medium">Not Taken</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Post Test Card -->
                                <div class="border border-slate-200 rounded-lg p-5 <?php echo $post ? 'bg-white' : 'bg-slate-50'; ?>">
                                    <h3 class="font-bold text-slate-700 uppercase tracking-wider text-sm mb-4 border-b pb-2">Post-Test</h3>
                                    <?php if ($post): ?>
                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1">Comprehension</div>
                                                <div class="text-2xl font-black text-blue-600"><?php echo $post['comp_raw']; ?>/<?php echo $post['total_questions']; ?></div>
                                                <div class="text-sm font-medium text-slate-500"><?php echo number_format($postPct, 1); ?>%</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1">Reading Accuracy</div>
                                                <div class="text-2xl font-black text-emerald-600"><?php echo number_format($post['accuracy_score'], 1); ?>%</div>
                                                <div class="text-sm font-medium text-slate-500">Profile: <?php echo $post['oral_reading_profile']; ?></div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1">Reading Speed</div>
                                                <div class="text-xl font-bold text-slate-700"><?php echo round($post['reading_speed']); ?> <span class="text-sm font-normal text-slate-500">WPM</span></div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1">Correct Words</div>
                                                <div class="text-xl font-bold text-slate-700"><?php echo $post['wcpm']; ?> <span class="text-sm font-normal text-slate-500">WCPM</span></div>
                                            </div>
                                        </div>
                                        <div class="mt-4 pt-4 border-t border-slate-100 text-center">
                                            <a href="review_detail.php?id=<?php echo $post['id']; ?>&sid=<?php echo $student_id; ?>&return=grades" class="inline-block px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition">
                                                <i class="fas fa-headphones mr-2"></i> Review Audio & Transcript
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-8 text-slate-400">
                                            <i class="fas fa-spinner fa-spin text-3xl mb-2 opacity-50"></i>
                                            <p class="font-medium">Pending</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                            </div>
                            
                            <!-- Recommendation -->
                            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                <div class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-1">Final Recommendation</div>
                                <p class="text-sm text-blue-900"><?php echo $rec; ?></p>
                            </div>

                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <script>
        window.toggleSidebar = function() {
            const sidebar = document.getElementById('appSidebar');
            if(sidebar) sidebar.classList.toggle('-translate-x-full');
        }
    </script>
</body>
</html>
