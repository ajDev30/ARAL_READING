<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM course_assessments WHERE id = ?");
$stmt->execute([$id]);
$test = $stmt->fetch();

if (!$test) {
    die("Test not found.");
}

// Fetch Azure Ephemeral Token
$azure_token = '';
$region = 'southeastasia'; // Usually from config
try {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "http://127.0.0.1:8000/token",
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $response = curl_exec($curl);
    if ($response) {
        $json = json_decode($response, true);
        if (isset($json['token'])) $azure_token = $json['token'];
    }
    curl_close($curl);
} catch(Exception $e) {}

$phase = $test['test_type'] === 'Pre-Test' ? 'Course-Pre-Test' : 'Course-Post-Test';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="https://tibungcodistrict.wordpress.com/wp-content/uploads/2018/06/untitled-1.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Take Test: <?php echo htmlspecialchars($test['title']); ?></title>
    <link rel="stylesheet" href="v536/public/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #F1F5F9; }
        .course-shell { max-width: 1200px; margin: 0 auto; background: white; border-radius: 12px; padding: 1rem; width: 100%; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
    
        @media (max-width: 768px) {
            .course-shell, .shell { padding: 1rem !important; margin: 0 !important; width: 100% !important; }
            .top-metrics { flex-direction: column; gap: 0.5rem !important; }
            .reader-toolbar { flex-wrap: wrap; gap: 0.5rem; justify-content: center !important; }
            .d-flex { flex-wrap: wrap; }
            .btn { width: 100%; margin-bottom: 0.5rem; }
            h3, h1 { font-size: 1.5rem !important; }
            .app-header { flex-direction: column; align-items: flex-start !important; }
            .app-header > div { margin-top: 10px; width: 100%; display: flex; flex-direction: column; }
        }
        
    .sidebar { background-color: #1a365d; }
    </style>

    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <meta name="htmx-config" content='{"globalViewTransitions":true}'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="p-4">

<div class="course-shell">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h3><?php echo htmlspecialchars($test['title']); ?> <span class="badge badge-info"><?php echo $test['test_type']; ?></span></h3>
        <a href="student_course.php" class="btn btn-outline-secondary">Back to Course</a>
    </div>

    <main class="shell" style="all: unset; display: block;">
        <header class="topbar" style="border-radius: 8px; margin-bottom: 1rem;">
            <div>
                <div class="eyebrow">Reading Assessment</div>
                <h1>Read aloud</h1>
            </div>
            <div class="top-actions">
                <span class="status-pill" id="statusPill">Ready</span>
            </div>
        </header>

        <section class="top-metrics">
            <div class="metric"><span>TIME</span><strong id="timer">00:00</strong></div>
            <div class="metric"><span>STORY WORDS</span><strong id="storyWordCount">0</strong></div>
        </section>

        <section class="reader-card">
            <div class="reader-toolbar">
                <div class="toolbar-status">
                    <span class="dot" id="vadDot"></span>
                    <span id="hint">Ready. Press Start and read the passage.</span>
                </div>
                <div class="mic-meter"><div id="meterBar"></div></div>
            </div>

            <textarea id="storyEditor" class="story-editor" hidden><?php echo htmlspecialchars($test['passage_text']); ?></textarea>

            <div class="reader-grid">
                <section class="reading-pane">
                    <div class="pane-heading">
                        <div><span class="pane-kicker">EXPECTED STORY</span></div>
                        <span class="word-count-badge" id="wordBadge">0 words</span>
                    </div>
                    <article id="story" class="story"><?php echo $test['passage_text']; ?></article>
                </section>

                <section class="reading-pane transcript-pane">
                    <div class="pane-heading">
                        <div><span class="pane-kicker">TRANSCRIPT</span></div>
                    </div>
                    <div id="transcript" class="transcript">
                        <span class="empty-state">Your spoken words will appear here...</span>
                    </div>
                </section>
            </div>

            <div class="action-buttons">
                <button id="startBtn" class="primary">▶ Start</button>
                <button id="stopBtn" class="secondary" disabled>⏹ Stop</button>
            </div>

            <div class="bottom-row">
                <div class="performance-metrics">
                    <div class="performance-card speed-card"><span>READING SPEED</span><strong id="wpm">—</strong></div>
                    <div class="performance-card accuracy-card"><span>READING ACCURACY</span><strong id="readingAccuracy">—</strong></div>
                </div>
                <div class="miscue-strip" id="miscueStrip"></div>
            </div>
        </section>

        <!-- COMPREHENSION TEST -->
        <div id="comprehension-section" class="card shadow-sm mt-4 d-none" style="border: 2px solid #0d6efd; border-radius: 8px;">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">📝 Comprehension Test</h4>
            </div>
            <div class="card-body" id="comprehension-questions">
                <!-- JS will inject questions here -->
            </div>
            <div class="card-footer text-right">
                <button id="submitTestBtn" class="btn btn-success btn-lg font-weight-bold shadow">Submit Test</button>
            </div>
        </div>

    </main>
</div>

<!-- Hidden dependencies for app.js -->
<div hidden>
    <select id="locale"><option value="en-US">English (US)</option></select>
    <button id="diagBtn"></button>
    <button id="restartBtn"></button>
    <button id="resetBtn"></button>
    <button id="editStoryBtn"></button>
    <div id="transcriptNotice"></div>
    <dialog id="diagModal"><button id="closeDiagBtn"></button><div id="diagContent"></div></dialog>
    <div id="tooltip"></div>
</div>

<script>
window.azureToken = "<?php echo $azure_token; ?>";
const courseQuestions = <?php echo $test['questions_json']; ?>;
let oralScore = 0;
let readingTime = 0;
let readingSpeed = 0;
let miscues = {};

// Handle Assessment Complete (From app.js)
window.addEventListener('assessmentComplete', (e) => {
    const data = e.detail;
    oralScore = data.readingAccuracy || 0;
    readingTime = data.durationSeconds || 0;
    readingSpeed = data.wpm || 0;
    miscues = data.miscuesJson || '{}';
    window.currentAssessment = data.evalData || null;
    
    // Show Comprehension Test
    document.getElementById('comprehension-section').classList.remove('d-none');
    renderComprehensionTest(courseQuestions);
});

function renderComprehensionTest(questions) {
    const container = document.getElementById('comprehension-questions');
    container.innerHTML = '';
    
    questions.forEach((q, idx) => {
        let html = `<div class="mb-4 p-3 bg-light rounded question-block" data-idx="${idx}">
            <h5>${idx+1}. ${q.question || q.text || ''}</h5>`;
        
        if (q.type === 'multichoice' || (!q.type && q.options)) {
            (q.options || []).forEach((opt, oIdx) => {
                html += `<div class="form-check">
                    <input class="form-check-input" type="radio" name="q_${idx}" value="${oIdx}" id="q_${idx}_${oIdx}">
                    <label class="form-check-label" for="q_${idx}_${oIdx}">${opt}</label>
                </div>`;
            });
        } else if (q.type === 'truefalse') {
            html += `<div class="form-check"><input class="form-check-input" type="radio" name="q_${idx}" value="true" id="q_${idx}_t"><label class="form-check-label" for="q_${idx}_t">True</label></div>
                     <div class="form-check"><input class="form-check-input" type="radio" name="q_${idx}" value="false" id="q_${idx}_f"><label class="form-check-label" for="q_${idx}_f">False</label></div>`;
        } else if (q.type === 'enumeration') {
            const count = (q.answers && q.answers.length > 0) ? q.answers.length : (parseInt(q.count) || 3);
            for(let c=0; c<count; c++) html += `<input type="text" class="form-control mb-2 enum-input" placeholder="Item ${c+1}">`;
        } else if (q.type === 'essay') {
            html += `<textarea class="form-control essay-input" rows="3" placeholder="Type your answer..."></textarea>`;
        }
        
        html += `</div>`;
        container.innerHTML += html;
    });
}

document.getElementById('submitTestBtn').addEventListener('click', async () => {
    let compCorrect = 0;
    let compTotal = 0;
    let studentAnswers = {};
    const blocks = document.querySelectorAll('.question-block');
    
    blocks.forEach(block => {
        const idx = block.getAttribute('data-idx');
        const q = courseQuestions[idx];
        
        if (q.type === 'multichoice' || !q.type) {
            compTotal++;
            const selected = document.querySelector(`input[name="q_${idx}"]:checked`);
            if (selected) { studentAnswers[idx] = selected.value; if (parseInt(selected.value) === parseInt(q.correct)) compCorrect++; }
        } else if (q.type === 'truefalse') {
            compTotal++;
            const selected = document.querySelector(`input[name="q_${idx}"]:checked`);
            if (selected) { studentAnswers[idx] = selected.value; if (selected.value === String(q.correct)) compCorrect++; }
        } else if (q.type === 'enumeration') {
            const inputs = document.querySelectorAll(`.question-block[data-idx="${idx}"] .enum-input`);
            let userAnswers = Array.from(inputs).map(inp => inp.value.trim());
            studentAnswers[idx] = userAnswers;
            let correctAnswers = q.answers || [];
            let expectedCount = correctAnswers.length > 0 ? correctAnswers.length : (parseInt(q.count) || 1);
            compTotal += expectedCount;
            
            let matches = 0;
            let anyOrder = q.anyOrder !== undefined ? q.anyOrder : true;
            let caseSensitive = q.caseSensitive || false;
            
            if (anyOrder) {
                let matchedIndexes = new Set();
                userAnswers.forEach(uAns => {
                    if (!uAns) return;
                    let u = caseSensitive ? uAns : uAns.toLowerCase();
                    for (let i = 0; i < correctAnswers.length; i++) {
                        if (matchedIndexes.has(i)) continue;
                        let c = caseSensitive ? correctAnswers[i].trim() : correctAnswers[i].trim().toLowerCase();
                        if (u === c && c !== "") {
                            matches++;
                            matchedIndexes.add(i);
                            break;
                        }
                    }
                });
            } else {
                for (let i = 0; i < correctAnswers.length; i++) {
                    if (i >= userAnswers.length) break;
                    let u = caseSensitive ? userAnswers[i] : userAnswers[i].toLowerCase();
                    let c = caseSensitive ? correctAnswers[i].trim() : correctAnswers[i].trim().toLowerCase();
                    if (u === c && c !== "") {
                        matches++;
                    }
                }
            }
            compCorrect += matches;
        }
        } else if (q.type === 'essay') {
            const textarea = document.querySelector(`.question-block[data-idx="${idx}"] .essay-input`);
            if (textarea) studentAnswers[idx] = textarea.value.trim();
            let pts = parseInt(q.points) || 30;
            compTotal += pts;
        }
    });
    
    const compScore = compTotal > 0 ? (compCorrect / compTotal) * 100 : 100;

    
    // Determine class
    let classification = "Instructional";
    if (oralScore >= 97 && compScore >= 80) classification = "Independent";
    else if (oralScore <= 89 || compScore <= 58) classification = "Frustration";

    const fd = new FormData();
    fd.append('passage_id', '<?php echo $test['id']; ?>');
    fd.append('phase', '<?php echo $phase; ?>');
    fd.append('accuracy_score', oralScore);
    fd.append('comprehension_score', compScore);
    fd.append('oral_reading_profile', classification);
    fd.append('reading_time', readingTime);
    fd.append('reading_speed', readingSpeed);
    fd.append('miscues_json', typeof miscues === 'string' ? miscues : JSON.stringify(miscues));
    fd.append('answers_json', JSON.stringify(studentAnswers));
    fd.append('evaluation_data', JSON.stringify(window.currentAssessment || {}));
    
    if (window.lastRecordingBlob) {
        fd.append('audio_file', window.lastRecordingBlob, 'course.webm');
    }

    // Submit to API
    const res = await fetch('api_assessment.php?action=submit_course_attempt', { method: 'POST', body: fd });
    if (res.ok) {
        window.location.href = 'student_course.php?success=1';
    } else {
        alert("Failed to submit.");
    }
});
</script>

<script src="v536/public/assessment-core.js"></script>
<script src="v536/public/app.js"></script>


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
