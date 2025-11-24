<?php

/**
 * Teachers Management Page
 */

session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

// Require admin access
require_admin('../login.php');

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_teacher'])) {
        // First create user account
        $user_data = [
            'username' => sanitize($_POST['username']),
            'email' => sanitize($_POST['email']),
            'password_hash' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            'role' => 'teacher',
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'status' => 'active'
        ];

        $user_id = db()->insert('users', $user_data);
        if ($user_id) {
            log_activity($_SESSION['user_id'], 'create', 'users', $user_id);
            $message = 'Teacher added successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error adding teacher. Username or email may already exist.';
            $message_type = 'error';
        }
    } elseif (isset($_POST['delete_teacher'])) {
        $id = (int)$_POST['teacher_id'];
        db()->delete('users', 'id = ?', [$id]);
        log_activity($_SESSION['user_id'], 'delete', 'users', $id);
        $message = 'Teacher deleted successfully!';
        $message_type = 'success';
    } elseif (isset($_POST['assign_class'])) {
        $teacher_id = (int)$_POST['teacher_id'];
        $class_id = (int)$_POST['class_id'];

        db()->update('classes', ['teacher_id' => $teacher_id], 'id = ?', [$class_id]);
        $message = 'Class assigned successfully!';
        $message_type = 'success';
    }
}

// Get all teachers
$teachers = db()->fetchAll("
    SELECT u.*,
           (SELECT COUNT(*) FROM classes WHERE teacher_id = u.id) as class_count
    FROM users u
    WHERE u.role = 'teacher'
    ORDER BY u.last_name, u.first_name
");

// Get unassigned classes
$unassigned_classes = db()->fetchAll("
    SELECT * FROM classes WHERE teacher_id IS NULL OR teacher_id = 0 ORDER BY name
");

// Get all classes for assignment
$all_classes = db()->fetchAll("SELECT * FROM classes ORDER BY name");

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
    <title>Teachers Management - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --dark: #1f2937;
            --light: #f9fafb;
            --border: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--dark);
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }

        .nav-menu {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }

        .nav-menu a {
            padding: 12px 20px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--dark);
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-menu a:hover {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .nav-menu a.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .card h2 {
            color: var(--dark);
            margin-bottom: 25px;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: var(--success);
            color: white;
            padding: 8px 16px;
            font-size: 12px;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
            padding: 8px 16px;
            font-size: 12px;
        }

        .btn-info {
            background: var(--info);
            color: white;
            padding: 8px 16px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        th {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        th:first-child {
            border-radius: 10px 0 0 10px;
        }

        th:last-child {
            border-radius: 0 10px 10px 0;
        }

        td {
            padding: 15px;
            background: white;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        td:first-child {
            border-left: 1px solid var(--border);
            border-radius: 10px 0 0 10px;
        }

        td:last-child {
            border-right: 1px solid var(--border);
            border-radius: 0 10px 10px 0;
        }

        tbody tr {
            transition: all 0.3s;
        }

        tbody tr:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border-left: 4px solid var(--danger);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: white;
            margin: 3% auto;
            padding: 35px;
            width: 90%;
            max-width: 600px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .close {
            float: right;
            font-size: 32px;
            font-weight: 300;
            cursor: pointer;
            color: #999;
            transition: all 0.3s;
            line-height: 1;
        }

        .close:hover {
            color: var(--danger);
            transform: rotate(90deg);
        }

        .teacher-card {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border: 2px solid var(--border);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .teacher-card:hover {
            border-color: var(--primary);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.2);
            transform: translateX(5px);
        }

        .teacher-card h3 {
            color: var(--primary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: var(--light);
            color: var(--dark);
        }

        .back-link {
            color: var(--dark);
            text-decoration: none;
            padding: 10px 20px;
            background: var(--light);
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-link:hover {
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
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
            <h1><i class="fas fa-chalkboard-teacher"></i> Teachers Management</h1>
            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="container">
        <?php include '../includes/cyber-nav.php'; ?>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2><i class="fas fa-chalkboard-teacher"></i> All Teachers (<?php echo count($teachers); ?>)</h2>
                <button onclick="document.getElementById('addModal').style.display='block'" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Teacher
                </button>
            </div>

            <?php if (empty($teachers)): ?>
                <div style="text-align: center; padding: 60px 20px; color: #999;">
                    <i class="fas fa-user-tie" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                    <p style="font-size: 18px;">No teachers found. Add your first teacher!</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Name</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-id-badge"></i> Username</th>
                            <th><i class="fas fa-book-open"></i> Classes</th>
                            <th><i class="fas fa-clock"></i> Last Login</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teachers as $teacher): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                <td><code><?php echo htmlspecialchars($teacher['username']); ?></code></td>
                                <td>
                                    <span class="stat-badge">
                                        <i class="fas fa-book"></i>
                                        <?php echo $teacher['class_count']; ?> class<?php echo $teacher['class_count'] != 1 ? 'es' : ''; ?>
                                    </span>
                                </td>
                                <td><?php echo $teacher['last_login'] ? format_datetime($teacher['last_login'], 'M j, g:i A') : 'Never'; ?></td>
                                <td>
                                    <button onclick="openAssignModal(<?php echo $teacher['id']; ?>, '<?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>')" class="btn btn-info">
                                        <i class="fas fa-plus-circle"></i> Assign
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this teacher? This will unassign all their classes.');">
                                        <input type="hidden" name="teacher_id" value="<?php echo $teacher['id']; ?>">
                                        <button type="submit" name="delete_teacher" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?php if (!empty($unassigned_classes)): ?>
            <div class="card">
                <h2><i class="fas fa-exclamation-triangle"></i> Unassigned Classes (<?php echo count($unassigned_classes); ?>)</h2>
                <div class="grid-2">
                    <?php foreach ($unassigned_classes as $class): ?>
                        <div class="teacher-card">
                            <h3><i class="fas fa-book"></i> <?php echo htmlspecialchars($class['name']); ?></h3>
                            <p><strong>Code:</strong> <?php echo htmlspecialchars($class['class_code']); ?></p>
                            <p><strong>Grade:</strong> <?php echo $class['grade_level']; ?></p>
                            <p style="color: var(--warning); margin-top: 10px;"><i class="fas fa-info-circle"></i> No teacher assigned</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Teacher Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
            <h2 style="margin-bottom: 25px;"><i class="fas fa-user-plus"></i> Add New Teacher</h2>
            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> First Name *</label>
                    <input type="text" name="first_name" required placeholder="Enter first name">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Last Name *</label>
                    <input type="text" name="last_name" required placeholder="Enter last name">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email *</label>
                    <input type="email" name="email" required placeholder="teacher@school.edu">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-id-badge"></i> Username *</label>
                    <input type="text" name="username" required placeholder="Choose a username">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password *</label>
                    <input type="password" name="password" required minlength="8" placeholder="Minimum 8 characters">
                </div>
                <button type="submit" name="add_teacher" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> Add Teacher
                </button>
            </form>
        </div>
    </div>

    <!-- Assign Class Modal -->
    <div id="assignModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('assignModal').style.display='none'">&times;</span>
            <h2 style="margin-bottom: 25px;"><i class="fas fa-user-plus"></i> Assign Class</h2>
            <p id="teacherName" style="color: #666; margin-bottom: 20px;"></p>
            <form method="POST">
                <input type="hidden" name="teacher_id" id="assignTeacherId">
                <div class="form-group">
                    <label><i class="fas fa-book"></i> Select Class *</label>
                    <select name="class_id" required>
                        <option value="">Choose a class to assign</option>
                        <?php foreach ($all_classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>">
                                <?php echo htmlspecialchars($class['class_code'] . ' - ' . $class['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="assign_class" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-check"></i> Assign Class
                </button>
            </form>
        </div>
    </div>

    <script>
        function openAssignModal(teacherId, teacherName) {
            document.getElementById('assignTeacherId').value = teacherId;
            document.getElementById('teacherName').textContent = 'Assigning class to: ' + teacherName;
            document.getElementById('assignModal').style.display = 'block';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/pwa-manager.js"></script>
    <script src="../assets/js/pwa-analytics.js"></script>
</body>

</html>