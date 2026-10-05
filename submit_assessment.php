<?php
session_start();
require_once 'config.php';

// Must be logged in as student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$level_idx = isset($_POST['level_idx']) ? (int)$_POST['level_idx'] : 0;
$transcript    = $_POST['transcript'] ?? '';
$accuracy      = (float)($_POST['accuracy_score'] ?? 0.0);
$reading_time  = (int)($_POST['reading_time'] ?? 0);
$reading_speed = (float)($_POST['reading_speed'] ?? 0.0);
$miscues       = $_POST['miscues_json'] ?? '[]';
$answers_raw   = $_POST['answers_json'] ?? '[]';
$evaluation_data = $_POST['evaluation_data'] ?? '{}';
$philiri_json  = $_POST['philiri_json'] ?? '';
$passage_id    = (int)($_POST['passage_id'] ?? 1); // Mock default

// Calculate Comprehension Score (Simplistic mock logic based on answers)
$student_answers = json_decode($answers_raw, true) ?: [];
$correct = 0;
$total = count($student_answers);
foreach ($student_answers as $ans) {
    if (isset($ans['is_correct']) && $ans['is_correct']) {
        $correct++;
    }
}
$comprehension = $total > 0 ? ($correct / $total) * 100 : 100;

// Determine Oral Reading Profile
// Independent: WR 97-100, Comp 80-100
// Instructional: WR 90-96, Comp 59-79
// Frustration: WR < 90, Comp < 59
$profile = 'Frustration';
if ($accuracy >= 97 && $comprehension >= 80) {
    $profile = 'Independent';
} elseif ($accuracy >= 90 && $comprehension >= 59) {
    $profile = 'Instructional';
}

// Update user's current_level
$upd = $pdo->prepare("UPDATE users SET current_level = :lvl, test_status = 'Pre-Assessment Complete' WHERE id = :uid");
$upd->execute(['lvl' => $profile, 'uid' => $user_id]);

// Insert into reading_attempts
$stmt = $pdo->prepare("
    INSERT INTO reading_attempts 
    (user_id, passage_id, phase, transcript, accuracy_score, reading_time, reading_speed, miscues_json, answers_json, evaluation_data, comprehension_score, oral_reading_profile) 
    VALUES 
    (:uid, :pid, 'Pre-Assessment', :trans, :acc, :rtime, :rspeed, :miscues, :answers, :eval, :comp, :prof)
");

$result = $stmt->execute([
    'uid' => $user_id,
    'pid' => $passage_id,
    'trans' => $transcript,
    'acc' => $accuracy,
    'rtime' => $reading_time,
    'rspeed' => $reading_speed,
    'miscues' => $miscues,
    'answers' => $answers_raw,
    'eval' => $evaluation_data,
    'comp' => $comprehension,
    'prof' => $profile
]);

if ($result) {
    echo json_encode([
        'status' => 'success', 
        'cascadeTo' => 'done', 
        'classification' => $profile, 
        'accScore' => $accuracy, 
        'compScore' => $comprehension
    ]);
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Failed to save to database']);
}
