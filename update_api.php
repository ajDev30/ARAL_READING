<?php
$file = '/var/www/andrew/ARAL_READING/api_assessment.php';
$content = file_get_contents($file);

$old_gst = <<<'EOD'
if ($action === 'get_gst') {
    $grade = $_GET['grade'] ?? 7;
    // For now, mock GST questions if no table exists, or fetch from DB if we had a gst_questions table.
    // The user requested to be able to manage this, but didn't provide a DB schema for it.
    // I will return a placeholder array of 40 questions to satisfy the front-end logic temporarily, 
    // but ideally, this comes from a managed database table.
    $questions = [];
    for($i=1; $i<=15; $i++) {
        $questions[] = [
            "id" => $i,
            "question" => "Sample English GST Question $i (Replace in Teacher Portal)",
            "options" => ["Option A", "Option B", "Option C", "Option D"],
            "correct" => 0
        ];
    }
    echo json_encode(['questions' => $questions]);
    exit;
}
EOD;

$new_gst = <<<'EOD'
if ($action === 'get_gst') {
    $grade = $_GET['grade'] ?? '7';
    if(is_numeric($grade)) $grade = "Grade $grade";

    $stmt = $pdo->prepare("SELECT questions_json, time_limit_minutes FROM gst_assessments WHERE grade_level = ? LIMIT 1");
    $stmt->execute([$grade]);
    $gst = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($gst) {
        echo json_encode([
            'questions' => json_decode($gst['questions_json'], true) ?: [],
            'time_limit' => (int)$gst['time_limit_minutes']
        ]);
    } else {
        echo json_encode(['questions' => [], 'time_limit' => 0]);
    }
    exit;
}
EOD;

$content = str_replace($old_gst, $new_gst, $content);
file_put_contents($file, $content);
echo "Updated api_assessment.php\n";
