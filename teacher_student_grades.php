<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

$student_id = (int)($_GET['user_id'] ?? 0);

$stmt = $pdo->prepare("SELECT fname, lname, grade_level, section FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Student not found.");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Grades - <?php echo htmlspecialchars($student['fname'] . ' ' . $student['lname']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #F8FAFC; }
        .sidebar { background-color: #1a365d; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-slate-800">
    <?php include 'teacher_sidebar.php'; ?>
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 shrink-0">
            <div class="flex items-center">
                <button onclick="toggleSidebar()" class="md:hidden mr-4 text-slate-500 hover:text-blue-600 focus:outline-none"><i class="fas fa-bars text-xl"></i></button>
                <a href="teacher_course_grades.php" class="text-slate-400 hover:text-slate-600 mr-4"><i class="fas fa-arrow-left"></i></a>
                <div>
                    <h1 class="font-bold text-lg text-slate-800">Course Grades: <?php echo htmlspecialchars($student['fname'] . ' ' . $student['lname']); ?></h1>
                    <p class="text-xs text-slate-500">Grade <?php echo htmlspecialchars($student['grade_level'] ?? ''); ?> - Section <?php echo htmlspecialchars($student['section'] ?? ''); ?></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openRulesModal()" class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-sm font-semibold border border-blue-200 transition shadow-xs">
                    <i class="fas fa-sliders-h"></i>
                    <span>Manage Conditional Rules</span>
                </button>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto max-w-6xl mx-auto w-full">
            <?php 
                $target_user_id = $student_id;
                include 'comparison_widget.php';
            ?>
        </main>
    </div>

    <!-- Customize Recommendation Modal -->
    <div id="customizeRecModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden transition-opacity duration-200">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                        <i class="fas fa-user-edit text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-base" id="recModalTitle">Customize Recommendation</h3>
                        <p class="text-xs text-slate-500" id="recModalSubtitle">Tailor specific guidance for this student</p>
                    </div>
                </div>
                <button type="button" onclick="closeCustomizeRecModal()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center hover:bg-slate-200/50 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form id="customizeRecForm" onsubmit="saveStudentRec(event)" class="p-6 overflow-y-auto space-y-4">
                <input type="hidden" name="user_id" id="recUserId" value="">
                <input type="hidden" name="course_group" id="recCourseGroup" value="">

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Condition Tag / Label</label>
                    <input type="text" name="condition_tag" id="recConditionTag" placeholder="e.g. Personalized Guidance, Targeted Intervention, Fast-Track" class="w-full px-3.5 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <span class="text-[11px] text-slate-400 self-center mr-1">Quick Tags:</span>
                        <button type="button" onclick="setRecTag('Teacher Note')" class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600 hover:bg-slate-200 transition">Teacher Note</button>
                        <button type="button" onclick="setRecTag('Targeted Practice')" class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600 hover:bg-slate-200 transition">Targeted Practice</button>
                        <button type="button" onclick="setRecTag('Intervention Plan')" class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600 hover:bg-slate-200 transition">Intervention Plan</button>
                        <button type="button" onclick="setRecTag('Advanced Enrichment')" class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600 hover:bg-slate-200 transition">Advanced Enrichment</button>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Recommendation Text</label>
                        <span class="text-[11px] text-slate-400">Click any shortcut below to insert</span>
                    </div>

                    <!-- Quick Insert Shortcut Pills -->
                    <div class="flex flex-wrap items-center gap-1.5 mb-2 p-2 bg-slate-50 border border-slate-200 rounded-lg">
                        <span class="text-[11px] font-bold text-slate-500 mr-1"><i class="fas fa-terminal text-blue-500 mr-1"></i>Shortcuts:</span>
                        <button type="button" onclick="insertToken('recText', '/student')" class="px-2 py-0.5 rounded text-xs bg-white text-blue-700 font-semibold border border-blue-200 hover:bg-blue-50 transition shadow-2xs" title="Student First Name">/student</button>
                        <button type="button" onclick="insertToken('recText', '/pre-test')" class="px-2 py-0.5 rounded text-xs bg-white text-indigo-700 font-semibold border border-indigo-200 hover:bg-indigo-50 transition shadow-2xs" title="Pre-Test Reading Profile">/pre-test</button>
                        <button type="button" onclick="insertToken('recText', '/post-test')" class="px-2 py-0.5 rounded text-xs bg-white text-emerald-700 font-semibold border border-emerald-200 hover:bg-emerald-50 transition shadow-2xs" title="Post-Test Reading Profile">/post-test</button>
                        <button type="button" onclick="insertToken('recText', '/diff_accuracy')" class="px-2 py-0.5 rounded text-xs bg-white text-cyan-700 font-semibold border border-cyan-200 hover:bg-cyan-50 transition shadow-2xs" title="Accuracy Difference %">/diff_accuracy</button>
                        <button type="button" onclick="insertToken('recText', '/diff_comprehension')" class="px-2 py-0.5 rounded text-xs bg-white text-violet-700 font-semibold border border-violet-200 hover:bg-violet-50 transition shadow-2xs" title="Comprehension Difference %">/diff_comprehension</button>
                        <button type="button" onclick="insertToken('recText', '/improve')" class="px-2 py-0.5 rounded text-xs bg-white text-amber-700 font-semibold border border-amber-200 hover:bg-amber-50 transition shadow-2xs" title="Improved Areas Summary">/improve</button>
                    </div>

                    <textarea name="recommendation_text" id="recText" rows="5" required placeholder="Write personalized teacher recommendation here e.g. Great work /student! Keep practicing..." class="w-full p-3.5 border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 leading-relaxed"></textarea>
                </div>

                <!-- Template Selector -->
                <div class="bg-blue-50/60 border border-blue-100 rounded-xl p-3.5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1.5">
                        <span class="text-xs font-bold text-blue-800"><i class="fas fa-lightbulb text-amber-500 mr-1.5"></i> Insert From Existing Rule</span>
                        <select id="quickTemplateSelect" onchange="insertTemplateFromSelect()" class="text-xs border border-blue-200 bg-white rounded px-2.5 py-1 text-slate-700 focus:outline-none focus:ring-1 focus:ring-blue-500 max-w-xs">
                            <option value="">-- Choose a rule template --</option>
                        </select>
                    </div>
                    <p class="text-[11px] text-blue-600 leading-normal">
                        Select a template above to use it as a starting draft, or write your own custom guidance from scratch.
                    </p>
                </div>

                <div id="recModalAlert" class="hidden p-3 rounded-lg text-xs font-medium"></div>

                <!-- Actions -->
                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeCustomizeRecModal()" class="px-4 py-2 border border-slate-300 text-slate-600 hover:bg-slate-50 rounded-lg text-sm font-medium transition">
                        Cancel
                    </button>
                    <button type="submit" id="saveRecBtn" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition shadow-sm flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Save Recommendation</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manage Conditional Rules Modal -->
    <div id="rulesModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden transition-opacity duration-200">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                        <i class="fas fa-sliders-h text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">Conditional Recommendation Rules</h3>
                        <p class="text-xs text-slate-500">Define automated recommendation templates triggered by student progress & Phil-IRI profile</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="showNewRuleForm()" id="btnAddRule" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition shadow-xs">
                        <i class="fas fa-plus"></i> Add New Rule
                    </button>
                    <button type="button" onclick="closeRulesModal()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center hover:bg-slate-200/50 transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- Add / Edit Rule Form (collapsible) -->
                <div id="ruleEditorCard" class="hidden bg-slate-50 border border-slate-200 rounded-xl p-5 mb-4">
                    <h4 class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2" id="ruleEditorTitle">
                        <i class="fas fa-edit text-blue-600"></i> <span>Add New Conditional Rule</span>
                    </h4>
                    <form id="ruleForm" onsubmit="saveRule(event)" class="space-y-4">
                        <input type="hidden" name="rule_id" id="editRuleId" value="0">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-1">
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Condition Name / Tag</label>
                                <input type="text" name="condition_name" id="editRuleName" required placeholder="e.g. Mastered Independent" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Trigger Outcome</label>
                                <select name="outcome" id="editRuleOutcome" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="Improved">Improved</option>
                                    <option value="Partial Progress">Partial Progress</option>
                                    <option value="Declined">Declined</option>
                                    <option value="Maintained">Maintained</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Post-Test Only">Post-Test Only</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Target Profile</label>
                                <select name="target_profile" id="editRuleProfile" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="Any">Any Profile</option>
                                    <option value="Independent">Independent</option>
                                    <option value="Instructional">Instructional</option>
                                    <option value="Frustration">Frustration</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Recommendation Template</label>
                                <span class="text-[11px] text-slate-400">Click any shortcut below to insert into template</span>
                            </div>

                            <!-- Click-to-insert Shortcut Pills -->
                            <div class="flex flex-wrap items-center gap-1.5 mb-2 p-2 bg-white border border-slate-200 rounded-lg shadow-2xs">
                                <span class="text-[11px] font-bold text-slate-500 mr-1"><i class="fas fa-terminal text-blue-500 mr-1"></i>Shortcuts:</span>
                                <button type="button" onclick="insertToken('editRuleTemplate', '/student')" class="px-2.5 py-1 rounded text-xs bg-blue-50 text-blue-700 font-bold border border-blue-200 hover:bg-blue-100 transition shadow-xs" title="Insert student name">/student</button>
                                <button type="button" onclick="insertToken('editRuleTemplate', '/pre-test')" class="px-2.5 py-1 rounded text-xs bg-indigo-50 text-indigo-700 font-bold border border-indigo-200 hover:bg-indigo-100 transition shadow-xs" title="Insert pre-test reading profile">/pre-test</button>
                                <button type="button" onclick="insertToken('editRuleTemplate', '/post-test')" class="px-2.5 py-1 rounded text-xs bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 hover:bg-emerald-100 transition shadow-xs" title="Insert post-test reading profile">/post-test</button>
                                <button type="button" onclick="insertToken('editRuleTemplate', '/diff_accuracy')" class="px-2.5 py-1 rounded text-xs bg-cyan-50 text-cyan-700 font-bold border border-cyan-200 hover:bg-cyan-100 transition shadow-xs" title="Insert accuracy difference %">/diff_accuracy</button>
                                <button type="button" onclick="insertToken('editRuleTemplate', '/diff_comprehension')" class="px-2.5 py-1 rounded text-xs bg-violet-50 text-violet-700 font-bold border border-violet-200 hover:bg-violet-100 transition shadow-xs" title="Insert comprehension difference %">/diff_comprehension</button>
                                <button type="button" onclick="insertToken('editRuleTemplate', '/improve')" class="px-2.5 py-1 rounded text-xs bg-amber-50 text-amber-700 font-bold border border-amber-200 hover:bg-amber-100 transition shadow-xs" title="Insert improved areas summary">/improve</button>
                            </div>

                            <textarea name="recommendation_template" id="editRuleTemplate" rows="3" required placeholder="Write recommendation with shortcuts e.g. Congratulations /student! Reading profile improved from /pre-test to /post-test..." class="w-full p-3 border border-slate-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 leading-relaxed"></textarea>

                            <!-- User-Friendly Shortcut Explanation Card -->
                            <div class="mt-2.5 p-3.5 bg-blue-50/70 border border-blue-100 rounded-xl text-xs text-slate-700">
                                <div class="font-bold text-blue-900 mb-2 flex items-center gap-1.5">
                                    <i class="fas fa-info-circle text-blue-600"></i>
                                    <span>Shortcut Guide & Explanations</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 text-[11px] leading-snug">
                                    <div class="bg-white/90 p-2.5 rounded-lg border border-blue-100 shadow-2xs">
                                        <div class="font-mono font-bold text-blue-700">/student</div>
                                        <p class="text-slate-600 mt-0.5">Student's first name (e.g. <i>Juan</i>)</p>
                                    </div>
                                    <div class="bg-white/90 p-2.5 rounded-lg border border-blue-100 shadow-2xs">
                                        <div class="font-mono font-bold text-indigo-700">/pre-test</div>
                                        <p class="text-slate-600 mt-0.5">Pre-test profile (e.g. <i>Instructional</i>)</p>
                                    </div>
                                    <div class="bg-white/90 p-2.5 rounded-lg border border-blue-100 shadow-2xs">
                                        <div class="font-mono font-bold text-emerald-700">/post-test</div>
                                        <p class="text-slate-600 mt-0.5">Post-test profile (e.g. <i>Independent</i>)</p>
                                    </div>
                                    <div class="bg-white/90 p-2.5 rounded-lg border border-blue-100 shadow-2xs">
                                        <div class="font-mono font-bold text-cyan-700">/diff_accuracy</div>
                                        <p class="text-slate-600 mt-0.5">Accuracy difference (e.g. <i>+12.5%</i>)</p>
                                    </div>
                                    <div class="bg-white/90 p-2.5 rounded-lg border border-blue-100 shadow-2xs">
                                        <div class="font-mono font-bold text-violet-700">/diff_comprehension</div>
                                        <p class="text-slate-600 mt-0.5">Comprehension difference (e.g. <i>+20.0%</i>)</p>
                                    </div>
                                    <div class="bg-white/90 p-2.5 rounded-lg border border-blue-100 shadow-2xs">
                                        <div class="font-mono font-bold text-amber-700">/improve</div>
                                        <p class="text-slate-600 mt-0.5">Summary of gains (e.g. <i>Accuracy (+12.5%)</i>)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="ruleEditorAlert" class="hidden p-3 rounded-lg text-xs font-medium"></div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                            <button type="button" onclick="cancelRuleEdit()" class="px-3.5 py-1.5 border border-slate-300 text-slate-600 hover:bg-slate-100 rounded-lg text-xs font-medium transition">
                                Cancel
                            </button>
                            <button type="submit" id="btnSaveRuleSubmit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition shadow-xs">
                                <i class="fas fa-check mr-1"></i> Save Rule
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Rules Table / List -->
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="p-3.5 font-semibold">Condition Name</th>
                                    <th class="p-3.5 font-semibold">Outcome</th>
                                    <th class="p-3.5 font-semibold">Profile</th>
                                    <th class="p-3.5 font-semibold">Recommendation Template</th>
                                    <th class="p-3.5 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="rulesTableBody" class="text-sm divide-y divide-slate-100">
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-400">Loading rules...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                <span class="text-xs text-slate-500">Changes to conditional rules immediately update automated recommendation outputs.</span>
                <button type="button" onclick="closeRulesModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition">
                    Done
                </button>
            </div>
        </div>
    </div>

    <script>
        window.cachedRules = [];

        window.toggleSidebar = function() {
            const sidebar = document.getElementById('appSidebar');
            if(sidebar) sidebar.classList.toggle('-translate-x-full');
        };

        // --- Customize Recommendation Modal ---
        window.openCustomizeRecModal = function(data) {
            document.getElementById('recUserId').value = data.user_id;
            document.getElementById('recCourseGroup').value = data.course_group;
            document.getElementById('recConditionTag').value = data.condition_tag || 'Teacher Note';
            document.getElementById('recText').value = data.recommendation || '';
            document.getElementById('recModalTitle').textContent = 'Customize Recommendation - ' + data.course_group;
            document.getElementById('recModalSubtitle').textContent = 'Personalize recommendation for ' + data.student_name;
            
            const alert = document.getElementById('recModalAlert');
            alert.className = 'hidden p-3 rounded-lg text-xs font-medium';
            alert.textContent = '';

            loadRulesDropdown();
            document.getElementById('customizeRecModal').classList.remove('hidden');
        };

        window.closeCustomizeRecModal = function() {
            document.getElementById('customizeRecModal').classList.add('hidden');
        };

        window.setRecTag = function(tag) {
            document.getElementById('recConditionTag').value = tag;
        };

        window.insertToken = function(targetTextareaId, token) {
            const textarea = document.getElementById(targetTextareaId);
            if (!textarea) return;

            const start = textarea.selectionStart !== undefined ? textarea.selectionStart : textarea.value.length;
            const end = textarea.selectionEnd !== undefined ? textarea.selectionEnd : textarea.value.length;
            const text = textarea.value;

            let insertStr = token;
            if (start > 0 && text[start - 1] !== ' ' && text[start - 1] !== '\n') {
                insertStr = ' ' + insertStr;
            }
            if (end < text.length && text[end] !== ' ' && text[end] !== '\n' && text[end] !== '.' && text[end] !== '!' && text[end] !== '?') {
                insertStr += ' ';
            }

            textarea.value = text.substring(0, start) + insertStr + text.substring(end);
            textarea.focus();
            const newPos = start + insertStr.length;
            textarea.setSelectionRange(newPos, newPos);
        };

        window.insertTemplateFromSelect = function() {
            const select = document.getElementById('quickTemplateSelect');
            const val = select.value;
            if (val) {
                const studentName = <?php echo json_encode($student['fname']); ?>;
                const substituted = val.replace(/\{student_name\}|\/student/g, studentName);
                document.getElementById('recText').value = substituted;
            }
        };

        window.saveStudentRec = function(e) {
            e.preventDefault();
            const form = document.getElementById('customizeRecForm');
            const submitBtn = document.getElementById('saveRecBtn');
            const alertBox = document.getElementById('recModalAlert');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';

            const formData = new FormData(form);
            formData.append('action', 'save_student_rec');

            fetch('api_recommendations.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Save Recommendation';

                if (data.success) {
                    alertBox.className = 'p-3 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200';
                    alertBox.textContent = data.message || 'Saved successfully!';
                    setTimeout(() => {
                        window.location.reload();
                    }, 600);
                } else {
                    alertBox.className = 'p-3 rounded-lg text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200';
                    alertBox.textContent = data.error || 'Failed to save recommendation.';
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Save Recommendation';
                alertBox.className = 'p-3 rounded-lg text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200';
                alertBox.textContent = 'Server error: ' + err.message;
            });
        };

        window.revertStudentRec = function(userId, courseGroup) {
            if (!confirm('Revert to the standard automated rule-based recommendation for "' + courseGroup + '"?')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete_student_rec');
            formData.append('user_id', userId);
            formData.append('course_group', courseGroup);

            fetch('api_recommendations.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to revert recommendation.');
                }
            })
            .catch(err => alert('Network error: ' + err.message));
        };

        // --- Rules Modal ---
        window.openRulesModal = function() {
            document.getElementById('rulesModal').classList.remove('hidden');
            loadRulesList();
        };

        window.closeRulesModal = function() {
            document.getElementById('rulesModal').classList.add('hidden');
            cancelRuleEdit();
        };

        function renderRulesTable(rules) {
            const tbody = document.getElementById('rulesTableBody');
            if (!rules || rules.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="p-6 text-center text-slate-400">No conditional rules defined yet. Click "Add New Rule" above.</td></tr>';
                return;
            }

            tbody.innerHTML = rules.map(r => {
                const outcomeBadgeClass = r.outcome === 'Improved' ? 'bg-emerald-100 text-emerald-700' :
                    (r.outcome === 'Declined' ? 'bg-rose-100 text-rose-700' : 
                    (r.outcome === 'Partial Progress' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700'));

                return `
                    <tr class="hover:bg-slate-50 transition" id="rule-row-${r.id}">
                        <td class="p-3.5 font-bold text-slate-800 text-xs">${escapeHtml(r.condition_name)}</td>
                        <td class="p-3.5"><span class="px-2 py-0.5 rounded-full text-xs font-semibold ${outcomeBadgeClass}">${escapeHtml(r.outcome)}</span></td>
                        <td class="p-3.5 text-xs text-slate-600 font-medium">${escapeHtml(r.target_profile)}</td>
                        <td class="p-3.5 text-xs text-slate-600 max-w-md truncate" title="${escapeHtml(r.recommendation_template)}">${escapeHtml(r.recommendation_template)}</td>
                        <td class="p-3.5 text-right whitespace-nowrap">
                            <button onclick="editRule(${r.id})" class="px-2.5 py-1 text-blue-600 hover:bg-blue-50 rounded text-xs font-semibold transition mr-1">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <button onclick="deleteRule(${r.id}, '${escapeJs(r.condition_name)}')" class="px-2.5 py-1 text-rose-600 hover:bg-rose-50 rounded text-xs font-semibold transition">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function loadRulesList() {
            fetch('api_recommendations.php?action=get_rules')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.rules) {
                    window.cachedRules = data.rules;
                    renderRulesTable(data.rules);
                    loadRulesDropdown();
                }
            })
            .catch(err => {
                document.getElementById('rulesTableBody').innerHTML = `<tr><td colspan="5" class="p-6 text-center text-rose-500">Failed to load rules: ${err.message}</td></tr>`;
            });
        }

        function loadRulesDropdown() {
            if (!window.cachedRules || window.cachedRules.length === 0) {
                fetch('api_recommendations.php?action=get_rules')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.rules) {
                        window.cachedRules = data.rules;
                        populateDropdown(data.rules);
                    }
                });
            } else {
                populateDropdown(window.cachedRules);
            }
        }

        function populateDropdown(rules) {
            const select = document.getElementById('quickTemplateSelect');
            if (!select) return;
            select.innerHTML = '<option value="">-- Choose a rule template --</option>' +
                rules.map(r => `<option value="${escapeHtml(r.recommendation_template)}">[${escapeHtml(r.outcome)}] ${escapeHtml(r.condition_name)}</option>`).join('');
        }

        window.showNewRuleForm = function() {
            document.getElementById('editRuleId').value = '0';
            document.getElementById('editRuleName').value = '';
            document.getElementById('editRuleOutcome').value = 'Improved';
            document.getElementById('editRuleProfile').value = 'Any';
            document.getElementById('editRuleTemplate').value = '';
            document.getElementById('ruleEditorTitle').innerHTML = '<i class="fas fa-plus-circle text-blue-600"></i> <span>Add New Conditional Rule</span>';
            
            const alert = document.getElementById('ruleEditorAlert');
            alert.className = 'hidden p-3 rounded-lg text-xs font-medium';
            alert.textContent = '';

            document.getElementById('ruleEditorCard').classList.remove('hidden');
            document.getElementById('editRuleName').focus();
        };

        window.editRule = function(ruleId) {
            const rule = (window.cachedRules || []).find(r => r.id == ruleId);
            if (!rule) return;

            document.getElementById('editRuleId').value = rule.id;
            document.getElementById('editRuleName').value = rule.condition_name;
            document.getElementById('editRuleOutcome').value = rule.outcome;
            document.getElementById('editRuleProfile').value = rule.target_profile;
            document.getElementById('editRuleTemplate').value = rule.recommendation_template;
            document.getElementById('ruleEditorTitle').innerHTML = '<i class="fas fa-edit text-blue-600"></i> <span>Edit Conditional Rule</span>';
            
            const alert = document.getElementById('ruleEditorAlert');
            alert.className = 'hidden p-3 rounded-lg text-xs font-medium';
            alert.textContent = '';

            document.getElementById('ruleEditorCard').classList.remove('hidden');
            document.getElementById('editRuleName').focus();
        };

        window.cancelRuleEdit = function() {
            document.getElementById('ruleEditorCard').classList.add('hidden');
        };

        window.saveRule = function(e) {
            e.preventDefault();
            const form = document.getElementById('ruleForm');
            const submitBtn = document.getElementById('btnSaveRuleSubmit');
            const alertBox = document.getElementById('ruleEditorAlert');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';

            const formData = new FormData(form);
            formData.append('action', 'save_rule');

            fetch('api_recommendations.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Save Rule';

                if (data.success) {
                    alertBox.className = 'p-3 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200';
                    alertBox.textContent = data.message || 'Rule saved successfully!';
                    setTimeout(() => {
                        cancelRuleEdit();
                        loadRulesList();
                    }, 500);
                } else {
                    alertBox.className = 'p-3 rounded-lg text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200';
                    alertBox.textContent = data.error || 'Failed to save rule.';
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Save Rule';
                alertBox.className = 'p-3 rounded-lg text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200';
                alertBox.textContent = 'Server error: ' + err.message;
            });
        };

        window.deleteRule = function(ruleId, ruleName) {
            if (!confirm('Are you sure you want to delete the rule "' + ruleName + '"?')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete_rule');
            formData.append('rule_id', ruleId);

            fetch('api_recommendations.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadRulesList();
                } else {
                    alert(data.error || 'Failed to delete rule.');
                }
            })
            .catch(err => alert('Network error: ' + err.message));
        };

        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/&/g, "&amp;")
                       .replace(/</g, "&lt;")
                       .replace(/>/g, "&gt;")
                       .replace(/"/g, "&quot;")
                       .replace(/'/g, "&#039;");
        }

        function escapeJs(text) {
            if (!text) return '';
            return text.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        }

        // Preload rules for template dropdown on initial page load
        document.addEventListener('DOMContentLoaded', () => {
            loadRulesDropdown();
        });
    </script>
</body>
</html>
