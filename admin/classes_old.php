<?php

/**
 * Classes Management Page
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
    if (isset($_POST['add_class'])) {
        $data = [
            'class_code' => sanitize($_POST['class_code']),
            'name' => sanitize($_POST['name']),
            'grade_level' => (int)$_POST['grade_level'],
            'academic_year' => sanitize($_POST['academic_year']),
            'teacher_id' => !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null,
            'room_number' => sanitize($_POST['room_number']),
            'schedule' => sanitize($_POST['schedule'])
        ];

        $id = db()->insert('classes', $data);
        if ($id) {
            log_activity($_SESSION['user_id'], 'create', 'classes', $id);
            $message = 'Class added successfully!';
            $message_type = 'success';
        }
    } elseif (isset($_POST['edit_class'])) {
        $id = (int)$_POST['id'];
        $data = [
            'class_code' => sanitize($_POST['class_code']),
            'name' => sanitize($_POST['name']),
            'grade_level' => (int)$_POST['grade_level'],
            'academic_year' => sanitize($_POST['academic_year']),
            'teacher_id' => !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null,
            'room_number' => sanitize($_POST['room_number']),
            'schedule' => sanitize($_POST['schedule'])
        ];

        db()->update('classes', $data, 'id = :id', ['id' => $id]);
        log_activity($_SESSION['user_id'], 'update', 'classes', $id);
        $message = 'Class updated successfully!';
        $message_type = 'success';
    } elseif (isset($_POST['delete_class'])) {
        $id = (int)$_POST['class_id'];
        db()->delete('classes', 'id = ?', [$id]);
        log_activity($_SESSION['user_id'], 'delete', 'classes', $id);
        $message = 'Class deleted successfully!';
        $message_type = 'success';
    } elseif (isset($_POST['enroll_student'])) {
        $student_id = (int)$_POST['student_id'];
        $class_id = (int)$_POST['class_id'];

        $exists = db()->fetch("SELECT id FROM class_enrollments WHERE student_id = ? AND class_id = ?", [$student_id, $class_id]);
        if (!$exists) {
            db()->insert('class_enrollments', [
                'student_id' => $student_id,
                'class_id' => $class_id,
                'enrollment_date' => date('Y-m-d')
            ]);
            $message = 'Student enrolled successfully!';
            $message_type = 'success';
        } else {
            $message = 'Student is already enrolled in this class!';
            $message_type = 'error';
        }
    } elseif (isset($_POST['unenroll_student'])) {
        $enrollment_id = (int)$_POST['enrollment_id'];
        db()->delete('class_enrollments', 'id = ?', [$enrollment_id]);
        $message = 'Student unenrolled successfully!';
        $message_type = 'success';
    }
}

// Get all classes with teacher and enrollment info
$classes = db()->fetchAll("
    SELECT c.*,
           CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
           COUNT(DISTINCT ce.student_id) as student_count
    FROM classes c
    LEFT JOIN users u ON c.teacher_id = u.id
    LEFT JOIN class_enrollments ce ON c.id = ce.class_id
    GROUP BY c.id
    ORDER BY c.grade_level, c.name
");

// Get teachers for dropdown
$teachers = db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE role = 'teacher' ORDER BY first_name, last_name");

// Get students for enrollment
$students = db()->fetchAll("SELECT id, student_id, first_name, last_name, grade_level FROM students WHERE status = 'active' ORDER BY last_name, first_name");

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
    <title>Classes Management - <?php echo APP_NAME; ?></title>
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
                <h1><i class="fas fa-book"></i> <?php echo APP_NAME; ?></h1>
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
            <div class="stat-orb">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count($classes); ?></h3>
                    <p>Total Classes</p>
                </div>
            </div>

            <div class="stat-orb">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo array_sum(array_column($classes, 'student_count')); ?></h3>
                    <p>Total Enrollments</p>
                </div>
            </div>

            <div class="stat-orb">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count($teachers); ?></h3>
                    <p>Available Teachers</p>
                </div>
            </div>

            <div class="stat-orb">
                <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count($students); ?></h3>
                    <p>Active Students</p>
                </div>
            </div>
        </div>

        <!-- Add Class Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-plus-circle"></i> Add New Class</h2>
                <button onclick="toggleForm('addClassForm')" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Class
                </button>
            </div>

            <form method="POST" id="addClassForm" class="form-grid" style="display: none;">
                <div class="form-group">
                    <label for="class_code">
                        <i class="fas fa-code"></i> Class Code *
                    </label>
                    <input type="text" id="class_code" name="class_code" required placeholder="e.g., MATH101">
                </div>

                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-book"></i> Class Name *
                    </label>
                    <input type="text" id="name" name="name" required placeholder="e.g., Mathematics 101">
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
                    <label for="academic_year">
                        <i class="fas fa-calendar-alt"></i> Academic Year *
                    </label>
                    <input type="text" id="academic_year" name="academic_year" required placeholder="e.g., 2024-2025" value="2024-2025">
                </div>

                <div class="form-group">
                    <label for="teacher_id">
                        <i class="fas fa-chalkboard-teacher"></i> Teacher
                    </label>
                    <select id="teacher_id" name="teacher_id">
                        <option value="">Select Teacher</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?php echo $teacher['id']; ?>">
                                <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="room_number">
                        <i class="fas fa-door-open"></i> Room Number
                    </label>
                    <input type="text" id="room_number" name="room_number" placeholder="e.g., Room 101">
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="schedule">
                        <i class="fas fa-clock"></i> Schedule
                    </label>
                    <input type="text" id="schedule" name="schedule" placeholder="e.g., Mon/Wed/Fri 9:00-10:30 AM">
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <button type="submit" name="add_class" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Class
                    </button>
                    <button type="button" onclick="toggleForm('addClassForm')" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- Classes List -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> All Classes (<?php echo count($classes); ?>)</h2>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search classes..." onkeyup="searchTable()">
                </div>
            </div>

            <div class="table-responsive">
                <table id="classesTable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Class Name</th>
                            <th>Grade</th>
                            <th>Teacher</th>
                            <th>Room</th>
                            <th>Schedule</th>
                            <th>Students</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($class['class_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($class['name']); ?></td>
                                <td><span class="badge badge-info">Grade <?php echo $class['grade_level']; ?></span></td>
                                <td>
                                    <?php if ($class['teacher_name']): ?>
                                        <i class="fas fa-chalkboard-teacher"></i> <?php echo htmlspecialchars($class['teacher_name']); ?>
                                    <?php else: ?>
                                        <span class="badge badge-warning">No Teacher</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($class['room_number'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($class['schedule'] ?: '-'); ?></td>
                                <td>
                                    <button onclick="showEnrollments(<?php echo $class['id']; ?>)" class="badge badge-primary" style="cursor: pointer; border: none;">
                                        <i class="fas fa-users"></i> <?php echo $class['student_count']; ?> Students
                                    </button>
                                </td>
                                <td><?php echo htmlspecialchars($class['academic_year']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="editClass(<?php echo htmlspecialchars(json_encode($class)); ?>)"
                                            class="btn-icon btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="enrollStudent(<?php echo $class['id']; ?>)"
                                            class="btn-icon btn-success" title="Enroll Student">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                        <form method="POST" style="display: inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this class?');">
                                            <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                            <button type="submit" name="delete_class" class="btn-icon btn-delete" title="Delete">
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

    <!-- Edit Class Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Class</h2>
                <span class="modal-close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST" class="form-grid">
                <input type="hidden" id="edit_id" name="id">

                <div class="form-group">
                    <label for="edit_class_code">
                        <i class="fas fa-code"></i> Class Code *
                    </label>
                    <input type="text" id="edit_class_code" name="class_code" required>
                </div>

                <div class="form-group">
                    <label for="edit_name">
                        <i class="fas fa-book"></i> Class Name *
                    </label>
                    <input type="text" id="edit_name" name="name" required>
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
                    <label for="edit_academic_year">
                        <i class="fas fa-calendar-alt"></i> Academic Year *
                    </label>
                    <input type="text" id="edit_academic_year" name="academic_year" required>
                </div>

                <div class="form-group">
                    <label for="edit_teacher_id">
                        <i class="fas fa-chalkboard-teacher"></i> Teacher
                    </label>
                    <select id="edit_teacher_id" name="teacher_id">
                        <option value="">Select Teacher</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?php echo $teacher['id']; ?>">
                                <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_room_number">
                        <i class="fas fa-door-open"></i> Room Number
                    </label>
                    <input type="text" id="edit_room_number" name="room_number">
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="edit_schedule">
                        <i class="fas fa-clock"></i> Schedule
                    </label>
                    <input type="text" id="edit_schedule" name="schedule">
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <button type="submit" name="edit_class" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Class
                    </button>
                    <button type="button" onclick="closeModal('editModal')" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Enroll Student Modal -->
    <div id="enrollModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Enroll Student</h2>
                <span class="modal-close" onclick="closeModal('enrollModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" id="enroll_class_id" name="class_id">

                <div class="form-group">
                    <label for="student_id">
                        <i class="fas fa-user-graduate"></i> Select Student *
                    </label>
                    <select id="student_id" name="student_id" required>
                        <option value="">Choose a student...</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['id']; ?>">
                                <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name'] . ' (Grade ' . $student['grade_level'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" name="enroll_student" class="btn btn-primary">
                        <i class="fas fa-check"></i> Enroll Student
                    </button>
                    <button type="button" onclick="closeModal('enrollModal')" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Enrollments Modal -->
    <div id="enrollmentsModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2><i class="fas fa-users"></i> Class Enrollments</h2>
                <span class="modal-close" onclick="closeModal('enrollmentsModal')">&times;</span>
            </div>
            <div id="enrollmentsContent">
                <p>Loading...</p>
            </div>
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

        function editClass(classData) {
            document.getElementById('edit_id').value = classData.id;
            document.getElementById('edit_class_code').value = classData.class_code;
            document.getElementById('edit_name').value = classData.name;
            document.getElementById('edit_grade_level').value = classData.grade_level;
            document.getElementById('edit_academic_year').value = classData.academic_year;
            document.getElementById('edit_teacher_id').value = classData.teacher_id || '';
            document.getElementById('edit_room_number').value = classData.room_number || '';
            document.getElementById('edit_schedule').value = classData.schedule || '';
            document.getElementById('editModal').style.display = 'flex';
        }

        function enrollStudent(classId) {
            document.getElementById('enroll_class_id').value = classId;
            document.getElementById('enrollModal').style.display = 'flex';
        }

        function showEnrollments(classId) {
            document.getElementById('enrollmentsModal').style.display = 'flex';
            document.getElementById('enrollmentsContent').innerHTML = '<p>Loading...</p>';

            fetch('ajax/get_enrollments.php?class_id=' + classId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('enrollmentsContent').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('enrollmentsContent').innerHTML = '<p class="alert alert-error">Error loading enrollments</p>';
                });
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('classesTable');
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