<?php
// We expect $target_user_id to be set before including this file.

// Fetch Course pairs with all 4 metrics
$stmtC = $pdo->prepare("
    SELECT a.passage_id, a.phase, a.accuracy_score, a.comprehension_score, a.reading_speed, a.oral_reading_profile, c.title 
    FROM reading_attempts a
    JOIN course_assessments c ON a.passage_id = c.id
    WHERE a.user_id = ? AND a.phase IN ('Course-Pre-Test', 'Course-Post-Test')
    ORDER BY a.created_at ASC
");
$stmtC->execute([$target_user_id]);
$c_attempts = $stmtC->fetchAll(PDO::FETCH_ASSOC);

$pairs = [];
foreach($c_attempts as $a) {
    // Extract Course Grade or Title group (e.g. "GRADE 7" from "GRADE 7 - PRE-TEST ...")
    $courseGroup = 'Course Assessment';
    if (preg_match('/^(.*?)(?:\s*(?:—|-)\s*(?:PRE-TEST|POST-TEST))/i', $a['title'], $matches)) {
        $courseGroup = trim($matches[1]);
    } else {
        // Fallback if title doesn't match expected pattern
        $courseGroup = $a['title'];
    }

    if(!isset($pairs[$courseGroup])) {
        $pairs[$courseGroup] = [
            'title' => $courseGroup,
            'pre' => null,
            'post' => null
        ];
    }
    
    $acc = floatval($a['accuracy_score']);
    $comp = floatval($a['comprehension_score']);
    $wpm = floatval($a['reading_speed']);
    $wcpm = round($wpm * ($acc / 100));

    $data = [
        'acc' => $acc,
        'comp' => $comp,
        'wpm' => $wpm,
        'wcpm' => $wcpm,
        'prof' => $a['oral_reading_profile']
    ];

    if($a['phase'] === 'Course-Pre-Test') {
        $pairs[$courseGroup]['pre'] = $data;
    } else {
        $pairs[$courseGroup]['post'] = $data;
    }
}

function getProfLevel($prof) {
    if ($prof === 'Independent') return 3;
    if ($prof === 'Instructional') return 2;
    if ($prof === 'Frustration') return 1;
    return 0;
}
?>

<?php if(count($pairs) > 0): ?>
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-8">
    <div class="p-6 border-b border-slate-100">
        <h3 class="font-bold text-slate-800 text-lg">Course Assessment Progress</h3>
        <p class="text-sm text-slate-500">Comparison of Pre-Test and Post-Test scores.</p>
    </div>
    <div class="p-6 bg-slate-50">
        <div class="space-y-6">
            <?php foreach($pairs as $p): 
                if ($p['pre'] === null && $p['post'] === null) continue; 

                $hasPre = $p['pre'] !== null;
                $hasPost = $p['post'] !== null;
                
                if ($hasPre && $hasPost) {
                    $pre = $p['pre'];
                    $post = $p['post'];
                    
                    $accDiff = $post['acc'] - $pre['acc'];
                    $compDiff = $post['comp'] - $pre['comp'];
                    $wpmDiff = $post['wpm'] - $pre['wpm'];
                    $wcpmDiff = $post['wcpm'] - $pre['wcpm'];
                    
                    $preLevel = getProfLevel($pre['prof']);
                    $postLevel = getProfLevel($post['prof']);
                    
                    $accImproved = $accDiff > 0;
                    $compImproved = $compDiff > 0;
                    $accDeclined = $accDiff < 0;
                    $compDeclined = $compDiff < 0;
                    
                    $fluencyStable = $wcpmDiff >= -2 && $wpmDiff >= -2;
                    
                    if ($postLevel > $preLevel || (($accImproved || $compImproved) && $fluencyStable)) {
                        $resultText = 'Improved';
                        $resultColor = 'text-emerald-600';
                        $resultBg = 'bg-emerald-50 border-emerald-200';
                        $icon = 'fa-arrow-trend-up text-emerald-500';
                        
                        if ($postLevel > $preLevel) {
                            $rec = "The student's overall reading profile successfully improved from {$pre['prof']} to {$post['prof']}.";
                        } else {
                            $rec = "The student showed clear improvement in " . ($accImproved ? "Accuracy " : "") . ($compImproved ? "Comprehension" : "") . " while maintaining reading fluency.";
                        }
                    } else if ($postLevel < $preLevel || ($accDeclined && $compDeclined)) {
                        $resultText = 'Declined';
                        $resultColor = 'text-rose-600';
                        $resultBg = 'bg-rose-50 border-rose-200';
                        $icon = 'fa-arrow-trend-down text-rose-500';
                        
                        if ($postLevel < $preLevel) {
                            $rec = "The student's overall reading profile dropped from {$pre['prof']} to {$post['prof']}. Additional support is needed.";
                        } else {
                            $rec = "Both Accuracy and Comprehension declined meaningfully. The teacher should review the student's reading needs and provide additional support.";
                        }
                    } else if ($accImproved || $compImproved) {
                        $resultText = 'Partial Progress';
                        $resultColor = 'text-blue-600';
                        $resultBg = 'bg-blue-50 border-blue-200';
                        $icon = 'fa-arrow-up-right-dots text-blue-500';
                        $rec = "The reading profile remains {$pre['prof']}, but the student showed measurable improvement in " . ($accImproved ? "Accuracy" : "Comprehension") . ".";
                    } else {
                        $resultText = 'Maintained';
                        $resultColor = 'text-amber-600';
                        $resultBg = 'bg-amber-50 border-amber-200';
                        $icon = 'fa-minus text-amber-500';
                        $rec = "The student's reading profile remains at {$pre['prof']} and metrics are generally stable with no meaningful improvement or decline.";
                    }
                } else if (!$hasPost) {
                    $resultText = 'In Progress';
                    $resultColor = 'text-blue-600';
                    $resultBg = 'bg-blue-50 border-blue-200';
                    $icon = 'fa-spinner text-blue-500';
                    $rec = "The student has completed the Pre-Test. They should now review the course material and proceed to take the Post-Test when ready.";
                } else if (!$hasPre) {
                    $resultText = 'Post-Test Only';
                    $resultColor = 'text-slate-600';
                    $resultBg = 'bg-slate-50 border-slate-200';
                    $icon = 'fa-check text-slate-500';
                    $rec = "The student completed the Post-Test without a recorded Pre-Test.";
                }
            ?>
            <div class="border border-slate-200 rounded-lg p-5 hover:shadow-md transition bg-slate-50">
                <h4 class="font-bold text-slate-700 text-lg mb-4 border-b pb-2"><?php echo htmlspecialchars($p['title']); ?></h4>
                
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-4">
                    <?php 
                    $metrics = [
                        ['label' => 'Accuracy', 'key' => 'acc', 'suffix' => '%'],
                        ['label' => 'Comprehension', 'key' => 'comp', 'suffix' => '%'],
                        ['label' => 'WCPM', 'key' => 'wcpm', 'suffix' => ''],
                        ['label' => 'WPM', 'key' => 'wpm', 'suffix' => '']
                    ];
                    foreach($metrics as $m): 
                        $key = $m['key'];
                        $preVal = $hasPre ? $p['pre'][$key] : null;
                        $postVal = $hasPost ? $p['post'][$key] : null;
                    ?>
                    <div class="bg-white p-3 rounded border border-slate-200">
                        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 text-center border-b pb-1"><?php echo $m['label']; ?></div>
                        <div class="flex justify-between items-center text-sm">
                            <div class="text-center w-1/3">
                                <div class="text-[10px] text-slate-400 uppercase">Pre</div>
                                <div class="font-bold text-slate-700"><?php echo $preVal !== null ? $preVal . $m['suffix'] : '—'; ?></div>
                            </div>
                            <div class="text-center w-1/3">
                                <i class="fas fa-arrow-right text-slate-300 text-xs"></i>
                            </div>
                            <div class="text-center w-1/3">
                                <div class="text-[10px] text-slate-400 uppercase">Post</div>
                                <div class="font-bold text-slate-700"><?php echo $postVal !== null ? $postVal . $m['suffix'] : '—'; ?></div>
                            </div>
                        </div>
                        <?php if ($hasPre && $hasPost): 
                            $diff = $postVal - $preVal;
                            $diffColor = $diff > 0 ? 'text-emerald-600' : ($diff < 0 ? 'text-rose-600' : 'text-slate-400');
                            $diffSign = $diff > 0 ? '+' : '';
                        ?>
                            <div class="text-center mt-2 pt-1 border-t border-slate-100">
                                <span class="text-xs font-bold <?php echo $diffColor; ?>"><?php echo $diffSign . $diff . $m['suffix']; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="md:w-1/3 p-4 rounded border <?php echo $resultBg; ?> flex items-center justify-center">
                        <i class="fas <?php echo $icon; ?> text-2xl mr-3"></i>
                        <span class="text-xl font-black uppercase tracking-wider <?php echo $resultColor; ?> text-center"><?php echo $resultText; ?></span>
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
