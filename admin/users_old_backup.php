<?php

/**
 * Users Management Page
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
    if (isset($_POST['bulk_action']) && isset($_POST['selected_users'])) {
        $selected_users = $_POST['selected_users'];
        $action = $_POST['bulk_action'];

        if ($action === 'disable' && !empty($selected_users)) {
            $count = 0;
            foreach ($selected_users as $user_id) {
                $user_id = (int)$user_id;
                // Prevent disabling own account
                if ($user_id !== $_SESSION['user_id']) {
                    db()->update('users', ['status' => 'inactive'], 'id = :id', ['id' => $user_id]);
                    log_activity($_SESSION['user_id'], 'disable', 'users', $user_id);
                    $count++;
                }
            }
            $message = "$count user(s) disabled successfully!";
            $message_type = 'success';
        } elseif ($action === 'enable' && !empty($selected_users)) {
            $count = 0;
            foreach ($selected_users as $user_id) {
                $user_id = (int)$user_id;
                db()->update('users', ['status' => 'active'], 'id = :id', ['id' => $user_id]);
                log_activity($_SESSION['user_id'], 'enable', 'users', $user_id);
                $count++;
            }
            $message = "$count user(s) enabled successfully!";
            $message_type = 'success';
        }
    } elseif (isset($_POST['toggle_status'])) {
        $user_id = (int)$_POST['user_id'];
        $new_status = $_POST['new_status'];

        if ($user_id !== $_SESSION['user_id']) {
            db()->update('users', ['status' => $new_status], 'id = :id', ['id' => $user_id]);
            log_activity($_SESSION['user_id'], $new_status === 'active' ? 'enable' : 'disable', 'users', $user_id);
            $message = "User status updated to $new_status!";
            $message_type = 'success';
        } else {
            $message = 'You cannot disable your own account!';
            $message_type = 'error';
        }
    } elseif (isset($_POST['add_user'])) {
        $data = [
            'username' => sanitize($_POST['username']),
            'email' => sanitize($_POST['email']),
            'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'role' => sanitize($_POST['role']),
            'status' => 'active'
        ];

        $id = db()->insert('users', $data);
        if ($id) {
            log_activity($_SESSION['user_id'], 'create', 'users', $id);
            $message = 'User added successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error adding user! Username or email may already exist.';
            $message_type = 'error';
        }
    } elseif (isset($_POST['edit_user'])) {
        $id = (int)$_POST['id'];
        $data = [
            'username' => sanitize($_POST['username']),
            'email' => sanitize($_POST['email']),
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'role' => sanitize($_POST['role']),
            'status' => sanitize($_POST['status'])
        ];

        // Update password only if provided
        if (!empty($_POST['password'])) {
            $data['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        db()->update('users', $data, 'id = :id', ['id' => $id]);
        log_activity($_SESSION['user_id'], 'update', 'users', $id);
        $message = 'User updated successfully!';
        $message_type = 'success';
    } elseif (isset($_POST['delete_user'])) {
        $id = (int)$_POST['user_id'];

        // Prevent deleting own account
        if ($id === $_SESSION['user_id']) {
            $message = 'You cannot delete your own account!';
            $message_type = 'error';
        } else {
            db()->delete('users', 'id = ?', [$id]);
            log_activity($_SESSION['user_id'], 'delete', 'users', $id);
            $message = 'User deleted successfully!';
            $message_type = 'success';
        }
    }
}

// Get all users
$users = db()->fetchAll("SELECT * FROM users ORDER BY role, last_name, first_name");

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
    <title>Users Management - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
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
                <h1><i class="fas fa-users"></i> <?php echo APP_NAME; ?></h1>
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

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count($users); ?></h3>
                    <p>Total Users</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count(array_filter($users, fn($u) => $u['role'] === 'admin')); ?></h3>
                    <p>Administrators</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count(array_filter($users, fn($u) => $u['role'] === 'teacher')); ?></h3>
                    <p>Teachers</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count(array_filter($users, fn($u) => $u['status'] === 'active')); ?></h3>
                    <p>Active Users</p>
                </div>
            </div>
        </div>

        <!-- Add User Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-user-plus"></i> Add New User</h2>
                <button onclick="toggleForm('addUserForm')" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add User
                </button>
            </div>

            <form method="POST" id="addUserForm" class="form-grid" style="display: none;">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Username *
                    </label>
                    <input type="text" id="username" name="username" required placeholder="Enter username">
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email *
                    </label>
                    <input type="email" id="email" name="email" required placeholder="user@example.com">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password *
                    </label>
                    <input type="password" id="password" name="password" required placeholder="Enter password">
                </div>

                <div class="form-group">
                    <label for="role">
                        <i class="fas fa-user-tag"></i> Role *
                    </label>
                    <select id="role" name="role" required>
                        <option value="admin">Administrator</option>
                        <option value="teacher">Teacher</option>
                        <option value="student">Student</option>
                        <option value="parent">Parent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="first_name">
                        <i class="fas fa-user"></i> First Name *
                    </label>
                    <input type="text" id="first_name" name="first_name" required placeholder="Enter first name">
                </div>

                <div class="form-group">
                    <label for="last_name">
                        <i class="fas fa-user"></i> Last Name *
                    </label>
                    <input type="text" id="last_name" name="last_name" required placeholder="Enter last name">
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <button type="submit" name="add_user" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save User
                    </button>
                    <button type="button" onclick="toggleForm('addUserForm')" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- Users List -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> All Users (<?php echo count($users); ?>)</h2>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <!-- Bulk Actions -->
                    <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                        <select name="bulk_action" style="padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                            <option value="">Bulk Actions</option>
                            <option value="disable">Disable Selected</option>
                            <option value="enable">Enable Selected</option>
                        </select>
                        <button type="submit" class="btn btn-warning" style="padding: 8px 15px;" onclick="return confirmBulkAction()">
                            <i class="fas fa-cogs"></i> Apply
                        </button>
                    </form>

                    <!-- Search -->
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search users..." onkeyup="searchTable()">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes()"> Select</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Last Login</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <input type="checkbox" name="selected_users[]" value="<?php echo $user['id']; ?>" class="user-checkbox">
                                    <?php else: ?>
                                        <span style="color: #64748b; font-size: 12px;">You</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                            <p style="font-size: 12px; color: #64748b;">@<?php echo htmlspecialchars($user['username']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php
                                                                echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'teacher' ? 'primary' : 'info');
                                                                ?>">
                                        <i class="fas fa-<?php
                                                            echo $user['role'] === 'admin' ? 'user-shield' : ($user['role'] === 'teacher' ? 'chalkboard-teacher' : 'user');
                                                            ?>"></i>
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['last_login']): ?>
                                        <?php echo date('M d, Y', strtotime($user['last_login'])); ?>
                                        <br>
                                        <small><?php echo date('h:i A', strtotime($user['last_login'])); ?></small>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <i class="fas fa-<?php echo $user['status'] === 'active' ? 'check-circle' : 'times-circle'; ?>"></i>
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>

                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="new_status" value="<?php echo $user['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $user['status'] === 'active' ? 'btn-warning' : 'btn-success'; ?>"
                                                    onclick="return confirm('Change user status to <?php echo $user['status'] === 'active' ? 'inactive' : 'active'; ?>?')"
                                                    title="<?php echo $user['status'] === 'active' ? 'Disable' : 'Enable'; ?> User">
                                                    <i class="fas fa-<?php echo $user['status'] === 'active' ? 'ban' : 'check'; ?>"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)"
                                            class="btn-icon btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <form method="POST" style="display: inline;"
                                                onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="delete_user" class="btn-icon btn-delete" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit User</h2>
                <span class="modal-close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" class="form-grid">
                <input type="hidden" id="edit_id" name="id">

                <div class="form-group">
                    <label for="edit_username">
                        <i class="fas fa-user"></i> Username *
                    </label>
                    <input type="text" id="edit_username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="edit_email">
                        <i class="fas fa-envelope"></i> Email *
                    </label>
                    <input type="email" id="edit_email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="edit_password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="edit_password" name="password" placeholder="Leave blank to keep current">
                </div>

                <div class="form-group">
                    <label for="edit_role">
                        <i class="fas fa-user-tag"></i> Role *
                    </label>
                    <select id="edit_role" name="role" required>
                        <option value="admin">Administrator</option>
                        <option value="teacher">Teacher</option>
                        <option value="student">Student</option>
                        <option value="parent">Parent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_first_name">
                        <i class="fas fa-user"></i> First Name *
                    </label>
                    <input type="text" id="edit_first_name" name="first_name" required>
                </div>

                <div class="form-group">
                    <label for="edit_last_name">
                        <i class="fas fa-user"></i> Last Name *
                    </label>
                    <input type="text" id="edit_last_name" name="last_name" required>
                </div>

                <div class="form-group">
                    <label for="edit_status">
                        <i class="fas fa-toggle-on"></i> Status *
                    </label>
                    <select id="edit_status" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <button type="submit" name="edit_user" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update User
                    </button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleForm(formId) {
            const form = document.getElementById(formId);
            if (form.style.display === 'none') {
                form.style.display = 'grid';
            } else {
                form.style.display = 'none';
                form.reset();
            }
        }

        function editUser(user) {
            document.getElementById('edit_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_first_name').value = user.first_name;
            document.getElementById('edit_last_name').value = user.last_name;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_status').value = user.status;
            document.getElementById('edit_password').value = '';
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('usersTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                tr[i].style.display = found ? '' : 'none';
            }
        }

        // Bulk action functions
        function toggleAllCheckboxes() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.user-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }

        function confirmBulkAction() {
            const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
            const action = document.querySelector('select[name="bulk_action"]').value;

            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one user.');
                return false;
            }

            if (!action) {
                alert('Please select an action.');
                return false;
            }

            return confirm(`Are you sure you want to ${action} ${selectedCheckboxes.length} user(s)?`);
        }

        // Enhanced UI feedback
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const selectedCount = document.querySelectorAll('.user-checkbox:checked').length;
                const selectAll = document.getElementById('selectAll');
                const totalCheckboxes = document.querySelectorAll('.user-checkbox').length;

                if (selectedCount === 0) {
                    selectAll.indeterminate = false;
                    selectAll.checked = false;
                } else if (selectedCount === totalCheckboxes) {
                    selectAll.indeterminate = false;
                    selectAll.checked = true;
                } else {
                    selectAll.indeterminate = true;
                    selectAll.checked = false;
                }
            });
        });
    </script>

    for (let j = 0; j < td.length; j++) {
        if (td[j]) {
        const txtValue=td[j].textContent || td[j].innerText;
        if (txtValue.toUpperCase().indexOf(filter)> -1) {
        found = true;
        break;
        }
        }
        }

        tr[i].style.display = found ? '' : 'none';
        }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
        const modal = document.getElementById('editModal');
        if (event.target == modal) {
        closeModal();
        }
        }
        </script>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/pwa-manager.js"></script>
    <script src="../assets/js/pwa-analytics.js"></script>
</body>

</html>