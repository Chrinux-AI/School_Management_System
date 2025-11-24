<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

$teacher_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

// Get teacher's classes
$my_classes = db()->fetchAll("
    SELECT c.*, COUNT(DISTINCT ce.student_id) as student_count
    FROM classes c
    LEFT JOIN class_enrollments ce ON c.id = ce.class_id
    WHERE c.teacher_id = ?
    GROUP BY c.id
", [$teacher_id]);

// Get total students
$total_students = db()->count('class_enrollments', 'class_id IN (SELECT id FROM classes WHERE teacher_id = ?)', ['teacher_id' => $teacher_id]);

// Today's attendance for my classes
$today = date('Y-m-d');
$today_records = db()->fetchAll("
    SELECT ar.* FROM attendance_records ar
    JOIN classes c ON ar.class_id = c.id
    WHERE c.teacher_id = ? AND DATE(ar.check_in_time) = ?
", [$teacher_id, $today]);

$today_present = count(array_filter($today_records, fn($r) => in_array($r['status'], ['present', 'late'])));
$today_total = count($today_records);
$today_rate = $today_total > 0 ? round(($today_present / $today_total) * 100, 1) : 0;

$page_title = 'Teacher Dashboard';
$page_icon = 'chalkboard-teacher';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/attendance/manifest.json">
    <meta name="theme-color" content="#00BFFF">
    <link rel="apple-touch-icon" href="/attendance/assets/images/icons/icon-192x192.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Orbitron:wght@500;700;900&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../assets/css/cyberpunk-ui.css" rel="stylesheet">
</head>

<body class="cyber-bg">
    <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="cyber-bg">
        <div class="starfield"></div>
    </div>
    <div class="cyber-grid"></div>
    <div class="cyber-layout">
        <aside class="cyber-sidebar">
            <div class="sidebar-header">
                <div class="logo-wrapper">
                    <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div class="logo-text">
                        <div class="app-name">Attendance AI</div>
                        <div class="app-tagline">TEACHER PANEL</div>
                    </div>
                </div>
            </div>
            <div class="user-card">
                <div class="user-avatar"><?php echo strtoupper(substr($full_name, 0, 2)); ?></div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($full_name); ?></div>
                    <div class="user-role">Teacher</div>
                </div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="section-title">CORE</div>
                    <a href="dashboard.php" class="menu-item active"><i class="fas fa-brain"></i><span>Dashboard</span></a>
                    <a href="my-classes.php" class="menu-item"><i class="fas fa-door-open"></i><span>My Classes</span></a>
                    <a href="attendance.php" class="menu-item"><i class="fas fa-clipboard-check"></i><span>Attendance</span></a>
                    <a href="students.php" class="menu-item"><i class="fas fa-user-graduate"></i><span>My Students</span></a>
                </div>
                <div class="nav-section">
                    <div class="section-title">MANAGEMENT</div>
                    <a href="reports.php" class="menu-item"><i class="fas fa-chart-line"></i><span>Reports</span></a>
                    <a href="settings.php" class="menu-item"><i class="fas fa-cog"></i><span>Settings</span></a>
                </div>
            </nav>
            <div class="sidebar-footer">
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </div>
        </aside>
        <main class="cyber-main">
            <header class="cyber-header">
                <div class="page-title-section">
                    <div class="page-icon-orb"><i class="fas fa-<?php echo $page_icon; ?>"></i></div>
                    <h1 class="page-title"><?php echo $page_title; ?></h1>
                </div>
                <div class="header-actions">
                    <div class="biometric-orb" title="Quick Scan"><i class="fas fa-fingerprint"></i></div>
                    <div class="user-card" style="padding:8px 15px;margin:0;">
                        <div class="user-avatar" style="width:35px;height:35px;font-size:0.9rem;">
                            <?php echo strtoupper(substr($full_name, 0, 2)); ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name" style="font-size:0.85rem;"><?php echo htmlspecialchars($full_name); ?></div>
                            <div class="user-role">Teacher</div>
                        </div>
                    </div>
                </div>
            </header>
            <div class="cyber-content fade-in">
                <section class="orb-grid">
                    <div class="stat-orb">
                        <div class="orb-icon-wrapper cyan"><i class="fas fa-door-open"></i></div>
                        <div class="orb-content">
                            <div class="orb-value"><?php echo count($my_classes); ?></div>
                            <div class="orb-label">My Classes</div>
                            <div class="orb-trend up"><i class="fas fa-check-circle"></i><span>Active</span></div>
                        </div>
                    </div>
                    <div class="stat-orb">
                        <div class="orb-icon-wrapper green"><i class="fas fa-user-graduate"></i></div>
                        <div class="orb-content">
                            <div class="orb-value"><?php echo $total_students; ?></div>
                            <div class="orb-label">Total Students</div>
                            <div class="orb-trend up"><i class="fas fa-users"></i><span>Enrolled</span></div>
                        </div>
                    </div>
                    <div class="stat-orb">
                        <div class="orb-icon-wrapper purple"><i class="fas fa-percentage"></i></div>
                        <div class="orb-content">
                            <div class="orb-value"><?php echo $today_rate; ?>%</div>
                            <div class="orb-label">Today's Attendance</div>
                            <div class="orb-trend <?php echo $today_rate >= 90 ? 'up' : 'down'; ?>"><i class="fas fa-<?php echo $today_rate >= 90 ? 'check' : 'exclamation'; ?>-circle"></i><span><?php echo $today_present; ?> of <?php echo $today_total; ?></span></div>
                        </div>
                    </div>
                </section>

                <section class="holo-card" style="margin-bottom:30px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">
                        <h2 style="margin:0;display:flex;align-items:center;gap:12px;">
                            <i class="fas fa-brain" style="color:var(--hologram-purple);"></i>
                            <span>AI Analytics & Machine Learning</span>
                        </h2>
                        <span class="cyber-badge" style="background:rgba(100,100,100,0.3);">System Inactive</span>
                    </div>
                    <div class="orb-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">
                        <div class="holo-card" style="padding:20px;">
                            <h4 style="color:var(--cyber-cyan);margin-bottom:12px;">Attendance Predictor</h4>
                            <div style="font-size:2rem;color:var(--text-primary);margin-bottom:8px;">0.0%</div>
                            <div style="color:var(--text-muted);font-size:0.85rem;">Accuracy Rate</div>
                            <span class="cyber-badge" style="margin-top:10px;background:rgba(100,100,100,0.3);">Inactive</span>
                        </div>
                        <div class="holo-card" style="padding:20px;">
                            <h4 style="color:var(--golden-pulse);margin-bottom:12px;">Performance Analyzer</h4>
                            <div style="font-size:2rem;color:var(--text-primary);margin-bottom:8px;">0.0%</div>
                            <div style="color:var(--text-muted);font-size:0.85rem;">Accuracy Rate</div>
                            <span class="cyber-badge" style="margin-top:10px;background:rgba(100,100,100,0.3);">Inactive</span>
                        </div>
                    </div>
                </section>

                <div class="holo-card">
                    <h3 style="margin-bottom:20px;">My Classes</h3>
                    <div style="display:grid;gap:20px;">
                        <?php foreach ($my_classes as $class): ?>
                            <div style="background:linear-gradient(135deg,rgba(0,191,255,0.05),rgba(138,43,226,0.05));border:1px solid rgba(0,191,255,0.2);border-radius:12px;padding:20px;">
                                <div style="display:flex;justify-content:space-between;align-items:start;">
                                    <div>
                                        <h4 style="color:var(--cyber-cyan);font-size:1.2rem;margin-bottom:8px;"><?php echo htmlspecialchars($class['name']); ?></h4>
                                        <div style="color:rgba(0,191,255,0.6);font-size:0.9rem;margin-bottom:10px;"><?php echo htmlspecialchars($class['class_code']); ?></div>
                                    </div>
                                    <span class="cyber-badge cyan"><?php echo $class['student_count']; ?> Students</span>
                                </div>
                                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-top:15px;">
                                    <div style="padding:10px;background:rgba(0,0,0,0.3);border-radius:8px;">
                                        <div style="color:var(--text-muted);font-size:0.8rem;">Level</div>
                                        <div style="color:var(--cyber-cyan);font-weight:600;"><?php echo $class['grade_level']; ?> Level</div>
                                    </div>
                                    <div style="padding:10px;background:rgba(0,0,0,0.3);border-radius:8px;">
                                        <div style="color:var(--text-muted);font-size:0.8rem;">Room</div>
                                        <div style="color:var(--cyber-cyan);font-weight:600;"><?php echo htmlspecialchars($class['room_number'] ?? 'TBA'); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/pwa-manager.js"></script>
    <script src="../assets/js/pwa-analytics.js"></script>
</body>

</html>