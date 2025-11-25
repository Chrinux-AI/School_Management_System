#!/usr/bin/env python3
"""
Comprehensive Navigation & Sidebar Fix Script
Ensures all navigation files have proper hamburger menu and sidebar collapse
"""

import os
import re
from pathlib import Path

BASE_DIR = "/opt/lampp/htdocs/attendance"
INCLUDES_DIR = os.path.join(BASE_DIR, "includes")

# Standard hamburger menu HTML
HAMBURGER_HTML = '''<!-- Hamburger Menu Button -->
<button class="hamburger-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
    <i class="fas fa-bars"></i>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
'''

# Standard sidebar toggle script
TOGGLE_SCRIPT = '''<script>
(function() {
    const sidebar = document.getElementById('cyberSidebar') || document.querySelector('.cyber-sidebar') || document.querySelector('.sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            if (overlay) overlay.classList.toggle('active');
        });
    }

    if (overlay && sidebar) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }
})();
</script>'''

def check_nav_file(filepath):
    """Check if nav file has hamburger menu and toggle script"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        has_hamburger = 'hamburger-btn' in content or 'sidebarToggle' in content
        has_overlay = 'sidebar-overlay' in content or 'sidebarOverlay' in content
        has_script = 'sidebarToggle' in content and 'addEventListener' in content
        has_sidebar_id = 'id="cyberSidebar"' in content or 'id="sidebar"' in content

        return {
            'has_hamburger': has_hamburger,
            'has_overlay': has_overlay,
            'has_script': has_script,
            'has_sidebar_id': has_sidebar_id,
            'needs_fix': not (has_hamburger and has_overlay and has_script)
        }
    except Exception as e:
        print(f"Error reading {filepath}: {e}")
        return None

def analyze_all_navs():
    """Analyze all navigation files"""
    nav_files = [
        'cyber-nav.php',
        'admin-nav.php',
        'student-nav.php',
        'general-nav.php',
        'nature-nav.php'
    ]

    print("=" * 70)
    print("NAVIGATION FILES ANALYSIS")
    print("=" * 70)

    results = {}
    for nav_file in nav_files:
        filepath = os.path.join(INCLUDES_DIR, nav_file)
        if os.path.exists(filepath):
            result = check_nav_file(filepath)
            if result:
                results[nav_file] = result
                status = "✅ OK" if not result['needs_fix'] else "⚠️  NEEDS FIX"
                print(f"\n{nav_file}: {status}")
                print(f"  Hamburger Button: {'✓' if result['has_hamburger'] else '✗'}")
                print(f"  Overlay: {'✓' if result['has_overlay'] else '✗'}")
                print(f"  Toggle Script: {'✓' if result['has_script'] else '✗'}")
                print(f"  Sidebar ID: {'✓' if result['has_sidebar_id'] else '✗'}")
        else:
            print(f"\n{nav_file}: ❌ NOT FOUND")

    print("\n" + "=" * 70)
    needs_fix = [k for k, v in results.items() if v['needs_fix']]
    if needs_fix:
        print(f"Files needing fixes: {', '.join(needs_fix)}")
    else:
        print("All navigation files are properly configured! ✅")
    print("=" * 70)

    return results

def scan_php_files_using_navs():
    """Scan PHP files that include navigation to ensure they have proper structure"""
    print("\n" + "=" * 70)
    print("SCANNING PHP FILES FOR NAVIGATION USAGE")
    print("=" * 70)

    issues = []

    # Scan all PHP files excluding backups and vendor
    for php_file in Path(BASE_DIR).rglob("*.php"):
        if '_backups' in str(php_file) or 'vendor' in str(php_file):
            continue

        try:
            with open(php_file, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()

            # Check if file includes navigation
            if any(nav in content for nav in ['cyber-nav.php', 'admin-nav.php', 'student-nav.php', 'general-nav.php', 'nature-nav.php']):
                # Check for proper CSS inclusion
                if 'cyberpunk-ui.css' not in content and 'nature-theme.css' not in content:
                    issues.append(f"{php_file.relative_to(BASE_DIR)}: Missing theme CSS")

                # Check for cyber-bg class
                if '<body' in content and 'cyber-bg' not in content and 'nature-bg' not in content:
                    issues.append(f"{php_file.relative_to(BASE_DIR)}: Missing background class on <body>")

        except Exception as e:
            pass

    if issues:
        print(f"\n⚠️  Found {len(issues)} potential issues:")
        for issue in issues[:20]:
            print(f"  - {issue}")
        if len(issues) > 20:
            print(f"  ... and {len(issues) - 20} more")
    else:
        print("\n✅ All files using navigation are properly configured!")

    print("=" * 70)

if __name__ == "__main__":
    print("\n🔍 Starting Comprehensive Navigation Analysis...\n")
    results = analyze_all_navs()
    scan_php_files_using_navs()
    print("\n✨ Analysis Complete!\n")
