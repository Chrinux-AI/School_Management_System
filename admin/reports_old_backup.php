<?php

/**
 * Reports Page
 */

session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

// Require admin access
require_admin('../login.php');

// Get filter parameters
$report_type = $_GET['type'] ?? 'summary';
$class_id = $_GET['class_id'] ?? null;
$student_id = $_GET['student_id'] ?? null;
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today

// Get classes for filter
$classes = db()->fetchAll("SELECT id, class_code, name FROM classes ORDER BY name");

// Get students for filter
$students = db()->fetchAll("SELECT id, student_id, first_name, last_name FROM students ORDER BY last_name, first_name");

// Generate report data based on type
$report_data = [];
$stats = [];

if ($report_type === 'summary') {
    // Overall attendance summary
    $stats = db()->fetch("
        SELECT
            COUNT(DISTINCT student_id) as total_students,
            COUNT(id) as total_records,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
            SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
            SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused_count
        FROM attendance_records
        WHERE DATE(check_in_time) BETWEEN ? AND ?
    ", [$date_from, $date_to]);

    $stats['attendance_rate'] = $stats['total_records'] > 0
        ? round(($stats['present_count'] + $stats['late_count'] + $stats['excused_count']) / $stats['total_records'] * 100, 2)
        : 0;
} elseif ($report_type === 'by_class' && $class_id) {
    // Class-specific report
    $report_data = db()->fetchAll("
        SELECT
            s.student_id,
            s.first_name,
            s.last_name,
            COUNT(ar.id) as total_records,
            SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
            SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late_count,
            SUM(CASE WHEN ar.status = 'excused' THEN 1 ELSE 0 END) as excused_count
        FROM students s
        JOIN class_enrollments ce ON s.id = ce.student_id
        LEFT JOIN attendance_records ar ON s.id = ar.student_id AND ar.class_id = ? AND DATE(ar.check_in_time) BETWEEN ? AND ?
        WHERE ce.class_id = ?
        GROUP BY s.id
        ORDER BY s.last_name, s.first_name
    ", [$class_id, $date_from, $date_to, $class_id]);

    foreach ($report_data as &$row) {
        $row['attendance_rate'] = $row['total_records'] > 0
            ? round(($row['present_count'] + $row['late_count'] + $row['excused_count']) / $row['total_records'] * 100, 2)
            : 0;
    }
} elseif ($report_type === 'by_student' && $student_id) {
    // Student-specific report
    $report_data = db()->fetchAll("
        SELECT
            ar.*,
            c.name as class_name,
            c.class_code
        FROM attendance_records ar
        JOIN classes c ON ar.class_id = c.id
        WHERE ar.student_id = ? AND DATE(ar.check_in_time) BETWEEN ? AND ?
        ORDER BY ar.check_in_time DESC
    ", [$student_id, $date_from, $date_to]);

    $student_info = db()->fetch("SELECT * FROM students WHERE id = ?", [$student_id]);
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
    <title>Reports - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .report-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .report-tab {
            padding: 12px 24px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: 600;
            color: #64748b;
            transition: all 0.3s ease;
        }

        .report-tab:hover {
            color: #667eea;
        }

        .report-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .progress-bar {
            background: #e2e8f0;
            border-radius: 10px;
            height: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
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
                <h1><i class="fas fa-chart-bar"></i> <?php echo APP_NAME; ?></h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
            </div>
            <a href="../logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="container">
        <?php include '../includes/cyber-nav.php'; ?>

        <!-- Report Tabs -->
        <div class="report-tabs">
            <a href="?type=summary&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>"
                class="report-tab <?php echo $report_type === 'summary' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> Summary
            </a>
            <a href="?type=by_class&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>"
                class="report-tab <?php echo $report_type === 'by_class' ? 'active' : ''; ?>">
                <i class="fas fa-book"></i> By Class
            </a>
            <a href="?type=by_student&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>"
                class="report-tab <?php echo $report_type === 'by_student' ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i> By Student
            </a>
        </div>

        <!-- Filters Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-filter"></i> Filters</h2>
            </div>

            <form method="GET" class="form-grid">
                <input type="hidden" name="type" value="<?php echo $report_type; ?>">

                <?php if ($report_type === 'by_class'): ?>
                    <div class="form-group">
                        <label for="class_id">
                            <i class="fas fa-book"></i> Select Class
                        </label>
                        <select id="class_id" name="class_id" required>
                            <option value="">Choose a class...</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo $class_id == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['name'] . ' (' . $class['class_code'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if ($report_type === 'by_student'): ?>
                    <div class="form-group">
                        <label for="student_id">
                            <i class="fas fa-user-graduate"></i> Select Student
                        </label>
                        <select id="student_id" name="student_id" required>
                            <option value="">Choose a student...</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>" <?php echo $student_id == $student['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="date_from">
                        <i class="fas fa-calendar"></i> From Date
                    </label>
                    <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>" required>
                </div>

                <div class="form-group">
                    <label for="date_to">
                        <i class="fas fa-calendar"></i> To Date
                    </label>
                    <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>

        <?php if ($report_type === 'summary'): ?>
            <!-- Summary Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['present_count']; ?></h3>
                        <p>Present</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['absent_count']; ?></h3>
                        <p>Absent</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['late_count']; ?></h3>
                        <p>Late</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['attendance_rate']; ?>%</h3>
                        <p>Attendance Rate</p>
                    </div>
                </div>
            </div>

        <?php elseif ($report_type === 'by_class' && !empty($report_data)): ?>
            <!-- Class Report -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-list"></i> Class Attendance Report</h2>
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Late</th>
                                <th>Excused</th>
                                <th>Total</th>
                                <th>Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_data as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><span class="badge badge-success"><?php echo $row['present_count']; ?></span></td>
                                    <td><span class="badge badge-danger"><?php echo $row['absent_count']; ?></span></td>
                                    <td><span class="badge badge-warning"><?php echo $row['late_count']; ?></span></td>
                                    <td><span class="badge badge-info"><?php echo $row['excused_count']; ?></span></td>
                                    <td><strong><?php echo $row['total_records']; ?></strong></td>
                                    <td>
                                        <div style="min-width: 100px;">
                                            <div><?php echo $row['attendance_rate']; ?>%</div>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo $row['attendance_rate']; ?>%;"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($report_type === 'by_student' && !empty($report_data)): ?>
            <!-- Student Report -->
            <?php if (isset($student_info)): ?>
                <div class="card">
                    <h3>Student Information</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($student_info['first_name'] . ' ' . $student_info['last_name']); ?></p>
                    <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student_info['student_id']); ?></p>
                    <p><strong>Grade:</strong> <?php echo $student_info['grade_level']; ?></p>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-list"></i> Attendance History</h2>
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Class</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_data as $record): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($record['check_in_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($record['class_name'] . ' (' . $record['class_code'] . ')'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php
                                                                    echo $record['status'] === 'present' ? 'success' : ($record['status'] === 'absent' ? 'danger' : ($record['status'] === 'late' ? 'warning' : 'info'));
                                                                    ?>">
                                            <?php echo ucfirst($record['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('h:i A', strtotime($record['check_in_time'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif (($report_type === 'by_class' && $class_id) || ($report_type === 'by_student' && $student_id)): ?>
            <div class="card">
                <p class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No attendance records found for the selected criteria.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/pwa-manager.js"></script>
    <script src="../assets/js/pwa-analytics.js"></script>
</body>

</html>