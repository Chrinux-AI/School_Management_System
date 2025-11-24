<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$student_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

// Get student's attendance summary
$records = db()->fetchAll("SELECT * FROM attendance_records WHERE student_id = ?", [$student_id]);
$present = count(array_filter($records, fn($r) => $r['status'] === 'present'));
$late = count(array_filter($records, fn($r) => $r['status'] === 'late'));
$absent = count(array_filter($records, fn($r) => $r['status'] === 'absent'));
$total = count($records);
$attendance_rate = $total > 0 ? round((($present + $late) / $total) * 100, 1) : 0;

// Get student's classes
$classes = db()->fetchAll("
    SELECT c.* FROM classes c
    JOIN class_enrollments ce ON c.id = ce.class_id
    WHERE ce.student_id = ?
", [$student_id]);

$page_title = 'Student Dashboard';
$page_icon = 'user-graduate';
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Orbitron:wght@500;700;900&family=Inter:wght@500;600;700&display=swap" rel="stylesheet">
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
        <?php include '../includes/cyber-nav.php'; ?>
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
                            <div class="user-role">Student</div>
                        </div>
                    </div>
                </div>
            </header>
            <div class="cyber-content slide-in">
                <?php include '../includes/notice-board.php'; ?>

                <section class="orb-grid">
                    <div class="stat-orb">
                        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $present; ?></div>
                            <div class="stat-label">Days Present</div>
                            <div class="stat-trend up"><i class="fas fa-check"></i><span>Excellent</span></div>
                        </div>
                    </div>
                    <div class="stat-orb">
                        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $late; ?></div>
                            <div class="stat-label">Times Late</div>
                            <div class="stat-trend <?php echo $late > 5 ? 'down' : 'up'; ?>"><i class="fas fa-exclamation-circle"></i><span><?php echo $late > 5 ? 'Improve' : 'Good'; ?></span></div>
                        </div>
                    </div>
                    <div class="stat-orb">
                        <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $absent; ?></div>
                            <div class="stat-label">Days Absent</div>
                            <div class="stat-trend <?php echo $absent > 3 ? 'down' : 'up'; ?>"><i class="fas fa-info-circle"></i><span><?php echo $absent > 3 ? 'Alert' : 'Great'; ?></span></div>
                        </div>
                    </div>
                    <div class="stat-orb">
                        <div class="stat-icon cyan"><i class="fas fa-percentage"></i></div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $attendance_rate; ?>%</div>
                            <div class="stat-label">Attendance Rate</div>
                            <div class="stat-trend <?php echo $attendance_rate >= 90 ? 'up' : 'down'; ?>"><i class="fas fa-star"></i><span><?php echo $attendance_rate >= 90 ? 'Excellent' : 'Improve'; ?></span></div>
                        </div>
                    </div>
                </section>

                <section class="holo-card" style="margin-bottom:30px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">
                        <h2 style="margin:0;display:flex;align-items:center;gap:12px;">
                            <i class="fas fa-brain" style="color:var(--hologram-purple);"></i>
                            <span>AI Analytics & Machine Learning</span>
                        </h2>
                        <span class="cyber-badge" style="background:rgba(100,100,100,0.3);">Neural Network Inactive</span>
                    </div>
                    <div class="orb-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">
                        <div class="holo-card" style="padding:20px;">
                            <h4 style="color:var(--cyber-cyan);margin-bottom:12px;">Performance Predictor</h4>
                            <div style="font-size:2rem;color:var(--text-primary);margin-bottom:8px;">0.0%</div>
                            <div style="color:var(--text-muted);font-size:0.85rem;">Accuracy Rate</div>
                            <span class="cyber-badge" style="margin-top:10px;background:rgba(100,100,100,0.3);">Inactive</span>
                        </div>
                        <div class="holo-card" style="padding:20px;">
                            <h4 style="color:var(--golden-pulse);margin-bottom:12px;">Behavior Analyzer</h4>
                            <div style="font-size:2rem;color:var(--text-primary);margin-bottom:8px;">0.0%</div>
                            <div style="color:var(--text-muted);font-size:0.85rem;">Accuracy Rate</div>
                            <span class="cyber-badge" style="margin-top:10px;background:rgba(100,100,100,0.3);">Inactive</span>
                        </div>
                    </div>
                </section>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;margin-bottom:30px;">
                    <div class="holo-card">
                        <div style="display:flex;align-items:center;gap:15px;margin-bottom:15px;">
                            <div style="width:50px;height:50px;background:linear-gradient(135deg,var(--cyber-cyan),var(--hologram-purple));border-radius:12px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-fingerprint" style="font-size:1.5rem;color:#fff;"></i>
                            </div>
                            <div>
                                <h3 style="margin:0;color:var(--cyber-cyan);">Quick Check-In</h3>
                                <p style="margin:5px 0 0;color:var(--text-muted);font-size:0.9rem;">Mark your attendance</p>
                            </div>
                        </div>
                        <a href="checkin.php" class="cyber-btn cyan" style="width:100%;text-align:center;text-decoration:none;"><i class="fas fa-clock"></i> Check In Now</a>
                    </div>

                    <div class="holo-card">
                        <div style="display:flex;align-items:center;gap:15px;margin-bottom:15px;">
                            <div style="width:50px;height:50px;background:linear-gradient(135deg,var(--neon-green),var(--cyber-cyan));border-radius:12px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-chart-line" style="font-size:1.5rem;color:#fff;"></i>
                            </div>
                            <div>
                                <h3 style="margin:0;color:var(--neon-green);">Attendance History</h3>
                                <p style="margin:5px 0 0;color:var(--text-muted);font-size:0.9rem;">View detailed records</p>
                            </div>
                        </div>
                        <a href="attendance.php" class="cyber-btn green" style="width:100%;text-align:center;text-decoration:none;"><i class="fas fa-clipboard-check"></i> View Records</a>
                    </div>

                    <div class="holo-card">
                        <div style="display:flex;align-items:center;gap:15px;margin-bottom:15px;">
                            <div style="width:50px;height:50px;background:linear-gradient(135deg,var(--golden-pulse),var(--neon-green));border-radius:12px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-calendar-alt" style="font-size:1.5rem;color:#fff;"></i>
                            </div>
                            <div>
                                <h3 style="margin:0;color:var(--golden-pulse);">Class Schedule</h3>
                                <p style="margin:5px 0 0;color:var(--text-muted);font-size:0.9rem;">Check upcoming classes</p>
                            </div>
                        </div>
                        <a href="schedule.php" class="cyber-btn orange" style="width:100%;text-align:center;text-decoration:none;"><i class="fas fa-calendar"></i> View Schedule</a>
                    </div>
                </div>

                <div class="holo-card">
                    <h3 style="margin-bottom:20px;">My Classes</h3>
                    <div style="display:grid;gap:15px;">
                        <?php foreach ($classes as $class): ?>
                            <div style="background:linear-gradient(135deg,rgba(0,191,255,0.05),rgba(138,43,226,0.05));border:1px solid rgba(0,191,255,0.2);border-radius:12px;padding:15px;display:flex;justify-content:space-between;align-items:center;">
                                <div>
                                    <h4 style="color:var(--cyber-cyan);margin:0 0 5px 0;"><?php echo htmlspecialchars($class['name']); ?></h4>
                                    <div style="color:rgba(0,191,255,0.6);font-size:0.85rem;"><?php echo htmlspecialchars($class['class_code']); ?></div>
                                </div>
                                <div style="text-align:right;">
                                    <span class="cyber-badge cyan"><?php echo $class['grade_level']; ?> Level</span>
                                    <div style="color:var(--text-muted);font-size:0.85rem;margin-top:5px;">Room: <?php echo htmlspecialchars($class['room_number'] ?? 'TBA'); ?></div>
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