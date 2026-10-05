<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

$grade = $_GET['grade'] ?? null;
$action = $_POST['action'] ?? null;

if (!$grade) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Manage English Passages</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>body { background-color: #F8FAFC; }</style>
    </head>
    <body class="flex flex-col h-screen">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center px-8 shrink-0">
            <a href="dashboard_teacher.php" class="text-slate-400 hover:text-slate-600 mr-4"><i class="fas fa-arrow-left"></i></a>
            <i class="fas fa-book-reader text-blue-600 text-2xl mr-3"></i>
            <h1 class="font-bold text-lg text-slate-800">Manage Reading Passages</h1>
        </header>
        <main class="flex-1 p-8 max-w-5xl mx-auto w-full overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Select Grade Level to Edit</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach(['Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9','Grade 10'] as $g): ?>
                    <a href="manage_preassessment.php?grade=<?php echo urlencode($g); ?>" class="bg-white border border-slate-200 rounded-xl p-8 text-center hover:border-blue-500 hover:shadow-md transition">
                        <div class="text-5xl text-blue-500 mb-4"><i class="fas fa-book-open"></i></div>
                        <h3 class="text-xl font-bold text-slate-700"><?php echo $g; ?></h3>
                        <p class="text-slate-500 text-sm mt-2">Manage the English Reading Passage and Comprehension Questions.</p>
                    </a>
                <?php endforeach; ?>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM reading_passages WHERE grade_level = ?");
$stmt->execute([$grade]);
$record = $stmt->fetch(PDO::FETCH_OBJ);

if (!$record) {
    $pdo->prepare("INSERT INTO reading_passages (title, grade_level, passage_text, questions_json) VALUES (?, ?, '', '[]')")
        ->execute(["$grade Assessment", $grade]);
    $stmt->execute([$grade]);
    $record = $stmt->fetch(PDO::FETCH_OBJ);
}

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $passage = $_POST['passage_text'] ?? '';
    $questions_json = $_POST['questions_json'] ?? '[]';
    
    $update = $pdo->prepare("UPDATE reading_passages SET passage_text=?, questions_json=? WHERE id=?");
    $update->execute([$passage, $questions_json, $record->id]);
    
    header("Location: manage_preassessment.php?grade=" . urlencode($grade) . "&success=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit <?php echo htmlspecialchars($grade); ?> Passage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #F8FAFC; }
    </style>
</head>
<body class="flex flex-col h-screen">

    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0">
        <div class="flex items-center">
            <a href="manage_preassessment.php" class="text-slate-400 hover:text-slate-600 mr-4"><i class="fas fa-arrow-left"></i></a>
            <i class="fas fa-book-reader text-blue-600 text-2xl mr-3"></i>
            <h1 class="font-bold text-lg text-slate-800">Edit <?php echo htmlspecialchars($grade); ?> Passage</h1>
        </div>
        <div>
            <button type="submit" form="editForm" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded shadow transition flex items-center">
                <i class="fas fa-save mr-2"></i> Save Content
            </button>
        </div>
    </header>

    <main class="flex-1 p-8 max-w-4xl mx-auto w-full overflow-y-auto">
        <?php if(isset($_GET['success'])): ?>
            <div class="bg-emerald-50 text-emerald-700 p-4 rounded border border-emerald-200 mb-6 font-bold shadow-sm flex items-center">
                <i class="fas fa-check-circle mr-2"></i> Passage and questions saved successfully!
            </div>
        <?php endif; ?>

        <form method="POST" id="editForm" onsubmit="syncJSON()">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="questions_json" id="questions_json" value="<?php echo htmlspecialchars($record->questions_json); ?>">

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
                <div class="flex items-center border-b border-slate-100 pb-3 mb-4">
                    <i class="fas fa-book text-blue-500 mr-2"></i>
                    <h3 class="font-bold text-lg text-slate-800">Grade-Level Reading Passage (English)</h3>
                </div>
                <div class="mb-2">
                    <textarea class="w-full border-slate-300 rounded text-base p-4 bg-slate-50 focus:bg-white border focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition outline-none" name="passage_text" rows="8" required placeholder="Paste the English reading passage text here..."><?php echo htmlspecialchars($record->passage_text); ?></textarea>
                </div>
                <p class="text-slate-500 text-sm"><i class="fas fa-info-circle mr-1"></i> This single passage will be tested dynamically. The system will classify the student's performance as Independent, Instructional, or Frustration automatically.</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-10">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-clipboard-list text-blue-500 mr-2"></i>
                        <h3 class="font-bold text-lg text-slate-800">Comprehension Questions</h3>
                    </div>
                    <div class="flex space-x-2">
                        <select id="ra-q-type-add" class="border border-slate-300 rounded px-3 py-1 text-sm bg-white font-medium outline-none focus:border-blue-500">
                            <option value="description">ℹ️ Instruction / Text</option>
                            <option value="multichoice">🔘 Multiple Choice</option>
                            <option value="truefalse">✅ True or False</option>
                            <option value="enumeration">🔢 Enumeration</option>
                            <option value="essay">📝 Essay</option>
                        </select>
                        <button type="button" onclick="addQ()" class="bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold py-1 px-3 rounded border border-blue-200 text-sm transition shadow-sm">
                            <i class="fas fa-plus mr-1"></i> Add Question
                        </button>
                    </div>
                </div>
                
                <div id="ra-q-list" class="space-y-4"></div>
            </div>
        </form>
    </main>

<script>
let questions = [];
try { questions = JSON.parse(document.getElementById('questions_json').value) || []; } catch(e) { questions = []; }

function escapeHtml(str) {
    if (!str) return "";
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function syncJSON() {
    document.getElementById('questions_json').value = JSON.stringify(questions);
}

function renderQ() {
    const list = document.getElementById('ra-q-list');
    list.innerHTML = '';
    
    if (questions.length === 0) {
        list.innerHTML = '<div class="text-slate-400 py-8 text-center border-2 border-dashed border-slate-200 rounded-lg bg-slate-50">No comprehension questions added yet.</div>';
    }

    questions.forEach((q, idx) => {
        const card = document.createElement('div');
        card.className = "bg-white border border-slate-200 rounded-lg p-5 relative shadow-sm hover:shadow-md transition";
        let typeName = "", typeColor = "", contentHtml = "";

        if (q.type === 'multichoice') {
            typeName = "Multiple Choice"; typeColor = "bg-blue-500";
            const opts = q.options || ["", ""];
            contentHtml = `
                <div class="mb-3">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Question:</label>
                    <textarea class="w-full border-slate-300 rounded text-sm p-2 bg-slate-50 focus:bg-white border focus:ring-1 focus:ring-blue-500 outline-none" rows="2" oninput="updateQProp(${idx}, 'question', this.value)">${escapeHtml(q.question || "")}</textarea>
                </div>
                <div class="mb-2 text-sm font-bold text-slate-700">Choices: <span class="font-normal text-slate-500">(Select the radio button to mark correct answer)</span></div>
                <div class="space-y-2 mb-3">
                    ${opts.map((opt, oidx) => `
                        <div class="flex items-center space-x-2">
                            <input type="radio" class="w-4 h-4 text-blue-600 cursor-pointer" name="correct_${idx}" ${parseInt(q.correct) === oidx ? "checked" : ""} onchange="updateQProp(${idx}, 'correct', ${oidx})">
                            <input type="text" class="flex-1 border border-slate-300 rounded text-sm p-2 outline-none focus:border-blue-500" value="${escapeHtml(opt)}" oninput="updateQOption(${idx}, ${oidx}, this.value)">
                            <button type="button" class="text-red-400 hover:text-red-600 px-2" onclick="removeQOption(${idx}, ${oidx})"><i class="fas fa-times"></i></button>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="text-blue-600 text-sm font-bold hover:underline" onclick="addQOption(${idx})"><i class="fas fa-plus text-xs mr-1"></i> Add Choice</button>
            `;
        } else if (q.type === 'truefalse') {
            typeName = "True/False"; typeColor = "bg-emerald-500";
            contentHtml = `
                <div class="mb-3">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Statement:</label>
                    <textarea class="w-full border-slate-300 rounded text-sm p-2 bg-slate-50 focus:bg-white border focus:ring-1 focus:ring-blue-500 outline-none" rows="2" oninput="updateQProp(${idx}, 'question', this.value)">${escapeHtml(q.question || "")}</textarea>
                </div>
                <div class="flex items-center text-sm font-bold text-slate-700">
                    Correct Answer: 
                    <select class="ml-3 border border-slate-300 rounded px-2 py-1 outline-none focus:border-blue-500" onchange="updateQProp(${idx}, 'correct', this.value === 'true')">
                        <option value="true" ${q.correct === true ? "selected" : ""}>True</option>
                        <option value="false" ${q.correct === false ? "selected" : ""}>False</option>
                    </select>
                </div>
            `;
        } else if (q.type === 'enumeration') {
            typeName = "Enumeration"; typeColor = "bg-purple-500";
            contentHtml = `
                <div class="mb-3">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Instruction / Prompt:</label>
                    <textarea class="w-full border-slate-300 rounded text-sm p-2 bg-slate-50 focus:bg-white border focus:ring-1 focus:ring-blue-500 outline-none" rows="2" oninput="updateQProp(${idx}, 'question', this.value)">${escapeHtml(q.question || "")}</textarea>
                </div>
                <div class="flex items-center text-sm font-bold text-slate-700">
                    Expected Number of Items:
                    <input type="number" min="1" max="10" class="ml-3 w-20 border border-slate-300 rounded px-2 py-1 outline-none text-center focus:border-blue-500" onchange="updateQProp(${idx}, 'count', this.value)" value="${q.count || 3}">
                </div>
            `;
        } else if (q.type === 'essay') {
            typeName = "Essay"; typeColor = "bg-pink-500";
            contentHtml = `
                <div class="mb-1">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Essay Prompt:</label>
                    <textarea class="w-full border-slate-300 rounded text-sm p-2 bg-slate-50 focus:bg-white border focus:ring-1 focus:ring-blue-500 outline-none" rows="3" oninput="updateQProp(${idx}, 'question', this.value)">${escapeHtml(q.question || "")}</textarea>
                </div>
            `;
        } else if (q.type === 'description') {
            typeName = "Instruction"; typeColor = "bg-slate-500";
            contentHtml = `
                <div class="mb-1">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Instruction Text:</label>
                    <textarea class="w-full border-slate-300 rounded text-sm p-2 bg-slate-50 focus:bg-white border focus:ring-1 focus:ring-blue-500 outline-none" rows="2" oninput="updateQProp(${idx}, 'question', this.value)">${escapeHtml(q.question || "")}</textarea>
                </div>
            `;
        }

        card.innerHTML = `
            <div class="flex justify-between items-center mb-3 pb-3 border-b border-slate-100">
                <div class="flex items-center">
                    <span class="font-bold text-slate-400 mr-3 text-lg">#${idx + 1}</span>
                    <span class="${typeColor} text-white text-xs font-bold px-2 py-1 rounded-full uppercase tracking-wide">${typeName}</span>
                </div>
                <div class="flex space-x-1">
                    <button type="button" class="text-slate-400 hover:text-slate-600 p-1" onclick="moveQ(${idx}, -1)" title="Move Up"><i class="fas fa-arrow-up"></i></button>
                    <button type="button" class="text-slate-400 hover:text-slate-600 p-1" onclick="moveQ(${idx}, 1)" title="Move Down"><i class="fas fa-arrow-down"></i></button>
                    <button type="button" class="text-red-400 hover:text-red-600 p-1 ml-2" onclick="deleteQ(${idx})" title="Delete"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>
            ${contentHtml}
        `;
        list.appendChild(card);
    });
    syncJSON();
}

window.addQ = function() {
    const type = document.getElementById('ra-q-type-add').value;
    let newQ = { type: type, question: "" };
    if (type === 'multichoice') { newQ.options = ["Option 1", "Option 2"]; newQ.correct = 0; } 
    else if (type === 'truefalse') { newQ.correct = true; }
    questions.push(newQ);
    renderQ();
};

window.deleteQ = function(idx) { if (confirm("Delete this question?")) { questions.splice(idx, 1); renderQ(); } };
window.moveQ = function(idx, dir) { const target = idx + dir; if (target >= 0 && target < questions.length) { const temp = questions[idx]; questions[idx] = questions[target]; questions[target] = temp; renderQ(); } };
window.updateQProp = function(idx, prop, val) { questions[idx][prop] = val; syncJSON(); };
window.addQOption = function(idx) { if (!questions[idx].options) questions[idx].options = []; questions[idx].options.push(""); renderQ(); };
window.removeQOption = function(idx, oidx) { questions[idx].options.splice(oidx, 1); if (questions[idx].correct >= questions[idx].options.length) questions[idx].correct = Math.max(0, questions[idx].options.length - 1); renderQ(); };
window.updateQOption = function(idx, oidx, val) { questions[idx].options[oidx] = val; syncJSON(); };

// Initial render
renderQ();
</script>
</body>
</html>
