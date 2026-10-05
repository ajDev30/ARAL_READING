<?php
$file = '/var/www/andrew/ARAL_READING/teacher_settings.php';
$content = file_get_contents($file);

// 1. Add PHP validation logic right before the closing ?> of the initial PHP block
$php_logic = <<<'PHP'
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
PHP;

$content = str_replace('?>' . "\n" . '<!DOCTYPE html>', $php_logic . "\n" . '<!DOCTYPE html>', $content);

// 2. Replace the HTML block
$html_old = <<<'HTML'
            <div class="flex items-center justify-between border-b pb-4 mb-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Enable ASR Service</h2>
                    <p class="text-sm text-slate-500">Toggle whether students can use the automated voice reading assessments.</p>
                </div>
                <div>
HTML;

$html_new = <<<'HTML'
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
HTML;

$content = str_replace($html_old, $html_new, $content);
file_put_contents($file, $content);
echo "Update applied.\n";
