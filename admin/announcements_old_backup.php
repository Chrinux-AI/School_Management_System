<?php

/**
 * School-wide Announcements System
 */

session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

// Require admin access
require_admin('../login.php');

$message = '';
$message_type = '';

// Create announcements table if not exists
try {
    db()->query("
        CREATE TABLE IF NOT EXISTS announcements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            type ENUM('general', 'urgent', 'event', 'academic', 'administrative') DEFAULT 'general',
            target_audience JSON,
            priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
            start_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            end_date DATETIME NULL,
            is_active BOOLEAN DEFAULT TRUE,
            is_pinned BOOLEAN DEFAULT FALSE,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id)
        )
    ");

    db()->query("
        CREATE TABLE IF NOT EXISTS announcement_views (
            id INT AUTO_INCREMENT PRIMARY KEY,
            announcement_id INT,
            user_id INT,
            viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id),
            UNIQUE KEY unique_view (announcement_id, user_id)
        )
    ");
} catch (Exception $e) {
    error_log("Announcements tables creation error: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_announcement']) && in_array($_SESSION['role'], ['admin', 'teacher'])) {
        $title = sanitize($_POST['title']);
        $content = sanitize($_POST['content']);
        $type = sanitize($_POST['type']);
        $priority = sanitize($_POST['priority']);
        $target_audience = json_encode($_POST['target_audience'] ?? []);
        $start_date = sanitize($_POST['start_date']);
        $end_date = !empty($_POST['end_date']) ? sanitize($_POST['end_date']) : null;
        $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;

        try {
            $announcement_id = db()->insert('announcements', [
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'target_audience' => $target_audience,
                'priority' => $priority,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'is_pinned' => $is_pinned,
                'created_by' => $_SESSION['user_id']
            ]);

            // Send email notifications for critical announcements
            if ($priority === 'critical') {
                $target_roles = json_decode($target_audience, true);
                if (empty($target_roles)) {
                    $target_roles = ['admin', 'teacher', 'student', 'parent'];
                }

                $users = db()->fetchAll("
                    SELECT email, first_name, last_name
                    FROM users
                    WHERE role IN ('" . implode("','", $target_roles) . "')
                    AND status = 'active'
                ");

                foreach ($users as $user) {
                    send_email(
                        $user['email'],
                        "CRITICAL ANNOUNCEMENT: $title",
                        "<h2 style='color: #ef4444;'>CRITICAL SCHOOL ANNOUNCEMENT</h2>
                         <h3>$title</h3>
                         <div style='background: #fee2e2; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                             $content
                         </div>
                         <p><em>This is a critical announcement from " . APP_NAME . ". Please take immediate action if required.</em></p>"
                    );
                }
            }

            $message = 'Announcement created successfully!' .
                ($priority === 'critical' ? ' Email notifications have been sent to target audience.' : '');
            $message_type = 'success';

            log_activity($_SESSION['user_id'], 'create_announcement', 'announcements', $announcement_id);
        } catch (Exception $e) {
            $message = 'Error creating announcement: ' . $e->getMessage();
            $message_type = 'error';
        }
    }

    if (isset($_POST['toggle_announcement']) && $_SESSION['role'] === 'admin') {
        $announcement_id = (int)$_POST['announcement_id'];
        $is_active = (int)$_POST['is_active'];

        try {
            db()->update('announcements', [
                'is_active' => $is_active
            ], 'id = ?', [$announcement_id]);

            $message = 'Announcement ' . ($is_active ? 'activated' : 'deactivated') . ' successfully!';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Error updating announcement: ' . $e->getMessage();
            $message_type = 'error';
        }
    }

    if (isset($_POST['mark_viewed'])) {
        $announcement_id = (int)$_POST['announcement_id'];

        try {
            db()->query("
                INSERT IGNORE INTO announcement_views (announcement_id, user_id)
                VALUES (?, ?)
            ", [$announcement_id, $_SESSION['user_id']]);
        } catch (Exception $e) {
            // Ignore errors for duplicate views
        }
    }
}

// Get announcements based on user role and target audience
$user_role = $_SESSION['role'];

if ($user_role === 'admin') {
    // Admins see all announcements
    $announcements = db()->fetchAll("
        SELECT a.*,
               CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
               u.role as creator_role,
               (SELECT COUNT(*) FROM announcement_views av WHERE av.announcement_id = a.id) as view_count,
               (SELECT COUNT(*) FROM announcement_views av WHERE av.announcement_id = a.id AND av.user_id = ?) as user_viewed
        FROM announcements a
        JOIN users u ON a.created_by = u.id
        WHERE a.is_active = 1
        AND (a.end_date IS NULL OR a.end_date > NOW())
        ORDER BY a.is_pinned DESC, a.priority DESC, a.created_at DESC
    ", [$_SESSION['user_id']]);
} else {
    // Other users see announcements targeted to them
    $announcements = db()->fetchAll("
        SELECT a.*,
               CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
               u.role as creator_role,
               (SELECT COUNT(*) FROM announcement_views av WHERE av.announcement_id = a.id AND av.user_id = ?) as user_viewed
        FROM announcements a
        JOIN users u ON a.created_by = u.id
        WHERE a.is_active = 1
        AND (a.end_date IS NULL OR a.end_date > NOW())
        AND (JSON_CONTAINS(a.target_audience, '\"$user_role\"') OR JSON_LENGTH(a.target_audience) = 0)
        ORDER BY a.is_pinned DESC, a.priority DESC, a.created_at DESC
    ", [$_SESSION['user_id']]);
}

// Get statistics
$stats = [
    'total' => count($announcements),
    'unread' => count(array_filter($announcements, fn($a) => $a['user_viewed'] == 0)),
    'pinned' => count(array_filter($announcements, fn($a) => $a['is_pinned'] == 1)),
    'critical' => count(array_filter($announcements, fn($a) => $a['priority'] === 'critical'))
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
    <title>School Announcements - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/advanced-ui.css">
    <style>
        .announcements-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin: 25px 0;
        }

        .announcement-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-left: 5px solid;
            transition: all 0.3s ease;
            position: relative;
        }

        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .announcement-card.unread {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-left-color: #3b82f6;
        }

        .announcement-card.critical {
            border-left-color: #ef4444;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            animation: pulse 3s infinite;
        }

        .announcement-card.high {
            border-left-color: #f59e0b;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }

        .announcement-card.medium {
            border-left-color: #3b82f6;
        }

        .announcement-card.low {
            border-left-color: #10b981;
        }

        .announcement-card.pinned {
            border: 2px solid #f59e0b;
            background: linear-gradient(135deg, #fefbf2 0%, #fef3c7 100%);
        }

        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }

        .announcement-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .announcement-meta {
            display: flex;
            gap: 15px;
            align-items: center;
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 15px;
        }

        .announcement-content {
            line-height: 1.6;
            color: #374151;
            margin-bottom: 20px;
        }

        .announcement-badges {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .type-badge,
        .priority-badge,
        .pinned-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .type-general {
            background: #f1f5f9;
            color: #475569;
        }

        .type-urgent {
            background: #fee2e2;
            color: #991b1b;
        }

        .type-event {
            background: #e0e7ff;
            color: #3730a3;
        }

        .type-academic {
            background: #dcfce7;
            color: #166534;
        }

        .type-administrative {
            background: #fef3c7;
            color: #92400e;
        }

        .priority-critical {
            background: #ef4444;
            color: white;
            animation: pulse 2s infinite;
        }

        .priority-high {
            background: #f59e0b;
            color: white;
        }

        .priority-medium {
            background: #3b82f6;
            color: white;
        }

        .priority-low {
            background: #10b981;
            color: white;
        }

        .pinned-badge {
            background: #f59e0b;
            color: white;
        }

        .unread-indicator {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 12px;
            height: 12px;
            background: #ef4444;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .announcement-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
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
            max-width: 800px;
            border-radius: 15px;
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        .audience-checkboxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
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
                <h1><i class="fas fa-bullhorn"></i> School Announcements</h1>
                <p>Stay informed with the latest school news and updates</p>
            </div>
            <a href="../logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="container">
        <nav class="nav-menu">
            <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="timetable.php"><i class="fas fa-calendar-alt"></i> Timetable</a>
            <a href="communication.php"><i class="fas fa-comments"></i> Communication</a>
            <a href="facilities.php"><i class="fas fa-building"></i> Facilities</a>
            <a href="announcements.php" class="active"><i class="fas fa-bullhorn"></i> Announcements</a>
        </nav>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <?php if (in_array($_SESSION['role'], ['admin', 'teacher'])): ?>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="stat-details">
                        <button onclick="showAnnouncementModal()" class="btn btn-primary">
                            <i class="fas fa-bullhorn"></i> New Announcement
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Total Active</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['unread']; ?></h3>
                    <p>Unread</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="fas fa-thumbtack"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['pinned']; ?></h3>
                    <p>Pinned</p>
                </div>
            </div>
        </div>

        <!-- Announcements List -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-newspaper"></i> Latest Announcements</h2>
                <?php if ($stats['unread'] > 0): ?>
                    <button onclick="markAllRead()" class="btn btn-secondary btn-sm">
                        <i class="fas fa-check-double"></i> Mark All Read
                    </button>
                <?php endif; ?>
            </div>

            <?php if (empty($announcements)): ?>
                <div style="text-align: center; padding: 60px; color: #64748b;">
                    <i class="fas fa-bullhorn fa-3x" style="margin-bottom: 20px; opacity: 0.5;"></i>
                    <h3>No announcements</h3>
                    <p>Check back later for important school updates!</p>
                </div>
            <?php else: ?>
                <div class="announcements-grid">
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="announcement-card <?php echo $announcement['priority']; ?>
                                    <?php echo $announcement['user_viewed'] == 0 ? 'unread' : ''; ?>
                                    <?php echo $announcement['is_pinned'] ? 'pinned' : ''; ?>"
                            onclick="markAsViewed(<?php echo $announcement['id']; ?>)">

                            <?php if ($announcement['user_viewed'] == 0): ?>
                                <div class="unread-indicator"></div>
                            <?php endif; ?>

                            <?php if ($announcement['is_pinned']): ?>
                                <div style="position: absolute; top: 20px; left: 20px;">
                                    <span class="pinned-badge">
                                        <i class="fas fa-thumbtack"></i> Pinned
                                    </span>
                                </div>
                            <?php endif; ?>

                            <div class="announcement-header">
                                <div style="flex: 1; <?php echo $announcement['is_pinned'] ? 'margin-top: 30px;' : ''; ?>">
                                    <div class="announcement-title">
                                        <?php echo htmlspecialchars($announcement['title']); ?>
                                    </div>

                                    <div class="announcement-meta">
                                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($announcement['created_by_name']); ?></span>
                                        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y g:i A', strtotime($announcement['created_at'])); ?></span>
                                        <?php if (isset($announcement['view_count'])): ?>
                                            <span><i class="fas fa-eye"></i> <?php echo $announcement['view_count']; ?> views</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="announcement-badges">
                                <span class="type-badge type-<?php echo $announcement['type']; ?>">
                                    <?php echo ucfirst($announcement['type']); ?>
                                </span>
                                <span class="priority-badge priority-<?php echo $announcement['priority']; ?>">
                                    <?php echo ucfirst($announcement['priority']); ?>
                                </span>
                            </div>

                            <div class="announcement-content">
                                <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                            </div>

                            <?php if ($announcement['end_date']): ?>
                                <div style="font-size: 0.9rem; color: #64748b; margin-top: 15px;">
                                    <i class="fas fa-clock"></i>
                                    <strong>Valid until:</strong> <?php echo date('M d, Y g:i A', strtotime($announcement['end_date'])); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <div class="announcement-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="announcement_id" value="<?php echo $announcement['id']; ?>">
                                        <input type="hidden" name="is_active" value="0">
                                        <button type="submit" name="toggle_announcement" class="btn btn-danger btn-sm"
                                            onclick="event.stopPropagation(); return confirm('Deactivate this announcement?')">
                                            <i class="fas fa-eye-slash"></i> Deactivate
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- New Announcement Modal -->
    <?php if (in_array($_SESSION['role'], ['admin', 'teacher'])): ?>
        <div id="announcementModal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 25px;">
                    <i class="fas fa-bullhorn"></i> Create New Announcement
                </h3>

                <form method="POST">
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" name="title" id="title" required
                            placeholder="Important school announcement">
                    </div>

                    <div class="form-group">
                        <label for="content">Content *</label>
                        <textarea name="content" id="content" rows="6" required
                            placeholder="Write your announcement content here..."></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select name="type" id="type">
                                <option value="general">General</option>
                                <option value="urgent">Urgent</option>
                                <option value="event">Event</option>
                                <option value="academic">Academic</option>
                                <option value="administrative">Administrative</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="priority">Priority</label>
                            <select name="priority" id="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_pinned" value="1"> Pin to top
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Target Audience (leave empty for all users)</label>
                        <div class="audience-checkboxes">
                            <div class="checkbox-item">
                                <input type="checkbox" name="target_audience[]" value="admin" id="target_admin">
                                <label for="target_admin">Administrators</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="target_audience[]" value="teacher" id="target_teacher">
                                <label for="target_teacher">Teachers</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="target_audience[]" value="student" id="target_student">
                                <label for="target_student">Students</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="target_audience[]" value="parent" id="target_parent">
                                <label for="target_parent">Parents</label>
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="start_date">Start Date & Time</label>
                            <input type="datetime-local" name="start_date" id="start_date"
                                value="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="end_date">End Date & Time (Optional)</label>
                            <input type="datetime-local" name="end_date" id="end_date">
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; margin-top: 25px;">
                        <button type="submit" name="create_announcement" class="btn btn-primary">
                            <i class="fas fa-bullhorn"></i> Publish Announcement
                        </button>
                        <button type="button" onclick="closeAnnouncementModal()" class="btn btn-secondary">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function showAnnouncementModal() {
            document.getElementById('announcementModal').style.display = 'block';
        }

        function closeAnnouncementModal() {
            document.getElementById('announcementModal').style.display = 'none';
        }

        function markAsViewed(announcementId) {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `mark_viewed=1&announcement_id=${announcementId}`
            }).then(() => {
                // Remove unread indicator
                const card = event.currentTarget;
                const indicator = card.querySelector('.unread-indicator');
                if (indicator) {
                    indicator.remove();
                }
                card.classList.remove('unread');
            });
        }

        function markAllRead() {
            const unreadCards = document.querySelectorAll('.announcement-card.unread');
            unreadCards.forEach(card => {
                const announcementId = card.onclick.toString().match(/markAsViewed\((\d+)\)/)[1];
                markAsViewed(announcementId);
            });

            // Refresh page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('announcementModal');
            if (event.target === modal) {
                closeAnnouncementModal();
            }
        }

        // Update end date minimum based on start date
        document.getElementById('start_date')?.addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    </script>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/pwa-manager.js"></script>
    <script src="../assets/js/pwa-analytics.js"></script>
</body>

</html>