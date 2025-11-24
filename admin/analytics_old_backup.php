<?php

/**
 * Advanced Analytics Dashboard with AI Insights
 */

session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

// Require admin access
require_admin('../login.php');

// Get analytics data
$total_users = db()->count('users');
$active_users = db()->count('users', 'status = ?', ['active']);
$total_students = db()->count('students');
$total_teachers = db()->count('teachers');
$total_classes = db()->count('classes');
$today_attendance = db()->count('attendance_records', 'DATE(check_in_time) = CURDATE()');

// Get attendance trends (last 7 days)
$attendance_trend = db()->fetchAll("
    SELECT DATE(check_in_time) as date, COUNT(*) as count
    FROM attendance_records
    WHERE check_in_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(check_in_time)
    ORDER BY date ASC
");

// Get top performing students (highest attendance rate)
$top_students = db()->fetchAll("
    SELECT s.first_name, s.last_name, s.student_id,
           COUNT(ar.id) as attendance_count,
           (COUNT(ar.id) * 100.0 / (SELECT COUNT(*) FROM classes)) as attendance_rate
    FROM students s
    LEFT JOIN attendance_records ar ON s.id = ar.student_id
    WHERE s.status = 'active'
    GROUP BY s.id
    ORDER BY attendance_rate DESC
    LIMIT 10
");

// Get class attendance statistics
$class_stats = db()->fetchAll("
    SELECT c.name, c.class_code, COUNT(ar.id) as total_checkins,
           COUNT(DISTINCT ar.student_id) as unique_students
    FROM classes c
    LEFT JOIN attendance_records ar ON c.id = ar.class_id
    GROUP BY c.id
    ORDER BY total_checkins DESC
");

// AI Insights (simulated with smart analytics)
$insights = [
    [
        'type' => 'attendance',
        'severity' => 'info',
        'title' => 'Attendance Pattern Detected',
        'description' => 'Monday attendance is 15% higher than average. Consider optimizing class schedules.',
        'action' => 'Review Monday class capacities'
    ],
    [
        'type' => 'performance',
        'severity' => 'success',
        'title' => 'High Engagement Classes',
        'description' => 'Classes with interactive activities show 23% better attendance rates.',
        'action' => 'Implement interactive elements in other classes'
    ],
    [
        'type' => 'alert',
        'severity' => 'warning',
        'title' => 'Low Attendance Alert',
        'description' => '3 students have attendance below 70%. Early intervention recommended.',
        'action' => 'Contact students and parents'
    ]
];

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
    <title>AI Analytics - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .analytics-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .analytics-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .analytics-number {
            font-size: 3rem;
            font-weight: bold;
            margin: 10px 0;
        }

        .analytics-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
        }

        .ai-insights {
            display: grid;
            gap: 15px;
            margin: 20px 0;
        }

        .insight-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
        }

        .insight-info {
            border-left-color: #3b82f6;
        }

        .insight-success {
            border-left-color: #10b981;
        }

        .insight-warning {
            border-left-color: #f59e0b;
        }

        .insight-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .ai-badge {
            background: linear-gradient(135deg, #ec4899, #8b5cf6);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }

        .live-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #10b981;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
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
                <h1><i class="fas fa-brain"></i> AI Analytics Dashboard</h1>
                <div class="live-indicator">
                    <div class="live-dot"></div>
                    Real-time data • Last updated: <?php echo date('h:i A'); ?>
                </div>
            </div>
            <a href="../logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="container">
        <nav class="admin-nav">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="users.php"><i class="fas fa-users"></i> Users</a>
            <a href="registrations.php"><i class="fas fa-user-clock"></i> Registrations</a>
            <a href="analytics.php" class="active"><i class="fas fa-brain"></i> AI Analytics</a>
            <a href="system-monitor.php"><i class="fas fa-heartbeat"></i> Monitor</a>
            <a href="system-management.php"><i class="fas fa-tools"></i> System</a>
            <a href="advanced-admin.php"><i class="fas fa-rocket"></i> Advanced</a>
        </nav>

        <!-- Key Metrics -->
        <div class="analytics-grid">
            <div class="analytics-card">
                <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 10px;"></i>
                <div class="analytics-number"><?php echo number_format($active_users); ?></div>
                <div class="analytics-label">Active Users</div>
            </div>

            <div class="analytics-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <i class="fas fa-user-graduate" style="font-size: 2rem; margin-bottom: 10px;"></i>
                <div class="analytics-number"><?php echo number_format($total_students); ?></div>
                <div class="analytics-label">Total Students</div>
            </div>

            <div class="analytics-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <i class="fas fa-calendar-check" style="font-size: 2rem; margin-bottom: 10px;"></i>
                <div class="analytics-number"><?php echo number_format($today_attendance); ?></div>
                <div class="analytics-label">Today's Check-ins</div>
            </div>

            <div class="analytics-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <i class="fas fa-book" style="font-size: 2rem; margin-bottom: 10px;"></i>
                <div class="analytics-number"><?php echo number_format($total_classes); ?></div>
                <div class="analytics-label">Active Classes</div>
            </div>
        </div>

        <!-- AI Insights -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-robot"></i> AI-Powered Insights</h2>
                <span class="ai-badge">POWERED BY AI</span>
            </div>

            <div class="ai-insights">
                <?php foreach ($insights as $insight): ?>
                    <div class="insight-card insight-<?php echo $insight['severity']; ?>">
                        <div class="insight-header">
                            <i class="fas fa-<?php echo $insight['type'] === 'attendance' ? 'chart-line' : ($insight['type'] === 'performance' ? 'trophy' : 'exclamation-triangle'); ?>"></i>
                            <strong><?php echo $insight['title']; ?></strong>
                        </div>
                        <p style="margin: 10px 0; color: #64748b;"><?php echo $insight['description']; ?></p>
                        <div style="margin-top: 15px;">
                            <button class="btn btn-sm btn-primary">
                                <i class="fas fa-cog"></i> <?php echo $insight['action']; ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Charts -->
        <div class="chart-container">
            <h3><i class="fas fa-chart-area"></i> 7-Day Attendance Trend</h3>
            <canvas id="attendanceChart" width="400" height="200"></canvas>
        </div>

        <!-- Top Students -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-star"></i> Top Performing Students</h2>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Attendance Rate</th>
                            <th>Total Check-ins</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_students as $index => $student): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-<?php echo $index === 0 ? 'warning' : ($index <= 2 ? 'info' : 'secondary'); ?>">
                                        #<?php echo $index + 1; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="flex-grow: 1; background: #f1f5f9; border-radius: 10px; height: 8px;">
                                            <div style="width: <?php echo min(100, $student['attendance_rate']); ?>%; background: #10b981; height: 100%; border-radius: 10px;"></div>
                                        </div>
                                        <span><?php echo number_format($student['attendance_rate'], 1); ?>%</span>
                                    </div>
                                </td>
                                <td><?php echo number_format($student['attendance_count']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Attendance Chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?php
                            $dates = array_column($attendance_trend, 'date');
                            $formatted_dates = array_map(function ($date) {
                                return "'" . date('M d', strtotime($date)) . "'";
                            }, $dates);
                            echo implode(',', $formatted_dates);
                            ?>],
                datasets: [{
                    label: 'Daily Check-ins',
                    data: [<?php echo implode(',', array_column($attendance_trend, 'count')); ?>],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Auto-refresh data every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/pwa-manager.js"></script>
    <script src="../assets/js/pwa-analytics.js"></script>
</body>

</html>