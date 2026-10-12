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

if (!function_exists('get_sections_by_grade')) {
    function get_sections_by_grade($pdo) {
        $default_sections = [
            'Grade 7' => ['Rizal', 'Luna', 'Bonifacio', 'Del Pilar'],
            'Grade 8' => ['Sapphire', 'Ruby', 'Emerald', 'Bloodstone'],
            'Grade 9' => ['Phoenix', 'Falcon', 'Lionheart', 'Raven'],
            'Grade 10' => ['Crimson', 'Amber', 'Coral', 'Ivory']
        ];

        if (!$pdo) {
            return $default_sections;
        }

        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'available_sections'");
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row && !empty(trim($row['setting_value'] ?? ''))) {
                $raw = trim($row['setting_value']);
                $decoded = json_decode($raw, true);

                if (is_array($decoded)) {
                    $result = [];
                    foreach (['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'] as $g) {
                        $short = preg_replace('/[^0-9]/', '', $g);
                        $raw_list = $decoded[$g] ?? $decoded[$short] ?? [];
                        if (is_array($raw_list)) {
                            $clean_list = [];
                            foreach ($raw_list as $item) {
                                $t = trim((string)$item);
                                if ($t !== '' && !in_array($t, $clean_list)) {
                                    $clean_list[] = $t;
                                }
                            }
                            $result[$g] = $clean_list;
                        } else {
                            $result[$g] = [];
                        }
                    }
                    return $result;
                }

                // Legacy comma/newline separated fallback
                $lines = preg_split('/[\r\n,]+/', $raw);
                $result = [
                    'Grade 7' => [],
                    'Grade 8' => [],
                    'Grade 9' => [],
                    'Grade 10' => []
                ];
                foreach ($lines as $item) {
                    $item = trim($item);
                    if ($item === '') continue;

                    if (preg_match('/^Grade\s*7\s*[:-]?\s*(.+)$/i', $item, $m)) {
                        $clean = trim($m[1]);
                        if ($clean !== '' && !in_array($clean, $result['Grade 7'])) $result['Grade 7'][] = $clean;
                    } elseif (preg_match('/^Grade\s*8\s*[:-]?\s*(.+)$/i', $item, $m)) {
                        $clean = trim($m[1]);
                        if ($clean !== '' && !in_array($clean, $result['Grade 8'])) $result['Grade 8'][] = $clean;
                    } elseif (preg_match('/^Grade\s*9\s*[:-]?\s*(.+)$/i', $item, $m)) {
                        $clean = trim($m[1]);
                        if ($clean !== '' && !in_array($clean, $result['Grade 9'])) $result['Grade 9'][] = $clean;
                    } elseif (preg_match('/^Grade\s*10\s*[:-]?\s*(.+)$/i', $item, $m)) {
                        $clean = trim($m[1]);
                        if ($clean !== '' && !in_array($clean, $result['Grade 10'])) $result['Grade 10'][] = $clean;
                    } else {
                        // Unprefixed legacy section, assign to Grade 7
                        if (!in_array($item, $result['Grade 7'])) {
                            $result['Grade 7'][] = $item;
                        }
                    }
                }

                // If any grade ended up completely empty, supply default sections
                foreach (['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'] as $g) {
                    if (empty($result[$g]) && !empty($default_sections[$g])) {
                        $result[$g] = $default_sections[$g];
                    }
                }

                return $result;
            }
        } catch (Exception $e) {
            // Fallback on database error
        }

        return $default_sections;
    }
}

if (!function_exists('save_sections_by_grade')) {
    function save_sections_by_grade($pdo, $sections_by_grade) {
        $clean = [];
        foreach (['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'] as $g) {
            $short = preg_replace('/[^0-9]/', '', $g);
            $raw_list = $sections_by_grade[$g] ?? $sections_by_grade[$short] ?? [];
            if (!is_array($raw_list)) {
                $raw_list = [];
            }
            $clean_list = [];
            foreach ($raw_list as $item) {
                $t = trim((string)$item);
                if ($t !== '' && !in_array($t, $clean_list)) {
                    $clean_list[] = $t;
                }
            }
            $clean[$g] = $clean_list;
        }

        $json = json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('available_sections', :v) ON DUPLICATE KEY UPDATE setting_value = :v2");
        $stmt->execute(['v' => $json, 'v2' => $json]);
        return $clean;
    }
}
?>
