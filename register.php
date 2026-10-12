<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

$sections_by_grade = get_sections_by_grade($pdo);
$posted_grade = $_POST['grade_level'] ?? '';
$posted_section = $_POST['section'] ?? '';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get inputs
    $fname = trim($_POST['fname']);
    $mname = trim($_POST['mname']);
    $lname = trim($_POST['lname']);
    $lrn = trim($_POST['lrn']);
    $age = (int)$_POST['age'];
    $section = trim($_POST['section']);
    $grade_level = trim($_POST['grade_level']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $pass = $_POST['password'];
    $repass = $_POST['repassword'];
    $role = 'student'; // Default role is student

    // Validation
    if ($age <= 0) {
        $error = "Age must be a positive number.";
    } elseif (!preg_match('/^[0-9]{12}$/', $lrn)) {
        $error = "LRN must be exactly 12 digits.";
    } elseif ($pass !== $repass) {
        $error = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($pass, PASSWORD_BCRYPT);

        // Anti-SQL Injection: Using prepared statements
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR username = :username OR lrn = :lrn");
        $stmt->execute(['email' => $email, 'username' => $username, 'lrn' => $lrn]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Email, Username, or LRN already exists!";
        } else {
            // Insert new user
            $insert = $pdo->prepare("INSERT INTO users (fname, mname, lname, lrn, age, section, grade_level, email, username, password, role) VALUES (:fname, :mname, :lname, :lrn, :age, :section, :grade_level, :email, :username, :password, :role)");
            $result = $insert->execute([
                'fname' => $fname,
                'mname' => $mname,
                'lname' => $lname,
                'lrn' => $lrn,
                'age' => $age,
                'section' => $section,
                'grade_level' => $grade_level,
                'email' => $email,
                'username' => $username,
                'password' => $hashed_password,
                'role' => $role
            ]);

            if ($result) {
                header("Location: index.php?registered=1");
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="aral_logoindex.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ARAL READING Program</title>
    <!-- Tailwind CSS -->
    <script>
        const originalWarn = console.warn;
        console.warn = function() {
            if (arguments[0] && typeof arguments[0] === 'string' && arguments[0].includes('cdn.tailwindcss.com should not be used in production')) return;
            originalWarn.apply(console, arguments);
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
    .sidebar { background-color: #1a365d; }
    </style>

    <script src="js/htmx.min.js"></script>
    <meta name="htmx-config" content='{"globalViewTransitions":true}'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="flex items-center justify-center min-h-screen py-10 px-4">

    <div class="w-full max-w-2xl bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-8">
        
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <img src="aral_logoindex.png" alt="ARAL Reading Program Logo" class="mx-auto h-24 mb-4 object-contain">
            <h2 class="text-2xl font-bold text-slate-800">Create an Account</h2>
        </div>

        <!-- Messages -->
        <?php if($error): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-lg mb-6 text-sm flex items-start">
            <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>
        <?php if($success): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6 text-sm flex items-start">
            <i class="fas fa-check-circle mt-0.5 mr-2"></i>
            <span><?php echo $success; ?></span>
        </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="" class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Row 1 -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">First Name</label>
                    <input type="text" name="fname" required 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Middle Name</label>
                    <input type="text" name="mname" required 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition">
                </div>

                <!-- Row 2 -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Last Name</label>
                    <input type="text" name="lname" required 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">LRN (Learner Reference Number)</label>
                    <input type="text" name="lrn" required maxlength="12" minlength="12" pattern="[0-9]{12}" inputmode="numeric"
                        title="LRN must be exactly 12 digits"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12)"
                        placeholder="12-digit LRN (e.g. 123456789012)"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition">
                </div>

                <!-- Row 3 -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Age</label>
                    <input type="number" name="age" required min="1" 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Grade Level</label>
                    <select name="grade_level" id="grade_level" required onchange="updateSectionDropdown()"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white transition">
                        <option value="" disabled <?php echo empty($posted_grade) ? 'selected' : ''; ?>>Select Grade</option>
                        <option value="Grade 7" <?php echo $posted_grade === 'Grade 7' ? 'selected' : ''; ?>>Grade 7</option>
                        <option value="Grade 8" <?php echo $posted_grade === 'Grade 8' ? 'selected' : ''; ?>>Grade 8</option>
                        <option value="Grade 9" <?php echo $posted_grade === 'Grade 9' ? 'selected' : ''; ?>>Grade 9</option>
                        <option value="Grade 10" <?php echo $posted_grade === 'Grade 10' ? 'selected' : ''; ?>>Grade 10</option>
                    </select>
                </div>

                <!-- Row 4 -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Section</label>
                    <select name="section" id="section" required 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white transition disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                        <option value="">Select Grade Level First</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" required 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition">
                </div>

                <!-- Row 5 -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                    <input type="text" name="username" required 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition">
                </div>

                <!-- Row 6 (Spans both columns) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Re-type Password</label>
                    <input type="password" name="repassword" required 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition">
                </div>
            </div>
            
            <div class="pt-4 border-t border-slate-100">
                <button type="submit" 
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 rounded-lg transition duration-150 shadow-sm">
                    Register Account
                </button>
            </div>
        </form>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-slate-500">
            Already have an account? 
            <a href="index.php" class="text-blue-600 hover:text-blue-700 font-medium hover:underline">Sign in here</a>
        </div>
    </div>


    <script>
        const sectionsByGrade = <?php echo json_encode($sections_by_grade, JSON_UNESCAPED_UNICODE); ?>;
        const initialSelectedSection = <?php echo json_encode($posted_section); ?>;

        function updateSectionDropdown() {
            const gradeSelect = document.getElementById('grade_level');
            const secSelect = document.getElementById('section');
            const selectedGrade = gradeSelect.value;

            secSelect.innerHTML = '';

            if (!selectedGrade || !sectionsByGrade[selectedGrade]) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'Select Grade Level First';
                secSelect.appendChild(opt);
                secSelect.disabled = true;
                return;
            }

            const sections = sectionsByGrade[selectedGrade] || [];
            secSelect.disabled = false;

            if (sections.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'No sections available for ' + selectedGrade;
                secSelect.appendChild(opt);
                return;
            }

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = 'Select Section (' + selectedGrade + ')';
            secSelect.appendChild(defaultOpt);

            sections.forEach(function(sec) {
                const opt = document.createElement('option');
                opt.value = sec;
                opt.textContent = sec;
                if (initialSelectedSection && initialSelectedSection === sec) {
                    opt.selected = true;
                }
                secSelect.appendChild(opt);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('grade_level').value) {
                updateSectionDropdown();
            } else {
                document.getElementById('section').disabled = true;
            }
        });

        window.toggleSidebar = function() {
            const sidebar = document.getElementById('appSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if(sidebar) sidebar.classList.toggle('-translate-x-full');
            if(overlay) overlay.classList.toggle('hidden');
        };
    </script>
</body>
</html>
