<?php

/**
 * Students Management Page
 */

session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

// Require admin access
require_admin('../login.php');

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_student'])) {
        $data = [
            'student_id' => sanitize($_POST['student_id']),
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'date_of_birth' => $_POST['date_of_birth'],
            'grade_level' => (int)$_POST['grade_level'],
            'email' => sanitize($_POST['email']),
            'phone' => sanitize($_POST['phone']),
            'status' => 'active',
            'created_by' => $_SESSION['user_id']
        ];

        $id = db()->insert('students', $data);
        if ($id) {
            log_activity($_SESSION['user_id'], 'create', 'students', $id);
            $message = 'Student added successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error adding student!';
            $message_type = 'error';
        }
    } elseif (isset($_POST['edit_student'])) {
        $id = (int)$_POST['id'];
        $data = [
            'student_id' => sanitize($_POST['student_id']),
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'date_of_birth' => $_POST['date_of_birth'],
            'grade_level' => (int)$_POST['grade_level'],
            'email' => sanitize($_POST['email']),
            'phone' => sanitize($_POST['phone']),
            'status' => sanitize($_POST['status'])
        ];

        db()->update('students', $data, 'id = :id', ['id' => $id]);
        log_activity($_SESSION['user_id'], 'update', 'students', $id);
        $message = 'Student updated successfully!';
        $message_type = 'success';
    } elseif (isset($_POST['delete_student'])) {
        $id = (int)$_POST['student_id'];
        db()->delete('students', 'id = ?', [$id]);
        log_activity($_SESSION['user_id'], 'delete', 'students', $id);
        $message = 'Student deleted successfully!';
        $message_type = 'success';
    }
}

// Get all students with enrollment counts
$students = db()->fetchAll("
    SELECT s.*,
           COUNT(DISTINCT ce.class_id) as class_count,
           COUNT(DISTINCT ar.id) as attendance_count
    FROM students s
    LEFT JOIN class_enrollments ce ON s.id = ce.student_id
    LEFT JOIN attendance_records ar ON s.id = ar.student_id
    GROUP BY s.id
    ORDER BY s.last_name, s.first_name
");

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
    <title>Students Management - <?php echo APP_NAME; ?></title>
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
                <h1><i class="fas fa-user-graduate"></i> <?php echo APP_NAME; ?></h1>
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
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count($students); ?></h3>
                    <p>Total Students</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count(array_filter($students, fn($s) => $s['status'] === 'active')); ?></h3>
                    <p>Active Students</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-book-reader"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo array_sum(array_column($students, 'class_count')); ?></h3>
                    <p>Total Enrollments</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo array_sum(array_column($students, 'attendance_count')); ?></h3>
                    <p>Total Attendance Records</p>
                </div>
            </div>
        </div>

        <!-- Add Student Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-user-plus"></i> Add New Student</h2>
                <button onclick="toggleForm('addStudentForm')" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Student
                </button>
            </div>

            <form method="POST" id="addStudentForm" class="form-grid" style="display: none;">
                <div class="form-group">
                    <label for="student_id">
                        <i class="fas fa-id-card"></i> Student ID *
                    </label>
                    <input type="text" id="student_id" name="student_id" required placeholder="e.g., STU2024001">
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

                <div class="form-group">
                    <label for="date_of_birth">
                        <i class="fas fa-calendar"></i> Date of Birth *
                    </label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required>
                </div>

                <div class="form-group">
                    <label for="grade_level">
                        <i class="fas fa-graduation-cap"></i> Grade Level *
                    </label>
                    <select id="grade_level" name="grade_level" required>
                        <option value="">Select Grade</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>">Grade <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" id="email" name="email" placeholder="student@example.com">
                </div>

                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i> Phone
                    </label>
                    <input type="tel" id="phone" name="phone" placeholder="+1234567890">
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <button type="submit" name="add_student" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Student
                    </button>
                    <button type="button" onclick="toggleForm('addStudentForm')" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- Students List -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> All Students (<?php echo count($students); ?>)</h2>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search students..." onkeyup="searchTable()">
                </div>
            </div>

            <div class="table-responsive">
                <table id="studentsTable">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Date of Birth</th>
                            <th>Grade</th>
                            <th>Contact</th>
                            <th>Classes</th>
                            <th>Attendance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($student['date_of_birth'])); ?></td>
                                <td><span class="badge badge-info">Grade <?php echo $student['grade_level']; ?></span></td>
                                <td>
                                    <?php if ($student['email']): ?>
                                        <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($student['email']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($student['phone']): ?>
                                        <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($student['phone']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        <i class="fas fa-book"></i> <?php echo $student['class_count']; ?> Classes
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> <?php echo $student['attendance_count']; ?> Records
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $student['status'] === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($student['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="editStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)"
                                            class="btn-icon btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this student?');">
                                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" name="delete_student" class="btn-icon btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Student</h2>
                <span class="modal-close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" class="form-grid">
                <input type="hidden" id="edit_id" name="id">

                <div class="form-group">
                    <label for="edit_student_id">
                        <i class="fas fa-id-card"></i> Student ID *
                    </label>
                    <input type="text" id="edit_student_id" name="student_id" required>
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
                    <label for="edit_date_of_birth">
                        <i class="fas fa-calendar"></i> Date of Birth *
                    </label>
                    <input type="date" id="edit_date_of_birth" name="date_of_birth" required>
                </div>

                <div class="form-group">
                    <label for="edit_grade_level">
                        <i class="fas fa-graduation-cap"></i> Grade Level *
                    </label>
                    <select id="edit_grade_level" name="grade_level" required>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>">Grade <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_email">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" id="edit_email" name="email">
                </div>

                <div class="form-group">
                    <label for="edit_phone">
                        <i class="fas fa-phone"></i> Phone
                    </label>
                    <input type="tel" id="edit_phone" name="phone">
                </div>

                <div class="form-group">
                    <label for="edit_status">
                        <i class="fas fa-toggle-on"></i> Status *
                    </label>
                    <select id="edit_status" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="graduated">Graduated</option>
                        <option value="transferred">Transferred</option>
                    </select>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <button type="submit" name="edit_student" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Student
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

        function editStudent(student) {
            document.getElementById('edit_id').value = student.id;
            document.getElementById('edit_student_id').value = student.student_id;
            document.getElementById('edit_first_name').value = student.first_name;
            document.getElementById('edit_last_name').value = student.last_name;
            document.getElementById('edit_date_of_birth').value = student.date_of_birth;
            document.getElementById('edit_grade_level').value = student.grade_level;
            document.getElementById('edit_email').value = student.email || '';
            document.getElementById('edit_phone').value = student.phone || '';
            document.getElementById('edit_status').value = student.status;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('studentsTable');
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