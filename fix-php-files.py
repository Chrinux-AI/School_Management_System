#!/usr/bin/env python3
"""
Comprehensive PHP File Fixer
Adds missing CSS includes and body classes to all PHP files using navigation
"""

import os
import re
from pathlib import Path

BASE_DIR = "/opt/lampp/htdocs/attendance"

# Track changes
files_fixed = []
errors = []

def fix_php_file(filepath):
    """Fix a single PHP file by adding missing CSS and body class"""
    try:
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()

        original = content
        modified = False

        # Check if file uses navigation
        uses_nav = any(nav in content for nav in [
            'cyber-nav.php', 'admin-nav.php', 'student-nav.php',
            'general-nav.php', 'nature-nav.php'
        ])

        if not uses_nav:
            return False

        # Fix 1: Add cyberpunk-ui.css if missing
        if 'cyberpunk-ui.css' not in content and 'nature-theme.css' not in content:
            # Find </head> tag
            if '</head>' in content:
                # Add CSS before </head>
                css_link = '    <link rel="stylesheet" href="../assets/css/cyberpunk-ui.css">\n    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">\n'
                content = content.replace('</head>', css_link + '</head>')
                modified = True

        # Fix 2: Add cyber-bg class to body if missing
        if '<body' in content and 'cyber-bg' not in content and 'nature-bg' not in content:
            # Replace <body> with <body class="cyber-bg">
            content = re.sub(
                r'<body(\s+[^>]*?)?>',
                lambda m: f'<body class="cyber-bg"{m.group(1) if m.group(1) else ""}>',
                content,
                count=1
            )
            # Also handle case where body already has class attribute
            content = re.sub(
                r'<body\s+class="([^"]*)"',
                r'<body class="\1 cyber-bg"',
                content,
                count=1
            )
            modified = True

        # Fix 3: Add cyber-layout wrapper if missing
        if uses_nav and '.cyber-main' not in content and 'cyber-main' in content:
            # This is complex, skip for now
            pass

        if modified:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True

        return False

    except Exception as e:
        errors.append(f"{filepath}: {str(e)}")
        return False

def scan_and_fix_all():
    """Scan and fix all PHP files"""
    print("🔧 Fixing PHP files with missing CSS and body classes...\n")

    # Scan all PHP files
    for php_file in Path(BASE_DIR).rglob("*.php"):
        # Skip backups, vendor, includes
        if any(skip in str(php_file) for skip in ['_backups', 'vendor', '/includes/']):
            continue

        if fix_php_file(str(php_file)):
            files_fixed.append(php_file.relative_to(BASE_DIR))
            print(f"  ✓ Fixed: {php_file.relative_to(BASE_DIR)}")

    # Summary
    print("\n" + "=" * 70)
    print(f"✅ SUMMARY")
    print("=" * 70)
    print(f"Files fixed: {len(files_fixed)}")
    print(f"Errors: {len(errors)}")

    if errors:
        print("\n⚠️  Errors:")
        for error in errors[:10]:
            print(f"  - {error}")
        if len(errors) > 10:
            print(f"  ... and {len(errors) - 10} more")

    print("\n✨ Done!\n")

if __name__ == "__main__":
    scan_and_fix_all()
