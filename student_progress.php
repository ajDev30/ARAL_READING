<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$level = $user['current_level'];

$mname = $user['mname'] ?? '';
$mi = !empty($mname) ? strtoupper(substr($mname, 0, 1)) . '.' : '';
$fullName = htmlspecialchars($user['fname'] . ' ' . $mi . ' ' . $user['lname']);

// Fetch attempts for charting
$stmt = $pdo->prepare("SELECT created_at, accuracy_score, reading_speed FROM reading_attempts WHERE user_id = ? ORDER BY created_at ASC");
$stmt->execute([$user_id]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$accuracy_data = [];
$speed_data = [];
$wcpm_data = [];

foreach($attempts as $att) {
    $labels[] = date('M d', strtotime($att['created_at']));
    $acc = floatval($att['accuracy_score']);
    $spd = floatval($att['reading_speed']);
    $accuracy_data[] = $acc;
    $speed_data[] = $spd;
    $wcpm_data[] = round($spd * ($acc / 100));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="https://tibungcodistrict.wordpress.com/wp-content/uploads/2018/06/untitled-1.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Progress - Phil-IRI</title>
    <script>
        const originalWarn = console.warn;
        console.warn = function() {
            if (arguments[0] && typeof arguments[0] === 'string' && arguments[0].includes('cdn.tailwindcss.com should not be used in production')) return;
            originalWarn.apply(console, arguments);
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
        .sidebar { background-color: #1a365d; }
    
        
                <?php $is_dark = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark'; ?>
        <?php if($is_dark): ?>
        /* Refined Slate Dark Mode */
        body { background-color: #0f172a !important; color: #f8fafc !important; }
        .bg-white, .bg-slate-50 { background-color: #1e293b !important; border-color: #334155 !important; color: #f8fafc !important; }
        
        .text-slate-800, .text-slate-700 { color: #f1f5f9 !important; }
        .text-slate-600, .text-slate-500, .text-slate-400 { color: #cbd5e1 !important; }
        .border-slate-200, .border-slate-100, .border-b { border-color: #334155 !important; }
        .border-slate-300 { border-color: #475569 !important; }
        .sidebar { background-color: #0b1120 !important; border-right: 1px solid #1e293b !important; }
        input, select, textarea { background-color: #0f172a !important; color: white !important; border-color: #475569 !important; }
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.5) !important; }

        /* Colored Badges / Cards Fixes */
        .bg-blue-50, .bg-blue-100 { background-color: rgba(59, 130, 246, 0.2) !important; color: #93c5fd !important; }
        .text-blue-600, .text-blue-700, .text-blue-800 { color: #60a5fa !important; }
        .border-blue-100, .border-blue-200 { border-color: rgba(59, 130, 246, 0.3) !important; }

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
        
        
        /* Bug Fixes for "Not Taken", "Pre-Test", and Table Hovers */
        .bg-slate-100, .bg-slate-200 { background-color: #334155 !important; color: #e2e8f0 !important; }
        .hover\:bg-slate-50:hover { background-color: #334155 !important; }
        tr:hover { background-color: #334155 !important; }
/* Logo Fix */
        .sidebar img { background-color: transparent !important; filter: drop-shadow(0px 0px 2px rgba(255,255,255,0.5)) !important; }
        <?php endif; ?>
    </style>




    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <meta name="htmx-config" content='{"globalViewTransitions":true}'>
</head>
<body class="flex h-screen overflow-hidden text-slate-800">
    <?php include 'student_sidebar.php'; ?>
    
    <!-- Sidebar -->
    
    

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Header -->
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 shrink-0">
            <div class="flex items-center">
                <button onclick="toggleSidebar()" class="md:hidden mr-4 text-slate-500 hover:text-blue-600 focus:outline-none"><i class="fas fa-bars text-xl"></i></button>
                <div>
                <h2 class="font-bold text-2xl text-slate-800">My Progress</h2>
                <p class="text-sm text-slate-500">Track your reading improvement over time.</p>
            </div>
            </div>
            <div class="flex items-center space-x-3">
                <?php 
                $avatarPath = 'uploads/profiles/user_' . $_SESSION['user_id'] . '.jpg';
                if (file_exists($avatarPath)): 
                ?>
                    <img src="<?php echo $avatarPath; ?>?t=<?php echo time(); ?>" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 keep-colors font-bold">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <span class="font-semibold text-sm"><?php echo $fullName; ?></span>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-slate-50">
            
            <?php $target_user_id = $user_id; include 'comparison_widget.php'; ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:p-8">
                <!-- Accuracy Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center"><i class="fas fa-crosshairs mr-2 text-blue-500"></i> Reading Accuracy Trend</h3>
                    <div style="height: 300px;">
                        <canvas id="accuracyChart"></canvas>
                    </div>
                </div>

                <!-- Speed Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center"><i class="fas fa-stopwatch mr-2 text-purple-500"></i> Reading Speed (WPM)</h3>
                    <div style="height: 300px;">
                        <canvas id="speedChart"></canvas>
                    </div>
                </div>

                <!-- WCPM Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:col-span-2 lg:col-span-1">
                    <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center"><i class="fas fa-tachometer-alt mr-2 text-emerald-500"></i> WCPM Trend</h3>
                    <div style="height: 300px;">
                        <canvas id="wcpmChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Keep Reading Promo -->
            <div class="mt-8 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-md p-4 md:p-8 text-white flex flex-col md:flex-row md:items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold mb-2">Great job tracking your progress!</h3>
                    <p class="text-blue-100 max-w-lg">Consistent practice is the key to becoming an Independent reader. Keep taking your intervention courses and reading new passages.</p>
                </div>
                <div class="text-6xl opacity-30">
                    <i class="fas fa-rocket"></i>
                </div>
            </div>

        </main>
    </div>

    <script>
    {
const labels = <?php echo json_encode($labels); ?>;
    const accData = <?php echo json_encode($accuracy_data); ?>;
    const speedData = <?php echo json_encode($speed_data); ?>;
    const wcpmData = <?php echo json_encode($wcpm_data); ?>;

    if (labels.length > 0) {
        new Chart(document.getElementById('accuracyChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Accuracy %',
                    data: accData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { min: 0, max: 100 } }
            }
        });

        new Chart(document.getElementById('speedChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Words Per Minute',
                    data: speedData,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { min: 0 } }
            }
        });

        new Chart(document.getElementById('wcpmChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Words Correct Per Minute',
                    data: wcpmData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { min: 0 } }
            }
        });
    }
}
    </script>

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
