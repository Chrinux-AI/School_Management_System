<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';
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
    } elseif (isset($_POST['delete_class'])) {
        $id = (int)$_POST['class_id'];
        db()->delete('classes', 'id = ?', [$id]);
        log_activity($_SESSION['user_id'], 'delete', 'classes', $id);
        $message = 'Class deleted successfully!';
        $message_type = 'success';
    }
}

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

$teachers = db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE role = 'teacher' ORDER BY first_name, last_name");

$page_title = 'Classes Management';
$page_icon = 'door-open';
$full_name = $_SESSION['full_name'];
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
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Orbitron:wght@500;700;900&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../assets/css/cyberpunk-ui.css" rel="stylesheet">
</head>
<body class="cyber-bg">
    <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="cyber-bg"><div class="starfield"></div></div>
    <div class="cyber-grid"></div>
    <div class="cyber-layout">
        <?php include '../includes/cyber-nav.php'; ?>
        <main class="cyber-main">
            <header class="cyber-header">
                <div class="page-title-section">
                    <div class="page-icon-orb"><i class="fas fa-<?php echo $page_icon; ?>"></i></div>
                    <h1 class="page-title"><?php echo $page_title; ?></h1>
                </div>
                <div class="header-actions">
                    <div class="biometric-orb" title="Quick Scan"><i class="fas fa-fingerprint"></i></div>
                    <div class="user-card" style="padding: 8px 15px; margin: 0;">
                        <div class="user-avatar" style="width: 35px; height: 35px; font-size: 0.9rem;">
                            <?php echo strtoupper(substr($full_name, 0, 2)); ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name" style="font-size: 0.85rem;"><?php echo htmlspecialchars($full_name); ?></div>
                            <div class="user-role">Administrator</div>
                        </div>
                    </div>
                </div>
            </header>
            <div class="cyber-content fade-in">
                <?php if ($message): ?>
                    <div class="cyber-alert <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
                        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                        <span><?php echo $message; ?></span>
                    </div>
                <?php endif; ?>

                <section class="orb-grid">
                    <div class="stat-orb">
                        <div class="orb-icon-wrapper cyan"><i class="fas fa-door-open"></i></div>
                        <div class="orb-content">
                            <div class="orb-value"><?php echo count($classes); ?></div>
                            <div class="orb-label">Total Classes</div>
                            <div class="orb-trend up"><i class="fas fa-arrow-up"></i><span>All Levels</span></div>
                        </div>
                    </div>
                </section>

                <div class="holo-card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-plus-circle"></i> <span>Add New Class</span></div>
                    </div>
                    <div class="card-body">
                        <form method="POST" style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                            <div>
                                <label style="color:var(--cyber-cyan);font-size:0.85rem;font-weight:600;margin-bottom:8px;display:block;">CLASS CODE</label>
                                <input type="text" name="class_code" required style="width:100%;padding:12px;background:rgba(0,191,255,0.05);border:1px solid var(--cyber-cyan);border-radius:8px;color:var(--cyber-cyan);font-family:Rajdhani;">
                            </div>
                            <div>
                                <label style="color:var(--cyber-cyan);font-size:0.85rem;font-weight:600;margin-bottom:8px;display:block;">CLASS NAME</label>
                                <input type="text" name="name" required style="width:100%;padding:12px;background:rgba(0,191,255,0.05);border:1px solid var(--cyber-cyan);border-radius:8px;color:var(--cyber-cyan);font-family:Rajdhani;">
                            </div>
                            <div>
                                <label style="color:var(--cyber-cyan);font-size:0.85rem;font-weight:600;margin-bottom:8px;display:block;">LEVEL</label>
                                <select name="grade_level" required style="width:100%;padding:12px;background:rgba(0,191,255,0.05);border:1px solid var(--cyber-cyan);border-radius:8px;color:var(--cyber-cyan);font-family:Rajdhani;">
                                    <option value="">Select Level</option>
                                    <option value="100">100 Level</option>
                                    <option value="200">200 Level</option>
                                    <option value="300">300 Level</option>
                                    <option value="400">400 Level</option>
                                    <option value="500">500 Level</option>
                                </select>
                            </div>
                            <div>
                                <label style="color:var(--cyber-cyan);font-size:0.85rem;font-weight:600;margin-bottom:8px;display:block;">ACADEMIC YEAR</label>
                                <input type="text" name="academic_year" required placeholder="2024/2025" style="width:100%;padding:12px;background:rgba(0,191,255,0.05);border:1px solid var(--cyber-cyan);border-radius:8px;color:var(--cyber-cyan);font-family:Rajdhani;">
                            </div>
                            <div>
                                <label style="color:var(--cyber-cyan);font-size:0.85rem;font-weight:600;margin-bottom:8px;display:block;">TEACHER</label>
                                <select name="teacher_id" style="width:100%;padding:12px;background:rgba(0,191,255,0.05);border:1px solid var(--cyber-cyan);border-radius:8px;color:var(--cyber-cyan);font-family:Rajdhani;">
                                    <option value="">Assign Later</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label style="color:var(--cyber-cyan);font-size:0.85rem;font-weight:600;margin-bottom:8px;display:block;">ROOM</label>
                                <input type="text" name="room_number" style="width:100%;padding:12px;background:rgba(0,191,255,0.05);border:1px solid var(--cyber-cyan);border-radius:8px;color:var(--cyber-cyan);font-family:Rajdhani;">
                            </div>
                            <div style="grid-column:span 2;">
                                <label style="color:var(--cyber-cyan);font-size:0.85rem;font-weight:600;margin-bottom:8px;display:block;">SCHEDULE</label>
                                <input type="text" name="schedule" placeholder="Mon-Fri 9:00-10:00" style="width:100%;padding:12px;background:rgba(0,191,255,0.05);border:1px solid var(--cyber-cyan);border-radius:8px;color:var(--cyber-cyan);font-family:Rajdhani;">
                            </div>
                            <div style="grid-column:span 2;display:flex;justify-content:flex-end;">
                                <button type="submit" name="add_class" class="cyber-btn primary"><i class="fas fa-save"></i> Add Class</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="holo-card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-list"></i> <span>All Classes</span></div>
                        <div class="card-badge cyan"><?php echo count($classes); ?> Classes</div>
                    </div>
                    <div class="card-body">
                        <div class="holo-table-wrapper">
                            <table class="holo-table">
                                <thead>
                                    <tr>
                                        <th>Class</th>
                                        <th>Level</th>
                                        <th>Teacher</th>
                                        <th>Students</th>
                                        <th>Room</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $class): ?>
                                        <tr>
                                            <td>
                                                <div><strong style="color:var(--cyber-cyan);"><?php echo htmlspecialchars($class['name']); ?></strong></div>
                                                <div style="font-size:0.85rem;color:rgba(0,191,255,0.6);"><?php echo htmlspecialchars($class['class_code']); ?></div>
                                            </td>
                                            <td><span class="cyber-badge purple"><?php echo $class['grade_level']; ?> Level</span></td>
                                            <td><?php echo htmlspecialchars($class['teacher_name'] ?? 'Unassigned'); ?></td>
                                            <td><span class="cyber-badge cyan"><?php echo $class['student_count']; ?> Students</span></td>
                                            <td><?php echo htmlspecialchars($class['room_number'] ?? 'TBA'); ?></td>
                                            <td>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this class?');">
                                                    <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                                    <button type="submit" name="delete_class" class="cyber-btn danger" style="padding:8px 12px;font-size:0.85rem;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/pwa-manager.js"></script>
    <script src="../assets/js/pwa-analytics.js"></script>
</body>
</html>
