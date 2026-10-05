<?php
session_start();
require_once 'config.php';

// RBAC Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

$grade = $_GET['grade'] ?? null;
$action = $_POST['action'] ?? null;

if (!$grade) {
    // Grade Selection Screen
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Manage GST</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>body { background-color: #F8FAFC; }</style>
    </head>
    <body class="flex flex-col h-screen">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center px-8">
            <a href="dashboard_teacher.php" class="text-slate-400 hover:text-slate-600 mr-4"><i class="fas fa-arrow-left"></i></a>
            <i class="fas fa-file-alt text-blue-600 text-2xl mr-3"></i>
            <h1 class="font-bold text-lg text-slate-800">Manage English GST Content</h1>
        </header>
        <main class="p-8 max-w-4xl mx-auto w-full">
            <h2 class="text-2xl font-bold text-slate-800 mb-6">Select Grade Level to Edit GST</h2>
            <div class="grid grid-cols-2 gap-4">
                <?php foreach(['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'] as $g): ?>
                    <a href="manage_gst.php?grade=<?php echo urlencode($g); ?>" class="bg-white border border-slate-200 rounded-xl p-6 text-center hover:border-blue-500 hover:shadow-md transition">
                        <div class="text-4xl text-blue-500 mb-3"><i class="fas fa-users"></i></div>
                        <h3 class="text-xl font-bold text-slate-700"><?php echo $g; ?> GST</h3>
                        <p class="text-slate-500 text-sm mt-2">Manage Group Screening Test questions and timer.</p>
                    </a>
                <?php endforeach; ?>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}

// Ensure record exists
$stmt = $pdo->prepare("SELECT * FROM gst_assessments WHERE grade_level = ?");
$stmt->execute([$grade]);
$record = $stmt->fetch(PDO::FETCH_OBJ);

if (!$record) {
    $pdo->prepare("INSERT INTO gst_assessments (grade_level, time_limit_minutes, questions_json) VALUES (?, 20, '[]')")
        ->execute([$grade]);
    $stmt->execute([$grade]);
    $record = $stmt->fetch(PDO::FETCH_OBJ);
}

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $time_limit = (int)$_POST['time_limit_minutes'];
    $questions_json = $_POST['questions_json'] ?? '[]';
    
    $update = $pdo->prepare("UPDATE gst_assessments SET time_limit_minutes=?, questions_json=? WHERE id=?");
    $update->execute([$time_limit, $questions_json, $record->id]);
    
    header("Location: manage_gst.php?grade=" . urlencode($grade) . "&success=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit <?php echo htmlspecialchars($grade); ?> GST</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #F8FAFC; }
        .builder-card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px; background: #fff; position: relative; }
        .builder-card .delete-btn { position: absolute; top: 12px; right: 12px; color: #ef4444; cursor: pointer; }
    </style>
</head>
<body class="flex flex-col h-screen">

    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0">
        <div class="flex items-center">
            <a href="manage_gst.php" class="text-slate-400 hover:text-slate-600 mr-4"><i class="fas fa-arrow-left"></i></a>
            <i class="fas fa-file-alt text-blue-600 text-2xl mr-3"></i>
            <h1 class="font-bold text-lg text-slate-800">Edit <?php echo htmlspecialchars($grade); ?> GST</h1>
        </div>
        <div>
            <button type="submit" form="gstForm" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded shadow"><i class="fas fa-save mr-2"></i> Save GST Content</button>
        </div>
    </header>

    <main class="flex-1 p-8 max-w-4xl mx-auto w-full overflow-y-auto">
        <?php if(isset($_GET['success'])): ?>
            <div class="bg-emerald-50 text-emerald-700 p-4 rounded border border-emerald-200 mb-6 font-bold shadow-sm">GST updated successfully!</div>
        <?php endif; ?>

        <form method="POST" id="gstForm" onsubmit="return serializeBuilder()">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="questions_json" id="questions_json" value="<?php echo htmlspecialchars($record->questions_json); ?>">

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
                <h3 class="font-bold text-lg text-slate-800 border-b pb-3 mb-4">Assessment Settings</h3>
                <div class="w-1/2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Time Limit (Minutes)</label>
                    <div class="flex items-center">
                        <input type="number" name="time_limit_minutes" value="<?php echo $record->time_limit_minutes; ?>" class="w-24 px-4 py-2 border border-slate-300 rounded focus:ring-blue-500 focus:border-blue-500 text-center">
                        <span class="ml-3 text-slate-500 text-sm">Enter 0 for no time limit.</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-10">
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h3 class="font-bold text-lg text-slate-800">GST Multiple Choice Questions</h3>
                    <button type="button" onclick="addMultipleChoice()" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-semibold rounded"><i class="fas fa-plus"></i> Add Question</button>
                </div>
                
                <div id="builderContainer" class="space-y-4">
                    <div class="text-center text-slate-400 py-6 border-2 border-dashed rounded bg-slate-50" id="emptyBuilderMsg">
                        No questions added yet.
                    </div>
                </div>
            </div>
        </form>
    </main>

    <!-- Template -->
    <template id="tpl-multiple_choice">
        <div class="builder-card border-l-4 border-l-blue-500" data-type="multichoice">
            <i class="fas fa-times delete-btn" onclick="this.parentElement.remove(); checkEmpty();"></i>
            <h4 class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-2">Question <span class="q-number"></span></h4>
            <textarea class="w-full border-slate-300 rounded text-sm p-3 mb-3 font-semibold bg-slate-50 focus:bg-white" placeholder="Type GST Question here..." data-field="question" rows="2"></textarea>
            
            <div class="options-container space-y-2 pl-4 border-l-2 border-slate-200 ml-2">
                <!-- Options injected here -->
            </div>
            <button type="button" class="mt-3 ml-6 text-xs text-blue-600 font-semibold" onclick="addOptionToMC(this.parentElement)"><i class="fas fa-plus"></i> Add Option</button>
        </div>
    </template>

    <template id="tpl-mc-option">
        <div class="flex items-center space-x-3 option-row">
            <input type="radio" class="w-4 h-4 text-blue-600 cursor-pointer" title="Mark as correct answer">
            <input type="text" class="flex-1 border-slate-300 rounded text-sm p-2" placeholder="Option text..." data-field="option">
            <i class="fas fa-minus-circle text-red-400 cursor-pointer text-lg hover:text-red-600" onclick="this.parentElement.remove()"></i>
        </div>
    </template>

    <script>
        let uniqueId = 0;
        const container = document.getElementById('builderContainer');
        const emptyMsg = document.getElementById('emptyBuilderMsg');

        function checkEmpty() {
            if(container.querySelectorAll('.builder-card').length === 0) emptyMsg.style.display = 'block';
            else emptyMsg.style.display = 'none';
            
            // Renumber
            container.querySelectorAll('.builder-card').forEach((card, idx) => {
                card.querySelector('.q-number').innerText = idx + 1;
            });
        }

        function addMultipleChoice(data = null) {
            checkEmpty();
            uniqueId++;
            const tpl = document.getElementById('tpl-multiple_choice').content.cloneNode(true);
            const card = tpl.querySelector('.builder-card');
            if(data) card.querySelector('[data-field="question"]').value = data.question;
            container.appendChild(card);
            
            if(data && data.options) {
                data.options.forEach((opt, idx) => addOptionToMC(card, opt, data.correct == idx));
            } else {
                addOptionToMC(card, "Option A", true); 
                addOptionToMC(card, "Option B", false); 
                addOptionToMC(card, "Option C", false); 
                addOptionToMC(card, "Option D", false);
            }
            checkEmpty();
        }

        function addOptionToMC(card, optionText = "", isCorrect = false) {
            const optsContainer = card.querySelector('.options-container');
            const tpl = document.getElementById('tpl-mc-option').content.cloneNode(true);
            const row = tpl.querySelector('.option-row');
            
            const radio = row.querySelector('input[type="radio"]');
            radio.name = 'mc_correct_' + uniqueId; 
            
            row.querySelector('[data-field="option"]').value = optionText;
            if(isCorrect) radio.checked = true;
            
            optsContainer.appendChild(row);
        }

        function serializeBuilder() {
            const cards = container.querySelectorAll('.builder-card');
            const questions = [];
            
            cards.forEach(card => {
                let q = { type: 'multichoice' };
                q.question = card.querySelector('[data-field="question"]').value;
                q.options = [];
                q.correct = 0;
                
                const rows = card.querySelectorAll('.option-row');
                rows.forEach((r, idx) => {
                    const optText = r.querySelector('[data-field="option"]').value;
                    if(optText.trim() !== "") {
                        q.options.push(optText);
                        if(r.querySelector('input[type="radio"]').checked) {
                            q.correct = idx;
                        }
                    }
                });
                questions.push(q);
            });
            
            document.getElementById('questions_json').value = JSON.stringify(questions);
            return true;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            try {
                const qs = JSON.parse(document.getElementById('questions_json').value);
                qs.forEach(q => addMultipleChoice(q));
            } catch(e) {}
            checkEmpty();
        });
    </script>
</body>
</html>
