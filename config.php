<?php
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'aral_reading_db';
$username = getenv('DB_USER') ?: 'andrew';
$password = getenv('DB_PASS') ?: '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (!function_exists('get_student_reading_level')) {
    function get_student_reading_level($pdo, $user_id, $user_data = null) {
        if (!$pdo || !$user_id) return 'Pending';
        if (!$user_data) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user_data = $stmt->fetch();
        }
        if (!$user_data) return 'Pending';

        $curr = $user_data['current_level'] ?? '';
        if ($curr === 'Pending') return 'Pending';

        $enrolled_grade = intval(preg_replace('/[^0-9]/', '', $user_data['grade_level'] ?? ''));
        if ($enrolled_grade <= 0) $enrolled_grade = 7;

        // Check GST
        $stmtGST = $pdo->prepare("SELECT needs_individual_assessment FROM gst_results WHERE user_id = ? ORDER BY completed_at DESC LIMIT 1");
        $stmtGST->execute([$user_id]);
        $gst_result = $stmtGST->fetch();

        if (isset($gst_result['needs_individual_assessment']) && (int)$gst_result['needs_individual_assessment'] === 0) {
            return 'Independent';
        }

        // Check Consolidated Reading Profile
        $stmtP = $pdo->prepare("SELECT * FROM reading_profiles WHERE user_id = ?");
        $stmtP->execute([$user_id]);
        $profile = $stmtP->fetch();

        if ($profile && ($profile['independent_grade'] !== null || $profile['instructional_grade'] !== null || $profile['frustration_grade'] !== null)) {
            if ($profile['independent_grade'] !== null && $enrolled_grade <= intval($profile['independent_grade'])) {
                return 'Independent';
            } elseif ($profile['instructional_grade'] !== null && $enrolled_grade <= intval($profile['instructional_grade'])) {
                return 'Instructional';
            } else {
                if ($profile['frustration_grade'] !== null && intval($profile['frustration_grade']) <= 4 && $profile['instructional_grade'] === null && $profile['independent_grade'] === null) {
                    return 'Non-Reader';
                } else {
                    return 'Frustration';
                }
            }
        }

        if (!empty($curr) && $curr !== 'Completed Profile') {
            return $curr;
        }

        return 'Instructional';
    }
}
?>
