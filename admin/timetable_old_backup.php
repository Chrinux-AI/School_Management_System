<?php

/**
 * Advanced Timetable Management System
 */

session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

// Require admin access
require_admin('../login.php');

$message = '';
$message_type = '';

// Create timetables table if not exists
try {
    db()->query("
        CREATE TABLE IF NOT EXISTS timetables (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            class_id INT,
            teacher_id INT,
            subject VARCHAR(100),
            day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
            start_time TIME,
            end_time TIME,
            room_number VARCHAR(50),
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_by INT,
            approved_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id),
            FOREIGN KEY (approved_by) REFERENCES users(id)
        )
    ");
} catch (Exception $e) {
    error_log("Timetable table creation error: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_timetable'])) {
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $class_id = (int)$_POST['class_id'];
        $teacher_id = (int)$_POST['teacher_id'];
        $subject = sanitize($_POST['subject']);
        $day_of_week = sanitize($_POST['day_of_week']);
        $start_time = sanitize($_POST['start_time']);
        $end_time = sanitize($_POST['end_time']);
        $room_number = sanitize($_POST['room_number']);

        try {
            $data = [
                'title' => $title,
                'description' => $description,
                'class_id' => $class_id,
                'teacher_id' => $teacher_id,
                'subject' => $subject,
                'day_of_week' => $day_of_week,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'room_number' => $room_number,
                'created_by' => $_SESSION['user_id'],
                'status' => $_SESSION['role'] === 'admin' ? 'approved' : 'pending'
            ];

            db()->insert('timetables', $data);

            $message = $_SESSION['role'] === 'admin'
                ? 'Timetable created and approved successfully!'
                : 'Timetable created and submitted for approval!';
            $message_type = 'success';

            log_activity($_SESSION['user_id'], 'create_timetable', 'timetables', db()->lastInsertId());
        } catch (Exception $e) {
            $message = 'Error creating timetable: ' . $e->getMessage();
            $message_type = 'error';
        }
    }

    if (isset($_POST['approve_timetable']) && $_SESSION['role'] === 'admin') {
        $timetable_id = (int)$_POST['timetable_id'];

        try {
            db()->update('timetables', [
                'status' => 'approved',
                'approved_by' => $_SESSION['user_id']
            ], 'id = ?', [$timetable_id]);

            $message = 'Timetable approved successfully!';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Error approving timetable: ' . $e->getMessage();
            $message_type = 'error';
        }
    }

    if (isset($_POST['reject_timetable']) && $_SESSION['role'] === 'admin') {
        $timetable_id = (int)$_POST['timetable_id'];

        try {
            db()->update('timetables', [
                'status' => 'rejected'
            ], 'id = ?', [$timetable_id]);

            $message = 'Timetable rejected!';
            $message_type = 'warning';
        } catch (Exception $e) {
            $message = 'Error rejecting timetable: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Get timetables based on user role
$where_clause = $_SESSION['role'] === 'admin' ? '' : 'WHERE created_by = ' . $_SESSION['user_id'];
$timetables = db()->fetchAll("
    SELECT t.*,
           CASE WHEN c.name IS NOT NULL THEN c.name ELSE 'No Class' END as class_name,
           CASE WHEN u.first_name IS NOT NULL THEN CONCAT(u.first_name, ' ', u.last_name) ELSE 'No Teacher' END as teacher_name,
           CONCAT(creator.first_name, ' ', creator.last_name) as created_by_name
    FROM timetables t
    LEFT JOIN classes c ON t.class_id = c.id
    LEFT JOIN users u ON t.teacher_id = u.id
    LEFT JOIN users creator ON t.created_by = creator.id
    $where_clause
    ORDER BY t.day_of_week, t.start_time
");

// Get classes and teachers for form
$classes = db()->fetchAll("SELECT id, name FROM classes ORDER BY name");
$teachers = db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE role = 'teacher' ORDER BY first_name");

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
    <title>Timetable Management - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/advanced-ui.css">
    <style>
        .timetable-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 15px;
            margin: 25px 0;
        }

        .day-column {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            min-height: 400px;
        }

        .day-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .time-slot {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #3b82f6;
            transition: all 0.3s ease;
        }

        .time-slot:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
        }

        .time-slot.pending {
            border-left-color: #f59e0b;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }

        .time-slot.approved {
            border-left-color: #10b981;
        }

        .time-slot.rejected {
            border-left-color: #ef4444;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            width: 90%;
            max-width: 600px;
            border-radius: 15px;
            max-height: 90vh;
            overflow-y: auto;
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
                <h1><i class="fas fa-calendar-alt"></i> Timetable Management</h1>
                <p>Create, manage and approve class schedules</p>
            </div>
            <a href="../logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="container">
        <nav class="nav-menu">
            <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="users.php"><i class="fas fa-users"></i> Users</a>
            <a href="timetable.php" class="active"><i class="fas fa-calendar-alt"></i> Timetable</a>
            <a href="communication.php"><i class="fas fa-comments"></i> Communication</a>
            <a href="facilities.php"><i class="fas fa-building"></i> Facilities</a>
            <a href="announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a>
            <a href="analytics.php"><i class="fas fa-brain"></i> AI Analytics</a>
            <a href="system-monitor.php"><i class="fas fa-heartbeat"></i> Monitor</a>
        </nav>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="stat-details">
                    <button onclick="showAddModal()" class="btn btn-primary">Add Timetable</button>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count(array_filter($timetables, fn($t) => $t['status'] === 'approved')); ?></h3>
                    <p>Approved</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count(array_filter($timetables, fn($t) => $t['status'] === 'pending')); ?></h3>
                    <p>Pending</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count(array_filter($timetables, fn($t) => $t['status'] === 'rejected')); ?></h3>
                    <p>Rejected</p>
                </div>
            </div>
        </div>

        <!-- Weekly Timetable Grid -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-table"></i> Weekly Timetable</h2>
            </div>

            <div class="timetable-grid">
                <?php
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                foreach ($days as $day):
                    $day_timetables = array_filter($timetables, fn($t) => $t['day_of_week'] === $day);
                ?>
                    <div class="day-column">
                        <div class="day-header">
                            <?php echo $day; ?>
                        </div>

                        <?php if (empty($day_timetables)): ?>
                            <div style="text-align: center; color: #64748b; padding: 20px;">
                                <i class="fas fa-calendar-times fa-2x"></i>
                                <p>No classes scheduled</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($day_timetables as $timetable): ?>
                                <div class="time-slot <?php echo $timetable['status']; ?>">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                        <strong><?php echo htmlspecialchars($timetable['title']); ?></strong>
                                        <span class="badge badge-<?php echo $timetable['status'] === 'approved' ? 'success' : ($timetable['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                            <?php echo ucfirst($timetable['status']); ?>
                                        </span>
                                    </div>

                                    <div style="font-size: 0.9rem; color: #64748b; margin-bottom: 8px;">
                                        <i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($timetable['start_time'])); ?> - <?php echo date('g:i A', strtotime($timetable['end_time'])); ?>
                                    </div>

                                    <div style="font-size: 0.9rem; color: #64748b; margin-bottom: 8px;">
                                        <i class="fas fa-book"></i> <?php echo htmlspecialchars($timetable['subject']); ?>
                                    </div>

                                    <div style="font-size: 0.9rem; color: #64748b; margin-bottom: 8px;">
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($timetable['teacher_name']); ?>
                                    </div>

                                    <div style="font-size: 0.9rem; color: #64748b; margin-bottom: 8px;">
                                        <i class="fas fa-users"></i> <?php echo htmlspecialchars($timetable['class_name']); ?>
                                    </div>

                                    <?php if ($timetable['room_number']): ?>
                                        <div style="font-size: 0.9rem; color: #64748b; margin-bottom: 8px;">
                                            <i class="fas fa-door-open"></i> Room <?php echo htmlspecialchars($timetable['room_number']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($_SESSION['role'] === 'admin' && $timetable['status'] === 'pending'): ?>
                                        <div style="display: flex; gap: 5px; margin-top: 10px;">
                                            <form method="POST" style="flex: 1;">
                                                <input type="hidden" name="timetable_id" value="<?php echo $timetable['id']; ?>">
                                                <button type="submit" name="approve_timetable" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" style="flex: 1;">
                                                <input type="hidden" name="timetable_id" value="<?php echo $timetable['id']; ?>">
                                                <button type="submit" name="reject_timetable" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Add Timetable Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-bottom: 25px;">
                <i class="fas fa-plus-circle"></i> Add New Timetable Entry
            </h3>

            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" name="title" id="title" required placeholder="Math Class A">
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <input type="text" name="subject" id="subject" required placeholder="Mathematics">
                    </div>

                    <div class="form-group">
                        <label for="class_id">Class</label>
                        <select name="class_id" id="class_id">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>">
                                    <?php echo htmlspecialchars($class['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="teacher_id">Teacher</label>
                        <select name="teacher_id" id="teacher_id">
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>">
                                    <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="day_of_week">Day *</label>
                        <select name="day_of_week" id="day_of_week" required>
                            <option value="">Select Day</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="room_number">Room Number</label>
                        <input type="text" name="room_number" id="room_number" placeholder="101">
                    </div>

                    <div class="form-group">
                        <label for="start_time">Start Time *</label>
                        <input type="time" name="start_time" id="start_time" required>
                    </div>

                    <div class="form-group">
                        <label for="end_time">End Time *</label>
                        <input type="time" name="end_time" id="end_time" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="3"
                        placeholder="Additional details about the class..."></textarea>
                </div>

                <div style="display: flex; gap: 15px; margin-top: 25px;">
                    <button type="submit" name="add_timetable" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <?php echo $_SESSION['role'] === 'admin' ? 'Create & Approve' : 'Submit for Approval'; ?>
                    </button>
                    <button type="button" onclick="closeAddModal()" class="btn btn-secondary">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addModal');
            if (event.target === modal) {
                closeAddModal();
            }
        }
    </script>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/pwa-manager.js"></script>
    <script src="../assets/js/pwa-analytics.js"></script>
</body>

</html>