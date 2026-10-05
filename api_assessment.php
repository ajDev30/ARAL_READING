<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'get_status') {
    // Check GST
    $stmt = $pdo->prepare("SELECT * FROM gst_results WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $gst = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch tested grades (attempts)
    $stmt = $pdo->prepare("SELECT passage_grade, oral_reading_profile as classification, accuracy_score as wr, comprehension_score as comp FROM reading_attempts WHERE user_id = ? AND passage_grade IS NOT NULL");
    $stmt->execute([$user_id]);
    $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tested_grades = [];
    foreach($attempts as $a) {
        $tested_grades[$a['passage_grade']] = [
            'classification' => $a['classification'],
            'wr' => $a['wr'],
            'comp' => $a['comp']
        ];
    }

    echo json_encode([
        'gst_completed' => $gst ? true : false,
        'gst_result' => $gst ? [
            'score' => (int)$gst['score'],
            'totalItems' => (int)$gst['total_items'],
            'percentage' => (float)$gst['percentage']
        ] : null,
        'tested_grades' => $tested_grades
    ]);
    exit;
}

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

if ($action === 'submit_gst') {
    $score = $_POST['score'] ?? 0;
    $total = $_POST['total'] ?? 0;
    $percentage = ($total > 0) ? ($score / $total) * 100 : 0;
    
    $stmt = $pdo->prepare("SELECT grade_level FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $gradeNum = (int) filter_var($stmt->fetchColumn(), FILTER_SANITIZE_NUMBER_INT);
    if(!$gradeNum) $gradeNum = 7;

    $starting_grade = $_POST['starting_grade'] ?? null;
    $needs_individual = $_POST['needs_individual'] ?? 1;
    $category = $_POST['category'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO gst_results (user_id, grade_level, score, total_items, percentage, gst_category, starting_grade, needs_individual_assessment) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $gradeNum, $score, $total, $percentage, $category, $starting_grade, $needs_individual]);
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'get_passage') {
    $grade = $_GET['grade'] ?? 'Grade 7'; // e.g., 'Grade 7' or '7'
    if(is_numeric($grade)) $grade = "Grade $grade";

    $stmt = $pdo->prepare("SELECT id, title, passage_text, questions_json FROM reading_passages WHERE grade_level = ? LIMIT 1");
    $stmt->execute([$grade]);
    $passage = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$passage) {
        // Fallback or empty
        echo json_encode(["passage_text" => ""]);
    } else {
        echo json_encode($passage);
    }
    exit;
}

if ($action === 'override_attempt') {
    if ($_SESSION['role'] !== 'teacher') { echo json_encode(['error' => 'Unauthorized']); exit; }
    
    $attempt_id = $_POST['attempt_id'] ?? 0;
    $acc = $_POST['accuracy_score'] ?? 0;
    $miscues = $_POST['miscues_json'] ?? '{}';
    $eval_data = $_POST['evaluation_data'] ?? null;
    
    $stmt = $pdo->prepare("UPDATE reading_attempts SET accuracy_score = ?, miscues_json = ?, evaluation_data = ? WHERE id = ?");
    $stmt->execute([$acc, $miscues, $eval_data, $attempt_id]);
    
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'submit_attempt') {
    $grade = $_POST['passage_grade'] ?? null;
    $acc = $_POST['accuracy_score'] ?? 0;
    $comp = $_POST['comprehension_score'] ?? 0;
    $class = $_POST['classification'] ?? 'Pending';
    $time = $_POST['reading_time'] ?? 0;
    $speed = $_POST['reading_speed'] ?? 0;
    $miscues = $_POST['miscues_json'] ?? '{}';
    $eval_data = $_POST['evaluation_data'] ?? null;
    
    $audio_path = null;
    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/audio/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = 'attempt_' . $user_id . '_' . time() . '.webm';
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $upload_dir . $filename)) {
            $audio_path = 'uploads/audio/' . $filename;
        }
    }
    
    // Find passage ID
    $stmt = $pdo->prepare("SELECT id FROM reading_passages WHERE grade_level = ? LIMIT 1");
    $stmt->execute(["Grade $grade"]);
    $pid = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->prepare("INSERT INTO reading_attempts (user_id, passage_id, passage_grade, accuracy_score, comprehension_score, oral_reading_profile, reading_time, reading_speed, miscues_json, evaluation_data, audio_path, status, phase) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Completed', 'Pre-Test')");
    $stmt->execute([$user_id, $pid, $grade, $acc, $comp, $class, $time, $speed, $miscues, $eval_data, $audio_path]);
    
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'finalize_profile') {
    $ind = $_POST['independent_grade'] ?? null;
    $ins = $_POST['instructional_grade'] ?? null;
    $fru = $_POST['frustration_grade'] ?? null;
    if($ind === '') $ind = null;
    if($ins === '') $ins = null;
    if($fru === '') $fru = null;

    $stmt = $pdo->prepare("REPLACE INTO reading_profiles (user_id, independent_grade, instructional_grade, frustration_grade) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $ind, $ins, $fru]);
    
    // Update main user record status
    $pdo->prepare("UPDATE users SET current_level = 'Completed Profile' WHERE id = ?")->execute([$user_id]);

    echo json_encode(['status' => 'success']);
    exit;
}

echo json_encode(['error' => 'Invalid action']);
