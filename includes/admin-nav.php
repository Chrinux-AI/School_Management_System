<?php

/**
 * Admin Cyberpunk Navigation Component
 * Advanced sidebar with hamburger menu and collapse functionality
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_name = $_SESSION['full_name'] ?? 'Admin';
$user_role = $_SESSION['role'] ?? 'admin';
$user_id = $_SESSION['user_id'] ?? 0;
$user_initials = strtoupper(substr($user_name, 0, 2));

// Get unread messages count
$unread_count = 0;
if ($user_id > 0) {
    try {
        $result = db()->fetchOne("
            SELECT COUNT(*) as count FROM message_recipients
            WHERE recipient_id = ? AND is_read = 0 AND deleted_at IS NULL
        ", [$user_id]);
        $unread_count = $result['count'] ?? 0;
    } catch (Exception $e) {
        $unread_count = 0;
    }
}

// Admin navigation menu structure
$nav_sections = [
    'Dashboard' => [
        'dashboard.php' => ['icon' => 'tachometer-alt', 'label' => 'Dashboard', 'badge' => null],
        'overview.php' => ['icon' => 'chart-pie', 'label' => 'Overview', 'badge' => null],
        'enhanced-analytics.php' => ['icon' => 'brain', 'label' => 'AI Analytics', 'badge' => null],
    ],
    'Core Management' => [
        'students.php' => ['icon' => 'user-graduate', 'label' => 'Students', 'badge' => null],
        'teachers.php' => ['icon' => 'chalkboard-teacher', 'label' => 'Teachers', 'badge' => null],
        'parents.php' => ['icon' => 'users', 'label' => 'Parents', 'badge' => null],
        'classes.php' => ['icon' => 'door-open', 'label' => 'Classes', 'badge' => null],
        'attendance.php' => ['icon' => 'check-circle', 'label' => 'Attendance', 'badge' => null],
    ],
    'Advanced Features' => [
        'advanced-admin.php' => ['icon' => 'rocket', 'label' => 'Advanced Admin', 'badge' => null],
        'system-management.php' => ['icon' => 'database', 'label' => 'System Management', 'badge' => null],
        'system-monitor.php' => ['icon' => 'shield-alt', 'label' => 'System Monitor', 'badge' => null],
        'db-schema-manager.php' => ['icon' => 'project-diagram', 'label' => 'DB Schema', 'badge' => null],
    ],
    'Communication' => [
        'communication.php' => ['icon' => 'comments', 'label' => 'Communication', 'badge' => null],
        'announcements.php' => ['icon' => 'bullhorn', 'label' => 'Announcements', 'badge' => null],
        'messages.php' => ['icon' => 'envelope', 'label' => 'Messages', 'badge' => $unread_count > 0 ? $unread_count : null],
    ],
    'Administration' => [
        'users.php' => ['icon' => 'users-cog', 'label' => 'Users', 'badge' => null],
        'registrations.php' => ['icon' => 'user-check', 'label' => 'Registrations', 'badge' => null],
        'id-management.php' => ['icon' => 'id-card', 'label' => 'ID Management', 'badge' => null],
        'timetable.php' => ['icon' => 'calendar-alt', 'label' => 'Timetable', 'badge' => null],
    ],
    'Operations' => [
        'facilities.php' => ['icon' => 'building', 'label' => 'Facilities', 'badge' => null],
        'reports.php' => ['icon' => 'chart-bar', 'label' => 'Reports', 'badge' => null],
        'realtime-sync.php' => ['icon' => 'sync', 'label' => 'Real-time Sync', 'badge' => null],
        'cloud-storage.php' => ['icon' => 'cloud', 'label' => 'Cloud Storage', 'badge' => null],
        'mobile-api.php' => ['icon' => 'mobile-alt', 'label' => 'Mobile API', 'badge' => null],
    ],
    'Settings' => [
        'settings.php' => ['icon' => 'cog', 'label' => 'Settings', 'badge' => null],
    ],
];
?>

<!-- Hamburger Menu Button -->
<button class="hamburger-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Cyberpunk Sidebar -->
<aside class="cyber-sidebar slide-in" id="cyberSidebar">
    <!-- Brand Section -->
    <div class="sidebar-brand">
        <div class="brand-orb">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <h2 class="brand-title">SMS</h2>
        <p class="brand-subtitle">Admin Panel</p>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-menu">
        <?php foreach ($nav_sections as $section_name => $items): ?>
            <div class="menu-section-title"><?php echo $section_name; ?></div>
            <?php foreach ($items as $page => $item): ?>
                <a href="<?php echo $page; ?>" class="menu-item <?php echo $current_page === $page ? 'active' : ''; ?>">
                    <span class="menu-icon">
                        <i class="fas fa-<?php echo $item['icon']; ?>"></i>
                    </span>
                    <span class="menu-label"><?php echo $item['label']; ?></span>
                    <?php if ($item['badge']): ?>
                        <span class="menu-badge"><?php echo $item['badge']; ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <!-- Logout -->
        <a href="../logout.php" class="menu-item" style="margin-top: 20px; border-top: 1px solid var(--glass-border); padding-top: 20px;">
            <span class="menu-icon">
                <i class="fas fa-sign-out-alt"></i>
            </span>
            <span class="menu-label">Logout</span>
        </a>
    </nav>

    <!-- User Profile Card -->
    <div class="sidebar-user">
        <div class="user-card">
            <div class="user-avatar">
                <?php echo $user_initials; ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                <div class="user-role"><?php echo htmlspecialchars($user_role); ?></div>
            </div>
        </div>
    </div>
</aside>

<!-- Sidebar Toggle Script -->
<script>
    (function() {
        const sidebar = document.getElementById('cyberSidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (sidebarToggle && sidebar && sidebarOverlay) {
            // Toggle sidebar
            sidebarToggle.addEventListener('click', function() {
                const isMobile = window.innerWidth <= 1024;

                if (isMobile) {
                    // Mobile: use .active class
                    sidebar.classList.toggle('active');
                    sidebarOverlay.classList.toggle('active');
                } else {
                    // Desktop: use .hidden class
                    sidebar.classList.toggle('hidden');
                }
            });

            // Close sidebar when clicking overlay (mobile only)
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            });

            // Close sidebar on mobile when clicking a link
            const menuItems = sidebar.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 1024) {
                        sidebar.classList.remove('active');
                        sidebarOverlay.classList.remove('active');
                    }
                });
            });
        }
    })();
</script>