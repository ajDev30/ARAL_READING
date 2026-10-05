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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Assessment - Teacher Dashboard</title>
    <!-- Use Tailwind for main layout, but keep Bootstrap for app.js DOM compatibility -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="v536/public/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .override-btn:hover { background-color: #f1f5f9; cursor: pointer; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8">
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

    <main class="p-8 max-w-6xl mx-auto flex gap-6">
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
                
                <div class="grid grid-cols-2 gap-4 mb-4">
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
                        <h4 class="text-md font-bold text-slate-800 mt-1"><?php echo htmlspecialchars($attempt['oral_reading_profile']); ?></h4>
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
                <button class="override-btn border p-3 text-left rounded font-medium flex justify-between items-center bg-blue-50 border-blue-200" data-type="self-corrected">
                    <span>Self-Corrected</span><i class="fas fa-undo text-blue-500"></i>
                </button>
            </div>
        </div>
    </div>

    <script src="v536/public/assessment-core.js?v=2.0.12"></script>
    <script src="v536/public/app.js?v=2.0.12"></script>
    <script>
        window.reviewData = <?php echo $eval_data; ?>;
        const attemptId = <?php echo $attempt_id; ?>;
        let activeAssessment = window.reviewData?.assessment;
        
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
                
                try {
                    const res = await fetch('api_assessment.php?action=override_attempt', {
                        method: 'POST',
                        body: fd
                    });
                    if (res.ok) {
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
</body>
</html>
