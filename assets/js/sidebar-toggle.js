/**
 * Sidebar Toggle Functionality
 * Handles collapsing/expanding sidebar across all panels
 */

// Initialize sidebar state from localStorage
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleBtn = document.getElementById('sidebarToggle');

    // Check if elements exist
    if (!sidebar || !toggleBtn) return;

    // Load saved state
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
    }

    // Update toggle icon
    updateToggleIcon();
});

/**
 * Toggle sidebar visibility
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');

    if (!sidebar) return;

    // Toggle collapsed class
    sidebar.classList.toggle('collapsed');

    // Save state to localStorage
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);

    // Update icon
    updateToggleIcon();

    // Add animation class
    sidebar.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
}

/**
 * Update toggle button icon based on sidebar state
 */
function updateToggleIcon() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');

    if (!sidebar || !toggleBtn) return;

    const icon = toggleBtn.querySelector('i');
    if (!icon) return;

    // Always use hamburger icon
    icon.className = 'fas fa-bars';

    if (sidebar.classList.contains('collapsed')) {
        toggleBtn.title = 'Expand Sidebar';
    } else {
        toggleBtn.title = 'Collapse Sidebar';
    }
}/**
 * Mobile sidebar toggle
 */
function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;

    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('active');
    }
}

/**
 * Close sidebar when clicking outside on mobile
 */
document.addEventListener('click', function(event) {
    if (window.innerWidth <= 768) {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');

        if (!sidebar || !toggleBtn) return;

        // Check if click is outside sidebar and toggle button
        if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    }
});

/**
 * Handle window resize
 */
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;

    // Reset mobile active class on desktop
    if (window.innerWidth > 768) {
        sidebar.classList.remove('active');
    }
});

/**
 * Keyboard shortcut: Ctrl+B to toggle sidebar
 */
document.addEventListener('keydown', function(event) {
    // Ctrl+B or Cmd+B
    if ((event.ctrlKey || event.metaKey) && event.key === 'b') {
        event.preventDefault();
        toggleSidebar();
    }
});
