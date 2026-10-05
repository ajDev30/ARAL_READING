<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle Delete
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM course_assessments WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: course.php?success=deleted");
    exit;
}

// Handle Form Submission (Save/Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_course'])) {
    $title = $_POST['title'] ?? 'Untitled Course Test';
    $test_type = $_POST['test_type'] ?? 'Pre-Test';
    $target_profile = $_POST['target_profile'] ?? 'Instructional';
    $status = $_POST['status'] ?? 'Draft';
    $available_from = !empty($_POST['available_from']) ? $_POST['available_from'] : null;
    $passage_text = $_POST['passage_text'] ?? '';
    $questions_json = $_POST['questions_json'] ?? '[]';

    if ($id) {
        $stmt = $pdo->prepare("UPDATE course_assessments SET title=?, test_type=?, target_profile=?, status=?, available_from=?, passage_text=?, questions_json=? WHERE id=?");
        $stmt->execute([$title, $test_type, $target_profile, $status, $available_from, $passage_text, $questions_json, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO course_assessments (title, test_type, target_profile, status, available_from, passage_text, questions_json) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $test_type, $target_profile, $status, $available_from, $passage_text, $questions_json]);
        $id = $pdo->lastInsertId();
    }
    
    header("Location: course.php?success=saved");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Course Assessments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { background-color: #F8FAFC; }</style>
</head>
<body class="flex flex-col h-screen">
    
    <?php if ($action === 'list'): 
        $stmt = $pdo->query("SELECT * FROM course_assessments ORDER BY created_at DESC");
        $assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <header class="h-16 bg-white border-b border-slate-200 flex items-center px-8 shrink-0 justify-between">
        <div class="flex items-center">
            <a href="dashboard_teacher.php" class="text-slate-400 hover:text-slate-600 mr-4"><i class="fas fa-arrow-left"></i></a>
            <i class="fas fa-chalkboard-teacher text-blue-600 text-2xl mr-3"></i>
            <h1 class="font-bold text-lg text-slate-800">Course Assessments (Intervention)</h1>
        </div>
        <a href="course.php?action=edit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow text-sm">
            <i class="fas fa-plus mr-2"></i> Create New Test
        </a>
    </header>

    <main class="flex-1 p-8 max-w-6xl mx-auto w-full overflow-y-auto">
        <?php if(isset($_GET['success'])): ?>
            <div class="bg-emerald-50 text-emerald-700 p-4 rounded border border-emerald-200 mb-6 font-bold shadow-sm flex items-center">
                <i class="fas fa-check-circle mr-2"></i> Action completed successfully!
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-bold text-slate-800 border-b pb-3 mb-4">Existing Course Tests</h2>
            
            <?php if(count($assessments) === 0): ?>
                <div class="text-center py-10 text-slate-400 border-2 border-dashed border-slate-200 rounded-lg">
                    <i class="fas fa-folder-open text-4xl mb-3"></i>
                    <p>No course tests found. Click "Create New Test" to get started.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-sm uppercase tracking-wider">
                                <th class="p-4 border-b font-semibold">Title</th>
                                <th class="p-4 border-b font-semibold">Type</th>
                                <th class="p-4 border-b font-semibold">Target Profile</th>
                                <th class="p-4 border-b font-semibold">Status</th>
                                <th class="p-4 border-b font-semibold">Available From</th>
                                <th class="p-4 border-b font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-700 text-sm">
                            <?php foreach($assessments as $row): ?>
                            <tr class="border-b hover:bg-slate-50 transition">
                                <td class="p-4 font-bold"><?php echo htmlspecialchars($row['title']); ?></td>
                                <td class="p-4"><span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold uppercase"><?php echo $row['test_type']; ?></span></td>
                                <td class="p-4"><span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs font-bold uppercase"><?php echo $row['target_profile']; ?></span></td>
                                <td class="p-4">
                                    <?php if($row['status'] == 'Published'): ?>
                                        <span class="text-emerald-600 font-bold"><i class="fas fa-globe mr-1"></i> Published</span>
                                    <?php else: ?>
                                        <span class="text-slate-400 font-bold"><i class="fas fa-edit mr-1"></i> Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-slate-500"><?php echo $row['available_from'] ? date('M d, Y h:i A', strtotime($row['available_from'])) : 'Anytime'; ?></td>
                                <td class="p-4 text-right">
                                    <a href="course.php?action=edit&id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700 p-2"><i class="fas fa-edit"></i></a>
                                    <a href="course.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this test?')" class="text-red-400 hover:text-red-600 p-2"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php elseif ($action === 'edit'): 
        $record = null;
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM course_assessments WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $title = $record['title'] ?? '';
        $test_type = $record['test_type'] ?? 'Pre-Test';
        $target_profile = $record['target_profile'] ?? 'Instructional';
        $status = $record['status'] ?? 'Draft';
        $available_from = $record['available_from'] ? date('Y-m-d\TH:i', strtotime($record['available_from'])) : '';
        $passage_text = $record['passage_text'] ?? '';
        $questions_json = $record['questions_json'] ?? '[]';
    ?>
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0">
        <div class="flex items-center">
            <a href="course.php" class="text-slate-400 hover:text-slate-600 mr-4"><i class="fas fa-arrow-left"></i></a>
            <i class="fas fa-edit text-blue-600 text-2xl mr-3"></i>
            <h1 class="font-bold text-lg text-slate-800"><?php echo $id ? 'Edit Course Test' : 'Create Course Test'; ?></h1>
        </div>
        <div>
            <button type="submit" form="courseForm" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded shadow transition flex items-center">
                <i class="fas fa-save mr-2"></i> Save Test
            </button>
        </div>
    </header>

    <main class="flex-1 p-8 max-w-4xl mx-auto w-full overflow-y-auto">
        <form method="POST" id="courseForm" onsubmit="syncJSON()">
            <input type="hidden" name="save_course" value="1">
            <input type="hidden" name="questions_json" id="questions_json" value="<?php echo htmlspecialchars($questions_json); ?>">

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
                <h3 class="font-bold text-lg text-slate-800 border-b border-slate-100 pb-3 mb-4">Test Settings</h3>
                
                <div class="mb-4">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Test Title</label>
                    <input type="text" name="title" required value="<?php echo htmlspecialchars($title); ?>" class="w-full border-slate-300 rounded text-sm p-3 bg-slate-50 focus:bg-white border focus:ring-1 focus:ring-blue-500 outline-none" placeholder="e.g., Week 1 Post-Test for Instructional Readers">
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Test Type</label>
                        <select name="test_type" class="w-full border-slate-300 rounded text-sm p-2 bg-slate-50 border focus:ring-1 outline-none">
                            <option value="Pre-Test" <?php if($test_type == 'Pre-Test') echo 'selected'; ?>>Pre-Test</option>
                            <option value="Post-Test" <?php if($test_type == 'Post-Test') echo 'selected'; ?>>Post-Test</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Target Profile</label>
                        <select name="target_profile" class="w-full border-slate-300 rounded text-sm p-2 bg-slate-50 border focus:ring-1 outline-none">
                            <option value="Instructional" <?php if($target_profile == 'Instructional') echo 'selected'; ?>>Instructional Readers</option>
                            <option value="Frustration" <?php if($target_profile == 'Frustration') echo 'selected'; ?>>Frustration Readers</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Status</label>
                        <select name="status" class="w-full border-slate-300 rounded text-sm p-2 bg-slate-50 border focus:ring-1 outline-none">
                            <option value="Draft" <?php if($status == 'Draft') echo 'selected'; ?>>Draft (Hidden)</option>
                            <option value="Published" <?php if($status == 'Published') echo 'selected'; ?>>Published (Display)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Available From</label>
                        <input type="datetime-local" name="available_from" value="<?php echo $available_from; ?>" class="w-full border-slate-300 rounded text-sm p-2 bg-slate-50 border focus:ring-1 outline-none">
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-3"><i class="fas fa-info-circle mr-1"></i> "Available From" restricts when students can see this test. Leave blank for immediate availability.</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
                <div class="flex items-center border-b border-slate-100 pb-3 mb-4">
                    <i class="fas fa-book text-blue-500 mr-2"></i>
                    <h3 class="font-bold text-lg text-slate-800">Reading Passage</h3>
                </div>
                <div class="mb-2">
                    <textarea class="w-full border-slate-300 rounded text-base p-4 bg-slate-50 focus:bg-white border focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition outline-none" name="passage_text" rows="6" required placeholder="Paste the reading passage text here..."><?php echo htmlspecialchars($passage_text); ?></textarea>
                </div>
                <p class="text-slate-500 text-sm"><i class="fas fa-microphone mr-1"></i> Students will read this text aloud, and the system will auto-compute WCPM, accuracy, and miscues.</p>
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
    <?php endif; ?>
</body>
</html>
