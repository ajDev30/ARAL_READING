<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

$attempt_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT a.*, u.fname, u.lname, u.grade_level, u.section 
    FROM reading_attempts a
    JOIN users u ON a.user_id = u.id
    WHERE a.id = ?
");
$stmt->execute([$attempt_id]);
$attempt = $stmt->fetch();

if (!$attempt) {
    die("Assessment not found.");
}

$eval_data = $attempt['evaluation_data'] ?: '{}';

// Fetch questions based on phase
$q_json = '[]';
if (strpos($attempt['phase'], 'Course') !== false) {
    $stmt = $pdo->prepare("SELECT questions_json FROM course_assessments WHERE id = ?");
    $stmt->execute([$attempt['passage_id']]);
    $q_json = $stmt->fetchColumn() ?: '[]';
} else {
    $stmt = $pdo->prepare("SELECT questions_json FROM reading_passages WHERE id = ?");
    $stmt->execute([$attempt['passage_id']]);
    $q_json = $stmt->fetchColumn() ?: '[]';
}
$answers_json = $attempt['answers_json'] ?: '{}';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="v536/public/favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Assessment - Teacher Dashboard</title>
    <!-- Use Tailwind for main layout, but keep Bootstrap for app.js DOM compatibility -->
    <script>
        const originalWarn = console.warn;
        console.warn = function() {
            if (arguments[0] && typeof arguments[0] === 'string' && arguments[0].includes('cdn.tailwindcss.com should not be used in production')) return;
            originalWarn.apply(console, arguments);
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="v536/public/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .override-btn:hover { background-color: #f1f5f9; cursor: pointer; }
            .sidebar { background-color: #1a365d; }
        <?php $is_dark = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark'; ?>
        <?php if($is_dark): ?>
        /* Refined Slate Dark Mode */
        body { background-color: #0f172a !important; color: #f8fafc !important; }
        .bg-white, .bg-slate-50 { background-color: #1e293b !important; border-color: #334155 !important; color: #f8fafc !important; }
        
        .text-slate-800, .text-slate-700 { color: #f1f5f9 !important; }
        .text-slate-600, .text-slate-500, .text-slate-400 { color: #cbd5e1 !important; }
        .border-slate-200, .border-slate-100, .border-b, .border-l { border-color: #334155 !important; }
        .border-slate-300 { border-color: #475569 !important; }
        .sidebar { background-color: #0b1120 !important; border-right: 1px solid #1e293b !important; }
        input, select, textarea { background-color: #0f172a !important; color: white !important; border-color: #475569 !important; }
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.5) !important; }

        /* Colored Badges / Cards Fixes */
        .bg-blue-50, .bg-blue-100 { background-color: rgba(59, 130, 246, 0.2) !important; color: #93c5fd !important; }
        .text-blue-600, .text-blue-700, .text-blue-800 { color: #60a5fa !important; }
        .border-blue-100, .border-blue-200, .border-l-blue-500 { border-color: rgba(59, 130, 246, 0.3) !important; }

        .bg-emerald-50, .bg-emerald-100 { background-color: rgba(16, 185, 129, 0.2) !important; color: #6ee7b7 !important; }
        .text-emerald-600, .text-emerald-700, .text-emerald-800 { color: #34d399 !important; }
        .border-emerald-100, .border-emerald-200 { border-color: rgba(16, 185, 129, 0.3) !important; }

        .bg-amber-50, .bg-amber-100 { background-color: rgba(245, 158, 11, 0.2) !important; color: #fcd34d !important; }
        .text-amber-600, .text-amber-700, .text-amber-800 { color: #fbbf24 !important; }
        .border-amber-100, .border-amber-200 { border-color: rgba(245, 158, 11, 0.3) !important; }

        .bg-rose-50, .bg-rose-100 { background-color: rgba(244, 63, 94, 0.2) !important; color: #fda4af !important; }
        .text-rose-600, .text-rose-700, .text-rose-800 { color: #fb7185 !important; }
        .border-rose-100, .border-rose-200 { border-color: rgba(244, 63, 94, 0.3) !important; }
        
        .bg-purple-50, .bg-purple-100 { background-color: rgba(168, 85, 247, 0.2) !important; color: #d8b4fe !important; }
        .text-purple-600, .text-purple-700, .text-purple-800 { color: #c084fc !important; }

        /* Bug Fixes for hover states and cards */
        .bg-slate-100, .bg-slate-200 { background-color: #334155 !important; color: #e2e8f0 !important; }
        .hover\:bg-slate-50:hover, tr:hover { background-color: #334155 !important; }
        .card { background-color: #1e293b !important; border-color: #334155 !important; }
        
        /* Logo Fix */
        .sidebar img { background-color: transparent !important; filter: drop-shadow(0px 0px 2px rgba(255,255,255,0.5)) !important; }
        <?php endif; ?>
    </style>


    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <meta name="htmx-config" content='{"globalViewTransitions":true}'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="flex h-screen overflow-hidden text-slate-800">
    <?php include 'teacher_sidebar.php'; ?>
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8">
        <div class="flex items-center">
            <?php $sid = $_GET['sid'] ?? $attempt['user_id']; ?>
            <a href="student_submissions.php?user_id=<?php echo $sid; ?>" class="text-slate-400 hover:text-slate-600 mr-4"><i class="fas fa-arrow-left"></i></a>
            <i class="fas fa-search text-blue-500 text-2xl mr-3"></i>
            <h1 class="font-bold text-lg text-slate-800">Reviewing: <?php echo htmlspecialchars($attempt['fname'] . ' ' . $attempt['lname']); ?> (Grade <?php echo htmlspecialchars($attempt['passage_grade']); ?>)</h1>
        </div>
        <div>
            <button id="saveOverridesBtn" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded font-medium shadow transition">Save Official Scores</button>
        </div>
    </header>

    <main class="p-4 md:p-8 max-w-6xl mx-auto flex gap-6">
        <!-- Left Column: Audio and Stats -->
        <div class="w-1/3 flex flex-col gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-4 border-b pb-2">Audio Recording</h3>
                <?php if ($attempt['audio_path']): ?>
                    <audio src="<?php echo htmlspecialchars($attempt['audio_path']); ?>" controls class="w-full"></audio>
                <?php else: ?>
                    <p class="text-slate-500 text-sm">No audio recording available for this attempt.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-4 border-b pb-2">Performance Scores</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="bg-slate-50 p-3 rounded text-center">
                        <div class="text-xs text-slate-500 uppercase font-bold">Accuracy</div>
                        <h4 id="readingAccuracy" class="text-xl font-bold text-slate-800"><?php echo number_format($attempt['accuracy_score'], 1); ?>%</h4>
                    </div>
                    <div class="bg-slate-50 p-3 rounded text-center">
                        <div class="text-xs text-slate-500 uppercase font-bold">WCPM</div>
                        <h4 id="wpm" class="text-xl font-bold text-slate-800"><?php echo number_format($attempt['reading_speed'], 1); ?></h4>
                    </div>
                    <div class="bg-slate-50 p-3 rounded text-center">
                        <div class="text-xs text-slate-500 uppercase font-bold">Comprehension</div>
                        <h4 class="text-xl font-bold text-slate-800"><?php echo number_format($attempt['comprehension_score'], 1); ?>%</h4>
                    </div>
                    <div class="bg-slate-50 p-3 rounded text-center">
                        <div class="text-xs text-slate-500 uppercase font-bold">System Profile</div>
                        <h4 id="passageClassification" class="text-md font-bold text-slate-800 mt-1"><?php echo htmlspecialchars($attempt['oral_reading_profile']); ?></h4>
                    </div>
                </div>

                <div id="miscueStrip" class="d-flex justify-content-center gap-2 flex-wrap"></div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <p class="text-sm text-slate-600 leading-relaxed">
                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                    <strong>Teacher Override:</strong> Click on any word in the transcript on the right to manually change its grading. The Accuracy and WCPM scores will instantly recalculate. When finished, click <strong>Save Official Scores</strong>.
                </p>
            </div>
        </div>

        <!-- Right Column: The Transcript -->
        <div class="w-2/3">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 min-h-[600px]">
                <h3 class="font-bold text-slate-800 mb-4 border-b pb-2">Diagnostic Transcript</h3>
                <article id="transcript" class="story text-muted mt-4" style="font-size: 1.25rem; line-height:2.0;"></article>
                <div id="transcriptNotice" hidden></div>
            </div>
        </div>
    </main>

    <!-- Comprehension & Essay Grading -->
    <div class="max-w-6xl mx-auto px-4 md:px-8 pb-8">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 class="font-bold text-slate-800 mb-4 border-b pb-2 flex justify-between items-center">
                <span>Comprehension & Essay Grading</span>
                <span class="text-sm font-normal text-slate-500">Auto-calculated: <strong id="compAutoScore" class="text-blue-600"></strong></span>
            </h3>
            <div id="comprehensionList" class="space-y-6 mt-4">
                <!-- Javascript will render questions here -->
            </div>
        </div>
    </div>


    <!-- Hidden DOM Elements required by app.js rendering logic -->
    <div style="display:none;">
        <article id="story"></article>
        <div id="tooltip"></div>
        <button id="startBtn"></button><button id="stopBtn"></button><button id="resetBtn"></button><button id="editStoryBtn"></button><button id="restartBtn"></button><button id="diagBtn"></button><div id="diagModal"></div><button id="closeDiagBtn"></button><div id="diagContent"></div><select id="locale"></select>
        <span id="statusPill"></span><span id="hint"></span><h4 id="timer"></h4><div id="meterBar"></div><span id="vadDot"></span><span id="liveBadge"></span><h4 id="storyWordCount"></h4><span id="wordBadge"></span>
    </div>

    <!-- Override Modal -->
    <div id="overrideModal" class="fixed inset-0 bg-slate-900 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-96 overflow-hidden">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="font-bold text-lg text-slate-800">Override Word: <span id="overrideTargetWord" class="text-blue-600"></span></h3>
                <button id="closeOverrideBtn" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-4 flex flex-col gap-2">
                <input type="hidden" id="overrideWordIndex">
                <button class="override-btn border p-3 text-left rounded font-medium flex justify-between items-center" data-type="match">
                    <span>Correct (No Error)</span><i class="fas fa-check text-emerald-500"></i>
                </button>
                <button class="override-btn border p-3 text-left rounded font-medium flex justify-between items-center bg-rose-50 border-rose-200" data-type="mis">
                    <span>Mispronunciation</span><i class="fas fa-exclamation-triangle text-rose-500"></i>
                </button>
                <button class="override-btn border p-3 text-left rounded font-medium flex justify-between items-center bg-slate-100 border-slate-300" data-type="omit">
                    <span>Omission</span><i class="fas fa-minus-circle text-slate-500"></i>
                </button>
                <button class="override-btn border p-3 text-left rounded font-medium flex justify-between items-center bg-amber-50 border-amber-200" data-type="sub">
                    <span>Substitution</span><i class="fas fa-exchange-alt text-amber-500"></i>
                </button>
                <button class="override-btn border p-3 text-left rounded font-medium flex justify-between items-center bg-emerald-50 border-emerald-200" data-type="insert">
                    <span>Insertion</span><i class="fas fa-plus-circle text-emerald-500"></i>
                </button>
                <button class="override-btn border p-3 text-left rounded font-medium flex justify-between items-center bg-yellow-50 border-yellow-200" data-type="repeat">
                    <span>Repetition</span><i class="fas fa-redo text-yellow-500"></i>
                </button>
                <button class="override-btn border p-3 text-left rounded font-medium flex justify-between items-center bg-purple-50 border-purple-200" data-type="trans">
                    <span>Transposition</span><i class="fas fa-random text-purple-500"></i>
                </button>
                <button class="override-btn border p-3 text-left rounded font-medium flex justify-between items-center bg-pink-50 border-pink-200" data-type="reverse">
                    <span>Reversal</span><i class="fas fa-arrows-alt-h text-pink-500"></i>
                </button>
                <button class="override-btn border p-3 text-left rounded font-medium flex justify-between items-center bg-blue-50 border-blue-200" data-type="self-corrected">
                    <span>Self-Corrected</span><i class="fas fa-undo text-blue-500"></i>
                </button>
            </div>
        </div>
    </div>

    <script src="v536/public/assessment-core.js?v=2.0.12"></script>
    <script src="v536/public/app.js?v=2.0.15"></script>
    <script>
        window.reviewData = <?php echo $eval_data; ?>;
        const attemptId = <?php echo $attempt_id; ?>;
        let activeAssessment = window.reviewData?.assessment;
        
        
        const qJson = <?php echo $q_json; ?>;
        const aJson = JSON.parse(<?php echo json_encode($answers_json); ?> || '{}');
        
        function renderComprehensionReview() {
            const container = document.getElementById('comprehensionList');
            if (!qJson || qJson.length === 0) {
                container.innerHTML = '<div class="text-slate-500 text-sm italic">No comprehension questions found for this attempt.</div>';
                return;
            }
            
            let html = '';
            qJson.forEach((q, idx) => {
                const sAns = aJson[idx];
                const pts = q.type === 'essay' ? (parseInt(q.points) || 30) : 1;
                
                // For auto-graded types, just show the score (1 or 0)
                // For essays, allow teacher to input score
                
                let sAnsDisplay = sAns;
                if (sAns && typeof sAns === 'object' && !Array.isArray(sAns) && sAns.answer !== undefined) { sAnsDisplay = sAns.answer; sAns = sAns.answer; }
                if (Array.isArray(sAns)) sAnsDisplay = sAns.join(', ');
                else if (typeof sAns === 'boolean' || typeof sAns === 'string') sAnsDisplay = sAns;
                else sAnsDisplay = '<i>No answer provided</i>';
                
                html += `
                <div class="border rounded-lg p-4 bg-slate-50 relative q-review-block" data-idx="${idx}" data-type="${q.type}" data-max="${pts}">
                    <div class="font-bold text-slate-700 mb-2">${idx + 1}. ${q.question || q.text || ''}</div>
                    <div class="mb-3 text-sm text-slate-600">
                        <strong class="text-slate-800">Student Answer:</strong><br>
                        <div class="mt-1 p-2 bg-white border rounded min-h-[40px]">${sAnsDisplay}</div>
                    </div>
                `;
                
                if (q.type === 'essay') {
                    // Try to extract existing manual score if we stored it in answers_json previously, else default to 0
                    const currentScore = (sAns && typeof sAns === 'object' && sAns.manual_score !== undefined) ? sAns.manual_score : 0;
                    
                    html += `
                    <div class="flex items-center justify-end border-t pt-3 mt-3">
                        <span class="text-sm font-bold text-slate-700 mr-3">Manual Score:</span>
                        <input type="number" min="0" max="${pts}" class="manual-score-input border border-slate-300 rounded px-2 py-1 w-20 text-center outline-none focus:border-blue-500" value="${currentScore}" data-idx="${idx}">
                        <span class="text-sm text-slate-500 ml-2">/ ${pts} Points</span>
                    </div>
                    `;
                } else {
                    html += `<div class="text-xs text-slate-500 text-right mt-2">Auto-graded (Max ${pts} pt)</div>`;
                }
                
                html += `</div>`;
            });
            container.innerHTML = html;
            updateCompScorePreview();
            
            document.querySelectorAll('.manual-score-input').forEach(inp => {
                inp.addEventListener('input', updateCompScorePreview);
            });
        }
        
        function updateCompScorePreview() {
            let totalPoints = 0;
            let earnedPoints = 0;
            
            qJson.forEach((q, idx) => {
                const sAns = aJson[idx];
                if (q.type === 'essay') {
                    const pts = parseInt(q.points) || 30;
                    totalPoints += pts;
                    const inp = document.querySelector(`.manual-score-input[data-idx="${idx}"]`);
                    if (inp) earnedPoints += (parseInt(inp.value) || 0);
                } else {
                    totalPoints += 1; // Auto-graded usually 1 pt
                    // Estimate score based on actual comprehension score from DB minus essay portion
                    // Since it's complex, we'll recalculate exactly like cascading-flow.js did
                    earnedPoints += calculateAutoGrade(q, sAns);
                }
            });
            
            const pct = totalPoints > 0 ? (earnedPoints / totalPoints) * 100 : 100;
            document.getElementById('compAutoScore').innerText = pct.toFixed(1) + '%';
            return pct;
        }
        
        function calculateAutoGrade(q, sAns) {
            if (sAns === undefined || sAns === null) return 0;
            if (q.type === 'multichoice' || !q.type) {
                return parseInt(sAns) === parseInt(q.correct) ? 1 : 0;
            } else if (q.type === 'truefalse') {
                return String(sAns) === String(q.correct) ? 1 : 0;
            } else if (q.type === 'enumeration') {
                // Enumeration is dynamic point value!
                let matches = 0;
                let userAnswers = Array.isArray(sAns) ? sAns : [sAns];
                let correctAnswers = q.answers || [];
                let anyOrder = q.anyOrder !== undefined ? q.anyOrder : true;
                let caseSensitive = q.caseSensitive || false;
                
                if (anyOrder) {
                    let matchedIndexes = new Set();
                    userAnswers.forEach(uAns => {
                        if (!uAns) return;
                        let u = caseSensitive ? uAns : uAns.toString().toLowerCase();
                        for (let i = 0; i < correctAnswers.length; i++) {
                            if (matchedIndexes.has(i)) continue;
                            let c = caseSensitive ? correctAnswers[i].trim() : correctAnswers[i].trim().toLowerCase();
                            if (u === c && c !== "") { matches++; matchedIndexes.add(i); break; }
                        }
                    });
                } else {
                    for (let i = 0; i < correctAnswers.length; i++) {
                        if (i >= userAnswers.length) break;
                        let u = caseSensitive ? userAnswers[i] : userAnswers[i].toString().toLowerCase();
                        let c = caseSensitive ? correctAnswers[i].trim() : correctAnswers[i].trim().toLowerCase();
                        if (u === c && c !== "") matches++;
                    }
                }
                return matches; // Each enumeration item is 1 point!
            }
            return 0;
        }
        
        // Hook into Save Official Scores
        const originalSave = document.getElementById('saveOverridesBtn');
        if (originalSave) {
            originalSave.addEventListener('click', async (e) => {
                // Intercept logic! Wait, originalSave already has a click handler in app.js!
                // To intercept FormData in app.js, we can override window.fetch!
            });
        }

        renderComprehensionReview();

        if (activeAssessment && activeAssessment.ops) {
            // Restore missing reference properties expected by the UI
            els.story.textContent = activeAssessment.referenceWords.map(w => w.word).join(" ");
            
            // 1. Initial Render
            els.transcript.innerHTML = renderTranscriptMarkup(activeAssessment, window.reviewData);
            renderMiscueStrip(activeAssessment.counts);
            // We don't call renderPerformance() directly because it pulls from 'data' duration.
            // We just let the PHP values stay, UNTIL they override.
            
            // 2. Override Logic
            const modal = document.getElementById('overrideModal');
            const targetWordSpan = document.getElementById('overrideTargetWord');
            const hiddenIndex = document.getElementById('overrideWordIndex');
            
            document.getElementById('transcript').addEventListener('click', e => {
                const token = e.target.closest('.transcript-token');
                if (token) {
                    const tipIndex = token.getAttribute('data-tip-index');
                    if (tipIndex !== null) {
                        // The tipIndex maps to transcriptTips[tipIndex] from app.js, which has the op!
                        const info = transcriptTips[tipIndex];
                        if (info) {
                            targetWordSpan.textContent = info.spokenWord || info.expectedWord || "Word";
                            hiddenIndex.value = tipIndex;
                            modal.classList.remove('hidden');
                        }
                    }
                }
            });
            
            document.getElementById('closeOverrideBtn').addEventListener('click', () => {
                modal.classList.add('hidden');
            });
            
            document.querySelectorAll('.override-btn').forEach(btn => {
                btn.addEventListener('click', e => {
                    const newType = e.currentTarget.getAttribute('data-type');
                    const tipIndex = hiddenIndex.value;
                    const info = transcriptTips[tipIndex];
                    
                    if (info && info.op) {
                        const oldType = info.op.type;
                        
                        // Decrement old count
                        let oldCat = getCategory(oldType);
                        if (oldCat && activeAssessment.counts[oldCat] > 0) activeAssessment.counts[oldCat]--;
                        
                        // Increment new count
                        let newCat = getCategory(newType);
                        if (newCat) {
                            activeAssessment.counts[newCat] = (activeAssessment.counts[newCat] || 0) + 1;
                        }
                        
                        // Update the OP
                        info.op.type = newType;
                        
                        // Re-render
                        els.transcript.innerHTML = renderTranscriptMarkup(activeAssessment, window.reviewData);
                        renderMiscueStrip(activeAssessment.counts);
                        
                        // Recalculate Accuracy
                        const miscues = miscuesForAccuracy(activeAssessment.counts);
                        const words = activeAssessment.referenceWords.length;
                        const accuracy = words > 0 ? Math.max(0, ((words - miscues) / words) * 100) : 0;
                        document.getElementById('readingAccuracy').textContent = accuracy.toFixed(1) + '%';
                        
                        modal.classList.add('hidden');
                    }
                });
            });
            
            function getCategory(type) {
                if (type === 'mis') return 'mispronunciation';
                if (type === 'omit' || type === 'azure-mis-omit') return 'omission';
                if (type === 'sub') return 'substitution';
                if (type === 'self-corrected') return 'selfCorrection';
                if (type === 'insert') return 'insertion';
                return null;
            }
            
            document.getElementById('saveOverridesBtn').addEventListener('click', async (e) => {
                const btn = e.currentTarget;
                btn.textContent = 'Saving...';
                
                const miscues = miscuesForAccuracy(activeAssessment.counts);
                const words = activeAssessment.referenceWords.length;
                const accuracy = words > 0 ? Math.max(0, ((words - miscues) / words) * 100) : 0;
                
                const fd = new FormData();
                fd.append('attempt_id', attemptId);
                fd.append('accuracy_score', accuracy);
                fd.append('miscues_json', JSON.stringify(activeAssessment.counts));
                // Update eval_data with new ops and counts so it persists
                window.reviewData.assessment = activeAssessment;
                fd.append('evaluation_data', JSON.stringify(window.reviewData));
                
                // --- Inject Manual Comprehension Score ---
                if (typeof updateCompScorePreview === 'function') {
                    const manualComp = updateCompScorePreview();
                    fd.append('comp_score', manualComp);
                    
                    // We must also update answers_json in the DB to save the manual scores!
                    if (typeof aJson !== 'undefined' && typeof qJson !== 'undefined') {
                        document.querySelectorAll('.manual-score-input').forEach(inp => {
                            const idx = inp.getAttribute('data-idx');
                            if (!aJson[idx] || typeof aJson[idx] !== 'object') {
                                // Convert primitive answers to object if needed, or just append it
                                let oldAns = aJson[idx];
                                aJson[idx] = { answer: oldAns, manual_score: parseInt(inp.value) || 0 };
                            } else {
                                aJson[idx].manual_score = parseInt(inp.value) || 0;
                            }
                        });
                        fd.append('answers_json', JSON.stringify(aJson));
                    }
                }

                
                try {
                    const res = await fetch('api_assessment.php?action=override_attempt', {
                        method: 'POST',
                        body: fd
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.new_passage_classification) {
                            document.getElementById('passageClassification').textContent = data.new_passage_classification;
                        }
                        btn.textContent = 'Saved!';
                        btn.classList.replace('bg-emerald-500', 'bg-blue-500');
                        setTimeout(() => { btn.textContent = 'Save Official Scores'; btn.classList.replace('bg-blue-500', 'bg-emerald-500'); }, 2000);
                    }
                } catch(err) {
                    alert('Error saving overrides.');
                    btn.textContent = 'Save Official Scores';
                }
            });
        }
    </script>

        </div>
<script>
        window.toggleSidebar = function() {
            const sidebar = document.getElementById('appSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if(sidebar) sidebar.classList.toggle('-translate-x-full');
            if(overlay) overlay.classList.toggle('hidden');
        }
    </script>
</body>
</html>
