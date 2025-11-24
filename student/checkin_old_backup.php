<?php

/**
 * Student Self Check-in Page
 */

session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

$message = '';
$message_type = '';
$student = null;
$classes = [];
$today_attendance = [];

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $student_id = sanitize($_POST['student_id']);

    // Validate student ID format (should start with STU)
    if (!preg_match('/^STU\d{8}$/', $student_id)) {
        $message = 'Invalid Student ID format! Please enter a valid ID (e.g., STU20250001)';
        $message_type = 'error';
    } else {
        // Query students table joined with users table
        $student = db()->fetchOne("
            SELECT s.*, u.first_name, u.last_name, u.email, u.id as user_id
            FROM students s
            JOIN users u ON s.user_id = u.id
            WHERE s.student_id = ? AND s.status = 'active' AND u.status = 'active'
        ", [$student_id]);

        if ($student) {
            $_SESSION['student_data'] = $student;
            $message = 'Welcome, ' . htmlspecialchars($student['first_name']) . '!';
            $message_type = 'success';
        } else {
            $message = 'Student ID not found or account is inactive. Please check your ID or contact administration.';
            $message_type = 'error';
        }
    }
}

// Handle check-in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkin'])) {
    $class_id = (int)$_POST['class_id'];
    $student = $_SESSION['student_data'];

    // Check if already checked in today
    $existing = db()->fetchOne("
        SELECT id FROM attendance_records
        WHERE student_id = ? AND class_id = ? AND DATE(check_in_time) = CURDATE()
    ", [$student['user_id'], $class_id]);

    if (!$existing) {
        db()->insert('attendance_records', [
            'student_id' => $student['user_id'],
            'class_id' => $class_id,
            'check_in_time' => date('Y-m-d H:i:s'),
            'status' => 'present',
            'marked_by' => $student['user_id']
        ]);

        $message = 'Attendance recorded successfully!';
        $message_type = 'success';
    } else {
        $message = 'You have already checked in for this class today!';
        $message_type = 'error';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    unset($_SESSION['student_data']);
    $student = null;
    $message = 'Logged out successfully!';
    $message_type = 'success';
}

// Get student data from session
if (isset($_SESSION['student_data'])) {
    $student = $_SESSION['student_data'];

    // Get enrolled classes
    $classes = db()->fetchAll("
        SELECT c.*, COUNT(ar.id) as attendance_count
        FROM classes c
        JOIN class_enrollments ce ON c.id = ce.class_id
        LEFT JOIN attendance_records ar ON c.id = ar.class_id AND ar.student_id = ?
        WHERE ce.student_id = ?
        GROUP BY c.id
        ORDER BY c.class_name
    ", [$student['user_id'], $student['user_id']]);

    // Get today's attendance
    $today_attendance = db()->fetchAll("
        SELECT ar.*, c.class_name, c.class_code
        FROM attendance_records ar
        JOIN classes c ON ar.class_id = c.id
        WHERE ar.student_id = ? AND DATE(ar.check_in_time) = CURDATE()
        ORDER BY ar.check_in_time DESC
    ", [$student['user_id']]);
}

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
    <title>Student Check-in - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .clock-widget {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4);
        }

        .clock-time {
            font-size: 56px;
            font-weight: 700;
            margin-bottom: 10px;
            font-family: 'Courier New', monospace;
        }

        .clock-date {
            font-size: 20px;
            opacity: 0.9;
        }

        .class-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 15px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .class-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }

        .class-card.checked-in {
            border-color: #10b981;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }

        .checkin-btn {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .checkin-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
        }

        .checkin-btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }
    </style>
</head>

<body class="cyber-bg">
    <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="header">
        <div class="header-content">
            <div>
                <h1><i class="fas fa-check-circle"></i> Student Check-in</h1>
                <p><?php echo APP_NAME; ?></p>
            </div>
            <?php if ($student): ?>
                <div style="display: flex; gap: 10px;">
                    <a href="../index.php" class="btn" style="background: #64748b; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="?logout=1" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!$student): ?>
            <!-- Login Form -->
            <div class="card" style="max-width: 500px; margin: 50px auto;">
                <div class="card-header">
                    <h2><i class="fas fa-id-card"></i> Student Check-in</h2>
                    <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.8;">Enter your Student ID to check into classes</p>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="student_id">
                            <i class="fas fa-id-badge"></i> Student ID
                        </label>
                        <input type="text" id="student_id" name="student_id" required autofocus
                            placeholder="STU000001" maxlength="9" pattern="STU[0-9]{6}"
                            style="font-size: 18px; padding: 15px; text-transform: uppercase;"
                            title="Student ID format: STU followed by 6 digits">
                        <small style="color: #64748b; font-size: 12px; margin-top: 5px; display: block;">
                            <i class="fas fa-info-circle"></i> Format: STU followed by 6 digits (e.g., STU000001)
                        </small>
                    </div>

                    <button type="submit" name="login" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 18px; margin-bottom: 15px;">
                        <i class="fas fa-sign-in-alt"></i> Check In
                    </button>

                    <a href="../index.php" class="btn" style="width: 100%; padding: 15px; font-size: 16px; background: #64748b; color: white; text-decoration: none; display: inline-block; text-align: center; border-radius: 8px;">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </form>

                <div style="margin-top: 20px; padding: 15px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #3b82f6;">
                    <h4 style="margin: 0 0 10px 0; color: #1e40af;"><i class="fas fa-question-circle"></i> Don't have a Student ID?</h4>
                    <p style="margin: 0; font-size: 14px; color: #64748b;">Contact the school administration or register for an account first.</p>
                    <a href="../register.php" style="color: #3b82f6; text-decoration: none; font-weight: 600;">
                        <i class="fas fa-user-plus"></i> Register New Account
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Clock Widget -->
            <div class="clock-widget">
                <div class="clock-time" id="clock"></div>
                <div class="clock-date" id="date"></div>
                <div style="margin-top: 20px; font-size: 24px;">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                </div>
                <div style="opacity: 0.8; margin-top: 5px;">
                    <?php echo htmlspecialchars($student['student_id']); ?> • Grade <?php echo $student['grade_level']; ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <!-- Available Classes -->
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-book"></i> Your Classes</h2>
                            <span class="badge badge-info"><?php echo count($classes); ?> Classes</span>
                        </div>

                        <?php if (empty($classes)): ?>
                            <p class="alert alert-warning">
                                <i class="fas fa-info-circle"></i> You are not enrolled in any classes yet.
                            </p>
                        <?php else: ?>
                            <?php foreach ($classes as $class): ?>
                                <?php
                                $checked_in = false;
                                foreach ($today_attendance as $att) {
                                    if ($att['class_id'] == $class['id']) {
                                        $checked_in = true;
                                        break;
                                    }
                                }
                                ?>
                                <div class="class-card <?php echo $checked_in ? 'checked-in' : ''; ?>">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                        <div>
                                            <h3 style="margin-bottom: 5px;">
                                                <?php echo htmlspecialchars($class['name']); ?>
                                            </h3>
                                            <p style="color: #64748b;">
                                                <i class="fas fa-code"></i> <?php echo htmlspecialchars($class['class_code']); ?> •
                                                <i class="fas fa-door-open"></i> <?php echo htmlspecialchars($class['room_number'] ?: 'N/A'); ?>
                                            </p>
                                            <?php if ($class['schedule']): ?>
                                                <p style="color: #64748b; font-size: 14px;">
                                                    <i class="fas fa-clock"></i> <?php echo htmlspecialchars($class['schedule']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($checked_in): ?>
                                            <span class="badge badge-success" style="font-size: 16px;">
                                                <i class="fas fa-check-double"></i> Checked In
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <form method="POST">
                                        <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                        <button type="submit" name="checkin" class="checkin-btn" <?php echo $checked_in ? 'disabled' : ''; ?>>
                                            <i class="fas fa-<?php echo $checked_in ? 'check' : 'hand-point-right'; ?>"></i>
                                            <?php echo $checked_in ? 'Already Checked In' : 'Check In Now'; ?>
                                        </button>
                                    </form>

                                    <div style="margin-top: 10px; text-align: center; color: #64748b; font-size: 13px;">
                                        <i class="fas fa-chart-line"></i> Total Attendance: <?php echo $class['attendance_count']; ?> days
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Today's Attendance -->
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-calendar-check"></i> Today's Check-ins</h2>
                        </div>

                        <?php if (empty($today_attendance)): ?>
                            <p class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No check-ins yet today.
                            </p>
                        <?php else: ?>
                            <?php foreach ($today_attendance as $att): ?>
                                <div style="padding: 15px; border-bottom: 1px solid #e2e8f0;">
                                    <div style="font-weight: 600; margin-bottom: 5px;">
                                        <i class="fas fa-book"></i> <?php echo htmlspecialchars($att['class_name']); ?>
                                    </div>
                                    <div style="font-size: 12px; color: #64748b;">
                                        <i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($att['check_in_time'])); ?>
                                    </div>
                                    <span class="badge badge-success" style="margin-top: 8px;">
                                        <i class="fas fa-check"></i> <?php echo ucfirst($att['status']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Student ID validation and formatting
        document.getElementById('student_id')?.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');

            // Auto-format: add STU prefix if user starts typing numbers
            if (value.length > 0 && !value.startsWith('STU')) {
                if (/^\d/.test(value)) {
                    value = 'STU' + value;
                }
            }

            // Limit to STU + 6 digits
            if (value.startsWith('STU')) {
                value = 'STU' + value.substring(3).replace(/\D/g, '').substring(0, 6);
            }

            e.target.value = value;

            // Visual feedback
            const isValid = /^STU\d{6}$/.test(value);
            e.target.style.borderColor = value.length === 0 ? '' : (isValid ? '#10b981' : '#ef4444');
        });

        function updateClock() {
            const now = new Date();

            // Update time
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const clockElement = document.getElementById('clock');
            if (clockElement) {
                clockElement.textContent = `${hours}:${minutes}:${seconds}`;
            }

            // Update date
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const dateElement = document.getElementById('date');
            if (dateElement) {
                dateElement.textContent = now.toLocaleDateString('en-US', options);
            }
        }

        // Update clock immediately and then every second
        updateClock();
        setInterval(updateClock, 1000);
    </script>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/pwa-manager.js"></script>
    <script src="../assets/js/pwa-analytics.js"></script>
</body>

</html>