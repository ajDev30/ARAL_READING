<?php
$file = '/var/www/andrew/ARAL_READING/api_assessment.php';
$content = file_get_contents($file);

$old_gst = <<<'EOD'
if ($action === 'submit_gst') {
    $score = $_POST['score'] ?? 0;
    $total = $_POST['total'] ?? 0;
    $percentage = ($total > 0) ? ($score / $total) * 100 : 0;
    
    $stmt = $pdo->prepare("SELECT grade_level FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $gradeNum = (int) filter_var($stmt->fetchColumn(), FILTER_SANITIZE_NUMBER_INT);
    if(!$gradeNum) $gradeNum = 7;

    $stmt = $pdo->prepare("INSERT INTO gst_results (user_id, grade_level, score, total_items, percentage) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $gradeNum, $score, $total, $percentage]);
    
    echo json_encode(['success' => true]);
    exit;
}
EOD;

$new_gst = <<<'EOD'
if ($action === 'submit_gst') {
    $score = (int)($_POST['score'] ?? 0);
    $total = (int)($_POST['total'] ?? 0);
    $percentage = ($total > 0) ? ($score / $total) * 100 : 0;
    
    $starting_grade = isset($_POST['starting_grade']) ? (int)$_POST['starting_grade'] : null;
    $needs_individual = isset($_POST['needs_individual']) ? (int)$_POST['needs_individual'] : 1;
    $category = $_POST['category'] ?? 'Requires Individual Assessment';
    
    $stmt = $pdo->prepare("SELECT grade_level FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $gradeNum = (int) filter_var($stmt->fetchColumn(), FILTER_SANITIZE_NUMBER_INT);
    if(!$gradeNum) $gradeNum = 7;

    $stmt = $pdo->prepare("INSERT INTO gst_results (user_id, grade_level, score, total_items, percentage, gst_category, starting_grade, needs_individual_assessment) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $gradeNum, $score, $total, $percentage, $category, $starting_grade, $needs_individual]);
    
    echo json_encode(['success' => true]);
    exit;
}
EOD;

$content = str_replace($old_gst, $new_gst, $content);
file_put_contents($file, $content);
echo "Updated API\n";
