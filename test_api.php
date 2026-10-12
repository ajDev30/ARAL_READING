<?php
require_once 'config.php';
// Check GST
$stmt = $pdo->prepare("SELECT * FROM gst_results WHERE user_id = 999 ORDER BY id DESC LIMIT 1");
$stmt->execute();
$gst = $stmt->fetch(PDO::FETCH_ASSOC);
echo "GST: " . print_r($gst, true);
