<?php

/**
 * Facilities Management System
 */

session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

// Require admin access
require_admin('../login.php');

$message = '';
$message_type = '';

// Create facilities tables if not exists
try {
    db()->query("
        CREATE TABLE IF NOT EXISTS facility_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(50) DEFAULT 'building',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    db()->query("
        CREATE TABLE IF NOT EXISTS facility_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            category_id INT,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
            status ENUM('pending', 'in_progress', 'completed', 'rejected') DEFAULT 'pending',
            requested_date DATE,
            expected_completion DATE,
            admin_notes TEXT,
            assigned_to INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (category_id) REFERENCES facility_categories(id),
            FOREIGN KEY (assigned_to) REFERENCES users(id)
        )
    ");

    db()->query("
        CREATE TABLE IF NOT EXISTS facility_updates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            request_id INT,
            user_id INT,
            update_type ENUM('comment', 'status_change', 'assignment') DEFAULT 'comment',
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (request_id) REFERENCES facility_requests(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    // Insert default categories if they don't exist
    $existing_categories = db()->count('facility_categories');
    if ($existing_categories === 0) {
        $default_categories = [
            ['Classroom Equipment', 'Projectors, whiteboards, desks, chairs', 'chalkboard'],
            ['Technology Support', 'Computers, internet, software issues', 'laptop'],
            ['Maintenance', 'Repairs, cleaning, general maintenance', 'tools'],
            ['Sports Facilities', 'Gym equipment, sports fields, courts', 'dumbbell'],
            ['Library Resources', 'Books, study spaces, research materials', 'book'],
            ['Laboratory Equipment', 'Science lab, computer lab equipment', 'flask'],
            ['Security & Safety', 'Locks, lighting, safety equipment', 'shield-alt'],
            ['Transportation', 'Bus services, parking facilities', 'bus'],
            ['Other', 'Miscellaneous facility requests', 'question-circle']
        ];

        foreach ($default_categories as $category) {
            db()->insert('facility_categories', [
                'name' => $category[0],
                'description' => $category[1],
                'icon' => $category[2]
            ]);
        }
    }
} catch (Exception $e) {
    error_log("Facilities tables creation error: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_request'])) {
        $category_id = (int)$_POST['category_id'];
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $priority = sanitize($_POST['priority']);
        $requested_date = sanitize($_POST['requested_date']);

        try {
            $request_id = db()->insert('facility_requests', [
                'user_id' => $_SESSION['user_id'],
                'category_id' => $category_id,
                'title' => $title,
                'description' => $description,
                'priority' => $priority,
                'requested_date' => $requested_date
            ]);

            // Add initial update
            db()->insert('facility_updates', [
                'request_id' => $request_id,
                'user_id' => $_SESSION['user_id'],
                'update_type' => 'comment',
                'message' => 'Facility request submitted.'
            ]);

            // Send notification to admins
            $admins = db()->fetchAll("SELECT email, first_name, last_name FROM users WHERE role = 'admin'");
            foreach ($admins as $admin) {
                send_email(
                    $admin['email'],
                    "New Facility Request: $title",
                    "A new facility request has been submitted by " . $_SESSION['full_name'] . ".<br><br>
                     <strong>Title:</strong> $title<br>
                     <strong>Priority:</strong> " . ucfirst($priority) . "<br>
                     <strong>Description:</strong> $description<br><br>
                     Please review and respond to this request."
                );
            }

            $message = 'Facility request submitted successfully! Administrators have been notified.';
            $message_type = 'success';

            log_activity($_SESSION['user_id'], 'submit_facility_request', 'facility_requests', $request_id);
        } catch (Exception $e) {
            $message = 'Error submitting request: ' . $e->getMessage();
            $message_type = 'error';
        }
    }

    if (isset($_POST['update_status']) && $_SESSION['role'] === 'admin') {
        $request_id = (int)$_POST['request_id'];
        $status = sanitize($_POST['status']);
        $admin_notes = sanitize($_POST['admin_notes']);
        $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;

        try {
            $old_request = db()->fetchRow("SELECT status FROM facility_requests WHERE id = ?", [$request_id]);

            db()->update('facility_requests', [
                'status' => $status,
                'admin_notes' => $admin_notes,
                'assigned_to' => $assigned_to
            ], 'id = ?', [$request_id]);

            // Add status update
            if ($old_request['status'] !== $status) {
                db()->insert('facility_updates', [
                    'request_id' => $request_id,
                    'user_id' => $_SESSION['user_id'],
                    'update_type' => 'status_change',
                    'message' => "Status changed from '" . $old_request['status'] . "' to '$status'. " .
                        ($admin_notes ? "Notes: $admin_notes" : "")
                ]);
            }

            $message = 'Request updated successfully!';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Error updating request: ' . $e->getMessage();
            $message_type = 'error';
        }
    }

    if (isset($_POST['add_update'])) {
        $request_id = (int)$_POST['request_id'];
        $update_message = sanitize($_POST['update_message']);

        try {
            db()->insert('facility_updates', [
                'request_id' => $request_id,
                'user_id' => $_SESSION['user_id'],
                'update_type' => 'comment',
                'message' => $update_message
            ]);

            $message = 'Update added successfully!';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Error adding update: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Get facility categories
$categories = db()->fetchAll("SELECT * FROM facility_categories ORDER BY name");

// Get requests based on user role
$user_condition = $_SESSION['role'] === 'admin' ? '' : 'WHERE fr.user_id = ' . $_SESSION['user_id'];
$requests = db()->fetchAll("
    SELECT fr.*,
           fc.name as category_name, fc.icon as category_icon,
           CONCAT(u.first_name, ' ', u.last_name) as requester_name,
           u.role as requester_role,
           CASE WHEN assigned.first_name IS NOT NULL
                THEN CONCAT(assigned.first_name, ' ', assigned.last_name)
                ELSE NULL END as assigned_name
    FROM facility_requests fr
    JOIN facility_categories fc ON fr.category_id = fc.id
    JOIN users u ON fr.user_id = u.id
    LEFT JOIN users assigned ON fr.assigned_to = assigned.id
    $user_condition
    ORDER BY
        CASE fr.priority
            WHEN 'urgent' THEN 1
            WHEN 'high' THEN 2
            WHEN 'medium' THEN 3
            WHEN 'low' THEN 4
        END,
        fr.created_at DESC
");

// Get statistics
$stats = [
    'total' => count($requests),
    'pending' => count(array_filter($requests, fn($r) => $r['status'] === 'pending')),
    'in_progress' => count(array_filter($requests, fn($r) => $r['status'] === 'in_progress')),
    'completed' => count(array_filter($requests, fn($r) => $r['status'] === 'completed'))
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
    <title>Facilities Management - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/advanced-ui.css">
    <style>
        .facilities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin: 25px 0;
        }

        .request-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-left: 5px solid;
            transition: all 0.3s ease;
        }

        .request-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .request-card.urgent {
            border-left-color: #ef4444;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        }

        .request-card.high {
            border-left-color: #f59e0b;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }

        .request-card.medium {
            border-left-color: #3b82f6;
        }

        .request-card.low {
            border-left-color: #10b981;
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .category-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-in_progress {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .priority-badge {
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-urgent {
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

        .request-timeline {
            border-left: 2px solid #e2e8f0;
            padding-left: 20px;
            margin-top: 20px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 15px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 0;
            width: 12px;
            height: 12px;
            background: #3b82f6;
            border-radius: 50%;
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
                opacity: 0.7;
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
                <h1><i class="fas fa-building"></i> Facilities Management</h1>
                <p>Request and manage school facilities and resources</p>
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
            <a href="facilities.php" class="active"><i class="fas fa-building"></i> Facilities</a>
            <a href="announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a>
        </nav>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="stat-details">
                    <button onclick="showRequestModal()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Request
                    </button>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['pending']; ?></h3>
                    <p>Pending</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['in_progress']; ?></h3>
                    <p>In Progress</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['completed']; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>

        <!-- Requests Grid -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i>
                    <?php echo $_SESSION['role'] === 'admin' ? 'All Facility Requests' : 'My Requests'; ?>
                    (<?php echo $stats['total']; ?>)
                </h2>
            </div>

            <?php if (empty($requests)): ?>
                <div style="text-align: center; padding: 60px; color: #64748b;">
                    <i class="fas fa-building fa-3x" style="margin-bottom: 20px; opacity: 0.5;"></i>
                    <h3>No requests found</h3>
                    <p>Submit your first facility request to get started!</p>
                    <button onclick="showRequestModal()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Submit Request
                    </button>
                </div>
            <?php else: ?>
                <div class="facilities-grid">
                    <?php foreach ($requests as $request): ?>
                        <div class="request-card <?php echo $request['priority']; ?>">
                            <div class="request-header">
                                <div class="category-icon">
                                    <i class="fas fa-<?php echo $request['category_icon']; ?>"></i>
                                </div>
                                <div>
                                    <span class="status-badge status-<?php echo $request['status']; ?>">
                                        <?php echo str_replace('_', ' ', $request['status']); ?>
                                    </span>
                                    <br>
                                    <span class="priority-badge priority-<?php echo $request['priority']; ?>">
                                        <?php echo $request['priority']; ?>
                                    </span>
                                </div>
                            </div>

                            <h3 style="margin-bottom: 10px; color: #1e293b;">
                                <?php echo htmlspecialchars($request['title']); ?>
                            </h3>

                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                <i class="fas fa-tag"></i>
                                <span style="color: #64748b; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($request['category_name']); ?>
                                </span>
                            </div>

                            <div style="margin-bottom: 15px; color: #64748b; font-size: 0.9rem;">
                                <?php echo nl2br(htmlspecialchars(substr($request['description'], 0, 150))); ?>
                                <?php if (strlen($request['description']) > 150): ?>...<?php endif; ?>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 0.9rem; margin-bottom: 15px;">
                                <div>
                                    <i class="fas fa-user"></i>
                                    <strong>Requester:</strong><br>
                                    <span style="color: #64748b;">
                                        <?php echo htmlspecialchars($request['requester_name']); ?>
                                        <span class="badge badge-<?php
                                                                    echo $request['requester_role'] === 'admin' ? 'danger' : ($request['requester_role'] === 'teacher' ? 'primary' : ($request['requester_role'] === 'student' ? 'info' : 'success'));
                                                                    ?>">
                                            <?php echo ucfirst($request['requester_role']); ?>
                                        </span>
                                    </span>
                                </div>

                                <div>
                                    <i class="fas fa-calendar"></i>
                                    <strong>Requested:</strong><br>
                                    <span style="color: #64748b;">
                                        <?php echo date('M d, Y', strtotime($request['created_at'])); ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($request['assigned_name']): ?>
                                <div style="margin-bottom: 15px; font-size: 0.9rem;">
                                    <i class="fas fa-user-check"></i>
                                    <strong>Assigned to:</strong>
                                    <span style="color: #64748b;"><?php echo htmlspecialchars($request['assigned_name']); ?></span>
                                </div>
                            <?php endif; ?>

                            <div style="display: flex; gap: 10px; margin-top: 20px;">
                                <button onclick="viewRequest(<?php echo $request['id']; ?>)" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View Details
                                </button>

                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <button onclick="manageRequest(<?php echo $request['id']; ?>)" class="btn btn-warning btn-sm">
                                        <i class="fas fa-cogs"></i> Manage
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- New Request Modal -->
    <div id="requestModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-bottom: 25px;">
                <i class="fas fa-plus-circle"></i> Submit Facility Request
            </h3>

            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select name="category_id" id="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="priority">Priority *</label>
                        <select name="priority" id="priority" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="title">Request Title *</label>
                    <input type="text" name="title" id="title" required
                        placeholder="Brief description of what you need">
                </div>

                <div class="form-group">
                    <label for="description">Detailed Description *</label>
                    <textarea name="description" id="description" rows="4" required
                        placeholder="Provide detailed information about your facility request..."></textarea>
                </div>

                <div class="form-group">
                    <label for="requested_date">Requested Completion Date</label>
                    <input type="date" name="requested_date" id="requested_date"
                        min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div style="display: flex; gap: 15px; margin-top: 25px;">
                    <button type="submit" name="submit_request" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                    <button type="button" onclick="closeRequestModal()" class="btn btn-secondary">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manage Request Modal (Admin Only) -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <div id="manageModal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 25px;">
                    <i class="fas fa-cogs"></i> Manage Request
                </h3>

                <form method="POST" id="manageForm">
                    <input type="hidden" name="request_id" id="manageRequestId">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="manage_status">Status</label>
                            <select name="status" id="manage_status">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="assigned_to">Assign To</label>
                            <select name="assigned_to" id="assigned_to">
                                <option value="">Unassigned</option>
                                <?php
                                $staff = db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE role IN ('admin', 'teacher') ORDER BY first_name");
                                foreach ($staff as $member): ?>
                                    <option value="<?php echo $member['id']; ?>">
                                        <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="admin_notes">Admin Notes</label>
                        <textarea name="admin_notes" id="admin_notes" rows="3"
                            placeholder="Add notes about this request..."></textarea>
                    </div>

                    <div style="display: flex; gap: 15px; margin-top: 25px;">
                        <button type="submit" name="update_status" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Request
                        </button>
                        <button type="button" onclick="closeManageModal()" class="btn btn-secondary">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function showRequestModal() {
            document.getElementById('requestModal').style.display = 'block';
        }

        function closeRequestModal() {
            document.getElementById('requestModal').style.display = 'none';
        }

        function manageRequest(requestId) {
            document.getElementById('manageRequestId').value = requestId;
            document.getElementById('manageModal').style.display = 'block';
        }

        function closeManageModal() {
            document.getElementById('manageModal').style.display = 'none';
        }

        function viewRequest(requestId) {
            // In a real implementation, this would open a detailed view
            window.open(`facility_details.php?id=${requestId}`, '_blank');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const requestModal = document.getElementById('requestModal');
            const manageModal = document.getElementById('manageModal');

            if (event.target === requestModal) {
                closeRequestModal();
            }
            if (event.target === manageModal) {
                closeManageModal();
            }
        }
    </script>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/pwa-manager.js"></script>
    <script src="../assets/js/pwa-analytics.js"></script>
</body>

</html>