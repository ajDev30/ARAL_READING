<?php
$file = '/var/www/andrew/ARAL_READING/assessment.php';
$content = file_get_contents($file);

// Replace realtimeTranscript with transcript + transcriptNotice
$content = str_replace(
    '<article id="realtimeTranscript" class="story text-muted" style="font-size: 1.1rem; line-height:1.6;"></article>',
    '<article id="transcript" class="story text-muted" style="font-size: 1.1rem; line-height:1.6;"></article><div id="transcriptNotice" hidden></div>',
    $content
);

// Add missing metrics below the transcript column
$metricsHtml = <<<'HTML'
                        </div>
                    </div>
                    
                    <div class="row mt-4 border-top pt-3">
                        <div class="col-md-3 text-center">
                            <span class="text-secondary small font-weight-bold">TIME</span><br>
                            <h4 id="timer" class="text-primary font-weight-bold">00:00</h4>
                        </div>
                        <div class="col-md-3 text-center">
                            <span class="text-secondary small font-weight-bold">STORY WORDS</span><br>
                            <h4 id="storyWordCount" class="text-primary font-weight-bold">0</h4>
                            <span id="wordBadge" hidden></span>
                        </div>
                        <div class="col-md-3 text-center">
                            <span class="text-secondary small font-weight-bold">WPM</span><br>
                            <h4 id="wpm" class="text-success font-weight-bold">—</h4>
                        </div>
                        <div class="col-md-3 text-center">
                            <span class="text-secondary small font-weight-bold">ACCURACY</span><br>
                            <h4 id="readingAccuracy" class="text-success font-weight-bold">—</h4>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <div id="miscueStrip" class="d-flex justify-content-center gap-2 flex-wrap"></div>
                        </div>
                    </div>
                    
                    <!-- Hidden elements needed by app.js -->
                    <div id="tooltip" hidden style="position:absolute; background:#333; color:#fff; padding:5px; border-radius:4px; z-index:9999;"></div>
                    <button id="editStoryBtn" hidden></button>
                    <button id="restartBtn" hidden></button>
                    <div id="diagModal" hidden></div>
                    <button id="closeDiagBtn" hidden></button>
                    <div id="diagContent" hidden></div>
                    <select id="locale" hidden><option value="en-US">en-US</option></select>
                    <div id="meterBar" hidden></div>
                    <span id="liveBadge" hidden></span>
HTML;

$content = str_replace('                        </div>'."\n".'                    </div>', $metricsHtml, $content);

file_put_contents($file, $content);
echo "Injected missing UI.\n";
