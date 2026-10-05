<?php
session_start();
require_once 'config.php';

// RBAC Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $keys = [
        'asr_enabled', 
        'azure_openai_key', 
        'openai_model',
        'azure_speech_key',
        'azure_region', 
        'azure_language', 
        'tts_voice', 
        'tts_model', 
        'tts_personality'
    ];
    
    // Save to DB
    foreach ($keys as $key) {
        $val = $_POST[$key] ?? '';
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v) ON DUPLICATE KEY UPDATE setting_value = :v2");
        $stmt->execute(['k' => $key, 'v' => $val, 'v2' => $val]);
    }
    
    // Generate .env file for the Python backend
    $env_content = "AZURE_OPENAI_API_KEY=" . ($_POST['azure_openai_key'] ?? '') . "\n";
    $env_content .= "AZURE_SPEECH_KEY=" . ($_POST['azure_speech_key'] ?? '') . "\n";
    $env_content .= "AZURE_REGION=" . ($_POST['azure_region'] ?? '') . "\n";
    $env_content .= "AZURE_LANGUAGE=" . ($_POST['azure_language'] ?? '') . "\n";
    $env_content .= "OPENAI_MODEL=" . ($_POST['openai_model'] ?? '') . "\n";
    
    file_put_contents(__DIR__ . '/v536/.env', $env_content);
    
        
    // --- BACKGROUND PROCESS MANAGEMENT ---
    $has_openai_key = !empty($_POST['azure_openai_key']);
    $has_speech_key = !empty($_POST['azure_speech_key']);
    $has_region = !empty($_POST['azure_region']);
    $req_met = $has_openai_key && $has_speech_key && $has_region;
    $is_enabled = ($_POST['asr_enabled'] ?? '') == '1';

    $python_path = __DIR__ . '/v536/.azure-venv/bin/python3';
    $script_path = __DIR__ . '/v536/service.py';
    $log_path = __DIR__ . '/v536/service.log';
    
    // 1. Always kill any existing process first
    exec("pkill -f 'python3 $script_path'");
    
    // 2. Start new daemon if enabled and ready
    if ($is_enabled && $req_met) {
        $cwd = __DIR__ . '/v536';
        $cmd = "cd $cwd && nohup $python_path $script_path > $log_path 2>&1 &";
        exec($cmd);
        $success = "Settings saved. ASR Background Service is RUNNING.";
    } else if ($is_enabled && !$req_met) {
        $success = "Settings saved. ASR is ON, but missing keys. Service NOT running.";
    } else {
        $success = "Settings saved. ASR Background Service STOPPED.";
    }

}

// Fetch current settings
$stmt = $pdo->query("SELECT * FROM settings");
$settings_raw = $stmt->fetchAll();
$settings = [];
foreach($settings_raw as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

$has_openai_key = !empty($settings['azure_openai_key']);
$has_speech_key = !empty($settings['azure_speech_key']);
$has_region = !empty($settings['azure_region']);

$requirements_met = $has_openai_key && $has_speech_key && $has_region;
$is_enabled = ($settings['asr_enabled'] ?? '') == '1';

if ($is_enabled && $requirements_met) {
    $badge_class = "bg-emerald-100 text-emerald-700 border-emerald-200";
    $badge_icon = "fas fa-check-circle";
    $badge_text = "Active & Ready";
} elseif ($is_enabled && !$requirements_met) {
    $badge_class = "bg-amber-100 text-amber-700 border-amber-200";
    $badge_icon = "fas fa-exclamation-triangle";
    $badge_text = "Action Required: Missing Keys";
} else {
    $badge_class = "bg-slate-100 text-slate-500 border-slate-200";
    $badge_icon = "fas fa-power-off";
    $badge_text = "Disabled";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASR Settings - Teacher Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="flex flex-col h-screen">
    
    <!-- Top Navbar -->
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0">
        <div class="flex items-center">
            <a href="dashboard_teacher.php" class="text-slate-400 hover:text-slate-600 mr-4"><i class="fas fa-arrow-left"></i></a>
            <i class="fas fa-cog text-slate-500 text-2xl mr-3"></i>
            <h1 class="font-bold text-lg text-slate-800">ASR & API Settings</h1>
        </div>
    </header>

    <main class="flex-1 p-8 max-w-4xl mx-auto w-full overflow-y-auto">
        <?php if($success): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6 flex items-start">
                <i class="fas fa-check-circle mt-0.5 mr-2"></i>
                <span><?php echo $success; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 space-y-6">
            
            <div class="flex items-start justify-between border-b pb-4 mb-4">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h2 class="text-xl font-bold text-slate-800">Enable ASR Service</h2>
                        <span class="px-3 py-1 rounded-full text-xs font-bold border <?php echo $badge_class; ?>">
                            <i class="<?php echo $badge_icon; ?> mr-1"></i> <?php echo $badge_text; ?>
                        </span>
                    </div>
                    <p class="text-sm text-slate-500">Toggle whether students can use the automated voice reading assessments.</p>
                    
                    <?php if ($is_enabled && !$requirements_met): ?>
                        <div class="mt-2 text-xs text-amber-600 font-medium">
                            The service is turned ON, but it will fail until you provide:
                            <ul class="list-disc ml-5 mt-1">
                                <?php if (!$has_openai_key): ?><li>OpenAI API Key</li><?php endif; ?>
                                <?php if (!$has_speech_key): ?><li>Azure Speech API Key</li><?php endif; ?>
                                <?php if (!$has_region): ?><li>Azure Region</li><?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mt-1">
                    <select name="asr_enabled" class="border border-slate-300 rounded-lg px-4 py-2 font-bold focus:ring-blue-500 focus:border-blue-500">
                        <option value="1" <?php echo ($settings['asr_enabled'] ?? '') == '1' ? 'selected' : ''; ?>>ON (Enabled)</option>
                        <option value="0" <?php echo ($settings['asr_enabled'] ?? '') == '0' ? 'selected' : ''; ?>>OFF (Disabled)</option>
                    </select>
                </div>
            </div>

            <h3 class="font-bold text-slate-800 text-lg mb-2"><i class="fas fa-robot text-blue-500 mr-2"></i> OpenAI Configuration</h3>
            <div class="grid grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">OpenAI API Key</label>
                    <input type="password" name="azure_openai_key" value="<?php echo htmlspecialchars($settings['azure_openai_key'] ?? ''); ?>" placeholder="sk-proj-..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">OpenAI Realtime Model</label>
                    <input type="text" name="openai_model" value="<?php echo htmlspecialchars($settings['openai_model'] ?? 'gpt-4o-realtime-preview-2024-10-01'); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <h3 class="font-bold text-slate-800 text-lg mb-2 mt-8"><i class="fas fa-cloud text-blue-500 mr-2"></i> Azure Speech SDK Configuration</h3>
            <div class="grid grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Azure Speech API Key</label>
                    <input type="password" name="azure_speech_key" value="<?php echo htmlspecialchars($settings['azure_speech_key'] ?? ''); ?>" placeholder="Enter Azure Speech Resource Key..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Azure Region</label>
                    <input type="text" name="azure_region" value="<?php echo htmlspecialchars($settings['azure_region'] ?? 'eastus'); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Azure Language</label>
                    <input type="text" name="azure_language" value="en-US" readonly class="w-full px-4 py-2 border border-slate-200 bg-slate-50 text-slate-500 rounded-lg cursor-not-allowed">
                    <p class="text-[10px] text-slate-400 mt-1">Must be en-US for this deployment.</p>
                </div>
            </div>

            <h3 class="font-bold text-slate-800 text-lg mb-2 mt-8"><i class="fas fa-volume-up text-blue-500 mr-2"></i> OpenAI TTS (Text-To-Speech)</h3>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">TTS Voice</label>
                    <select name="tts_voice" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="alloy" <?php echo ($settings['tts_voice'] ?? '') == 'alloy' ? 'selected' : ''; ?>>Alloy</option>
                        <option value="echo" <?php echo ($settings['tts_voice'] ?? '') == 'echo' ? 'selected' : ''; ?>>Echo</option>
                        <option value="fable" <?php echo ($settings['tts_voice'] ?? '') == 'fable' ? 'selected' : ''; ?>>Fable</option>
                        <option value="onyx" <?php echo ($settings['tts_voice'] ?? '') == 'onyx' ? 'selected' : ''; ?>>Onyx</option>
                        <option value="nova" <?php echo ($settings['tts_voice'] ?? '') == 'nova' ? 'selected' : ''; ?>>Nova</option>
                        <option value="shimmer" <?php echo ($settings['tts_voice'] ?? '') == 'shimmer' ? 'selected' : ''; ?>>Shimmer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">TTS Model</label>
                    <input type="text" name="tts_model" value="<?php echo htmlspecialchars($settings['tts_model'] ?? 'tts-1'); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">TTS Personality Prompt</label>
                    <textarea name="tts_personality" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['tts_personality'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="pt-6 border-t mt-8 text-right">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-8 rounded-lg transition shadow-sm">Save Settings</button>
            </div>
        </form>
    </main>
</body>
</html>
