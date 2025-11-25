#!/usr/bin/env python3
"""
Fix remaining files with missing CSS
"""

import re

BASE_DIR = "/opt/lampp/htdocs/attendance"

files_to_fix = [
    "student/study-groups.php",
    "teacher/resource-library.php",
    "teacher/meeting-hours.php",
    "teacher/behavior-logs.php",
    "teacher/resources.php",
    "general/help.php",
    "general/faq.php",
    "parent/book-meeting.php",
    "parent/my-meetings.php",
]

for filename in files_to_fix:
    filepath = f"{BASE_DIR}/{filename}"
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        modified = False

        # Add CSS if missing
        if 'cyberpunk-ui.css' not in content and 'nature-theme.css' not in content:
            if '</head>' in content:
                css_links = '''    <link rel="stylesheet" href="../assets/css/cyberpunk-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
'''
                content = content.replace('</head>', css_links + '</head>')
                modified = True

        # Add body class if missing
        if '<body' in content and 'cyber-bg' not in content and 'nature-bg' not in content:
            # Simple replacement - add cyber-bg class
            content = re.sub(r'<body\s*>', '<body class="cyber-bg">', content)
            content = re.sub(r'<body\s+class="', r'<body class="cyber-bg ', content)
            modified = True

        if modified:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"✓ Fixed: {filename}")
        else:
            print(f"- Skipped: {filename}")

    except Exception as e:
        print(f"✗ Error fixing {filename}: {e}")

print("\n✅ All files processed!")
