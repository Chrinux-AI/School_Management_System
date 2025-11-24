<?php

/**
 * Settings Page
 */

session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

// Require admin access
require_admin('../login.php');

$message = '';
$message_type = '';

// Get recent activity logs
$logs = db()->fetchAll("
    SELECT al.*, u.username, u.first_name, u.last_name
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 50
");

// Get system stats
$db_stats = db()->fetch("
    SELECT
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM students) as total_students,
        (SELECT COUNT(*) FROM teachers) as total_teachers,
        (SELECT COUNT(*) FROM classes) as total_classes,
        (SELECT COUNT(*) FROM attendance_records) as total_attendance
");

// Ensure we have valid data
if (!$db_stats) {
    $db_stats = [
        'total_users' => 0,
        'total_students' => 0,
        'total_teachers' => 0,
        'total_classes' => 0,
        'total_attendance' => 0
    ];
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
    <title>Settings - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .settings-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .settings-tab {
            padding: 12px 24px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: 600;
            color: #64748b;
            transition: all 0.3s ease;
        }

        .settings-tab:hover {
            color: #667eea;
        }

        .settings-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .log-entry {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .log-entry:hover {
            background: #f8fafc;
            padding-left: 20px;
        }

        .log-entry:last-child {
            border-bottom: none;
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
                <h1><i class="fas fa-cog"></i> <?php echo APP_NAME; ?></h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
            </div>
            <a href="../logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="container">
        <?php include '../includes/cyber-nav.php'; ?>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Settings Tabs -->
        <div class="settings-tabs">
            <button class="settings-tab active" onclick="showTab('system')">
                <i class="fas fa-info-circle"></i> System Info
            </button>
            <button class="settings-tab" onclick="showTab('logs')">
                <i class="fas fa-history"></i> Activity Logs
            </button>
            <button class="settings-tab" onclick="showTab('database')">
                <i class="fas fa-database"></i> Database
            </button>
        </div>

        <!-- System Info Tab -->
        <div id="system" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> System Information</h2>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $db_stats['total_users']; ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $db_stats['total_students']; ?></h3>
                            <p>Total Students</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $db_stats['total_teachers']; ?></h3>
                            <p>Total Teachers</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $db_stats['total_classes']; ?></h3>
                            <p>Total Classes</p>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <h3 style="margin-bottom: 15px;">Server Information</h3>
                    <table>
                        <tr>
                            <td><strong>PHP Version:</strong></td>
                            <td><?php echo PHP_VERSION; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Server Software:</strong></td>
                            <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Database Type:</strong></td>
                            <td>MySQL/MariaDB</td>
                        </tr>
                        <tr>
                            <td><strong>Total Attendance Records:</strong></td>
                            <td><?php echo number_format($db_stats['total_attendance']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Application Version:</strong></td>
                            <td>1.0.0</td>
                        </tr>
                        <tr>
                            <td><strong>Timezone:</strong></td>
                            <td><?php echo date_default_timezone_get(); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Activity Logs Tab -->
        <div id="logs" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-history"></i> Recent Activity Logs</h2>
                    <span class="badge badge-info"><?php echo count($logs); ?> Records</span>
                </div>

                <div style="max-height: 600px; overflow-y: auto;">
                    <?php foreach ($logs as $log): ?>
                        <div class="log-entry">
                            <div>
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                                    <span class="badge badge-<?php
                                                                echo $log['action'] === 'create' ? 'success' : ($log['action'] === 'delete' ? 'danger' : ($log['action'] === 'login' ? 'info' : 'warning'));
                                                                ?>">
                                        <i class="fas fa-<?php
                                                            echo $log['action'] === 'create' ? 'plus' : ($log['action'] === 'delete' ? 'trash' : ($log['action'] === 'login' ? 'sign-in-alt' : 'edit'));
                                                            ?>"></i>
                                        <?php echo ucfirst($log['action']); ?>
                                    </span>
                                    <strong><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></strong>
                                    <span style="color: #64748b;">performed action on</span>
                                    <strong><?php echo ucfirst($log['entity_type']); ?></strong>
                                </div>
                                <p style="font-size: 12px; color: #64748b;">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($log['username']); ?> •
                                    <i class="fas fa-clock"></i> <?php echo format_datetime($log['created_at']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Database Tab -->
        <div id="database" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-database"></i> Database Management</h2>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> Database operations should be performed with caution. Always backup your data before proceeding.
                </div>

                <div style="display: grid; gap: 15px; margin-top: 20px;">
                    <div class="card" style="padding: 20px;">
                        <h3><i class="fas fa-download"></i> Backup Database</h3>
                        <p style="color: #64748b; margin: 10px 0;">Create a complete backup of your database.</p>
                        <button class="btn btn-primary" onclick="alert('Database backup functionality would be implemented here')">
                            <i class="fas fa-download"></i> Create Backup
                        </button>
                    </div>

                    <div class="card" style="padding: 20px;">
                        <h3><i class="fas fa-broom"></i> Clear Old Logs</h3>
                        <p style="color: #64748b; margin: 10px 0;">Remove activity logs older than 30 days.</p>
                        <button class="btn btn-secondary" onclick="if(confirm('Delete old logs?')) alert('Logs would be cleared here')">
                            <i class="fas fa-broom"></i> Clear Logs
                        </button>
                    </div>

                    <div class="card" style="padding: 20px;">
                        <h3><i class="fas fa-chart-line"></i> Optimize Database</h3>
                        <p style="color: #64748b; margin: 10px 0;">Optimize database tables for better performance.</p>
                        <button class="btn btn-success" onclick="alert('Database optimization would be performed here')">
                            <i class="fas fa-magic"></i> Optimize Tables
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Remove active from all buttons
            const buttons = document.querySelectorAll('.settings-tab');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Show selected tab
            document.getElementById(tabName).classList.add('active');

            // Add active to clicked button
            event.target.classList.add('active');
        }
    </script>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/pwa-manager.js"></script>
    <script src="../assets/js/pwa-analytics.js"></script>
</body>

</html>