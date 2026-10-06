<?php
// We expect $target_user_id to be set before including this file.

if(!function_exists('getTotalItemsFromJSON')) {
    function getTotalItemsFromJSON($json_string) {
        $q = json_decode($json_string, true);
        if(!$q) return 0;
        $total = 0;
        foreach($q as $item) {
            if ($item['type'] === 'multichoice' || !isset($item['type']) || isset($item['options'])) {
                $total += 1;
            } else if ($item['type'] === 'truefalse') {
                $total += 1;
            } else if ($item['type'] === 'enumeration') {
                $expectedCount = (isset($item['answers']) && count($item['answers']) > 0) ? count($item['answers']) : (intval($item['count'] ?? 1));
                $total += $expectedCount;
            } else if ($item['type'] === 'essay') {
                $pts = intval($item['points'] ?? 1);
                $total += $pts;
            }
        }
        return $total;
    }
}

// 1. Fetch Course pairs
$stmtC = $pdo->prepare("
    SELECT a.passage_id, a.phase, a.comprehension_score, c.title, c.questions_json 
    FROM reading_attempts a
    JOIN course_assessments c ON a.passage_id = c.id
    WHERE a.user_id = ? AND a.phase IN ('Course-Pre-Test', 'Course-Post-Test')
    ORDER BY a.created_at ASC
");
$stmtC->execute([$target_user_id]);
$c_attempts = $stmtC->fetchAll(PDO::FETCH_ASSOC);


$pairs = [];

// Process courses
foreach($c_attempts as $att) {
    $key = 'course_' . $att['passage_id'];
    if(!isset($pairs[$key])) {
        $pairs[$key] = ['title' => 'Course: ' . $att['title'], 'total' => getTotalItemsFromJSON($att['questions_json']), 'pre' => null, 'post' => null];
    }
    if($att['phase'] === 'Course-Pre-Test') $pairs[$key]['pre'] = $att['comprehension_score'];
    else $pairs[$key]['post'] = $att['comprehension_score'];
}


// Filter to only those with BOTH Pre and Post, OR just show all that have at least Pre?
// "The system should compare the Pre-Test and Post-Test."
// Let's show all that have BOTH, or if they only have Pre-Test, show it as Pending Post-Test.
$has_pairs = false;
foreach($pairs as $p) {
    if($p['pre'] !== null && $p['post'] !== null) {
        $has_pairs = true;
        break;
    }
}

if ($has_pairs): 
?>
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
    <div class="p-6 border-b border-slate-100 bg-blue-50/50">
        <h3 class="font-bold text-slate-800 text-lg"><i class="fas fa-chart-line text-blue-500 mr-2"></i> Progress & Recommendations</h3>
        <p class="text-sm text-slate-500 mt-1">Comparison of Pre-Test and Post-Test scores.</p>
    </div>
    <div class="p-6">
        <div class="space-y-6">
            <?php foreach($pairs as $p): 
                if ($p['pre'] === null || $p['post'] === null) continue; 
                
                $total = $p['total'];
                // Prevent division by zero logic issues if total is 0
                if ($total <= 0) $total = 1; 

                $prePct = floatval($p['pre']);
                $postPct = floatval($p['post']);
                
                $preRaw = round(($prePct / 100) * $total);
                $postRaw = round(($postPct / 100) * $total);
                
                $diffRaw = $postRaw - $preRaw;
                $diffPct = $postPct - $prePct;
                
                if ($postPct > $prePct) {
                    $resultText = 'Improved';
                    $resultColor = 'text-emerald-600';
                    $resultBg = 'bg-emerald-50 border-emerald-200';
                    $icon = 'fa-arrow-trend-up text-emerald-500';
                    $rec = "The student demonstrated improvement from the Pre-Test to the Post-Test. Continue the current reading instruction and monitor the student's progress.";
                } else if ($postPct == $prePct) {
                    $resultText = 'No Improvement';
                    $resultColor = 'text-amber-600';
                    $resultBg = 'bg-amber-50 border-amber-200';
                    $icon = 'fa-minus text-amber-500';
                    $rec = "The student showed no measurable improvement between the Pre-Test and Post-Test. Additional reading support and review of the student's learning needs are recommended.";
                } else {
                    $resultText = 'Declined';
                    $resultColor = 'text-rose-600';
                    $resultBg = 'bg-rose-50 border-rose-200';
                    $icon = 'fa-arrow-trend-down text-rose-500';
                    $rec = "The student's Post-Test performance decreased compared with the Pre-Test. The teacher should review the student's reading needs and provide additional support.";
                }
            ?>
            <div class="border border-slate-200 rounded-lg p-5 hover:shadow-md transition bg-slate-50">
                <h4 class="font-bold text-slate-700 text-lg mb-4 border-b pb-2"><?php echo htmlspecialchars($p['title']); ?></h4>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Pre Test -->
                    <div class="bg-white p-4 rounded border border-slate-200 text-center">
                        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pre-Test</div>
                        <div class="text-2xl font-black text-slate-800"><?php echo $preRaw; ?>/<?php echo $total; ?></div>
                        <div class="text-sm font-semibold text-slate-500"><?php echo number_format($prePct, 1); ?>%</div>
                    </div>
                    
                    <!-- Post Test -->
                    <div class="bg-white p-4 rounded border border-slate-200 text-center">
                        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Post-Test</div>
                        <div class="text-2xl font-black text-slate-800"><?php echo $postRaw; ?>/<?php echo $total; ?></div>
                        <div class="text-sm font-semibold text-slate-500"><?php echo number_format($postPct, 1); ?>%</div>
                    </div>
                    
                    <!-- Improvement -->
                    <div class="bg-white p-4 rounded border border-slate-200 text-center relative overflow-hidden">
                        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Improvement</div>
                        <div class="text-2xl font-black <?php echo $resultColor; ?>">
                            <?php echo $diffRaw > 0 ? '+' : ''; ?><?php echo $diffRaw; ?> <span class="text-sm font-medium text-slate-500">items</span>
                        </div>
                        <div class="text-sm font-bold <?php echo $resultColor; ?>">
                            <?php echo $diffPct > 0 ? '+' : ''; ?><?php echo number_format($diffPct, 1); ?> percentage points
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="md:w-1/3 p-4 rounded border <?php echo $resultBg; ?> flex items-center justify-center">
                        <i class="fas <?php echo $icon; ?> text-2xl mr-3"></i>
                        <span class="text-xl font-black uppercase tracking-wider <?php echo $resultColor; ?>"><?php echo $resultText; ?></span>
                    </div>
                    <div class="md:w-2/3 p-4 rounded border border-slate-200 bg-white">
                        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Recommendation</div>
                        <p class="text-sm text-slate-700 font-medium"><?php echo $rec; ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
