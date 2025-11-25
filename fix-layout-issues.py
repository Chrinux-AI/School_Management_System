#!/usr/bin/env python3
"""
Comprehensive CSS and Layout Fixer
Fixes excessive spacing, overflow issues, and layout problems across the School Management System
"""

import os
import re
import sys
from pathlib import Path

# Base directory
BASE_DIR = "/opt/lampp/htdocs/attendance"

# Track changes
changes_made = []
files_modified = []

def fix_css_overflow_issues(css_file):
    """Fix overflow issues in CSS files"""
    try:
        with open(css_file, 'r', encoding='utf-8') as f:
            content = f.read()

        original = content
        modified = False

        # Fix body/html overflow issues - ensure they allow scrolling
        # Replace body { ... overflow: hidden ... } with overflow-y: auto
        if re.search(r'body\s*{[^}]*overflow:\s*hidden', content, re.DOTALL):
            content = re.sub(
                r'(body\s*{[^}]*?)overflow:\s*hidden',
                r'\1overflow-y: auto',
                content,
                flags=re.DOTALL
            )
            modified = True
            changes_made.append(f"Fixed body overflow in {css_file}")

        # Ensure .cyber-layout doesn't have overflow hidden
        content = re.sub(
            r'(\.cyber-layout\s*{[^}]*?)overflow:\s*hidden',
            r'\1overflow-y: auto',
            content,
            flags=re.DOTALL
        )

        # Fix excessive padding/margins (> 200px)
        if re.search(r'(padding|margin)(-\w+)?:\s*[3-9]\d{2,}px', content):
            def reduce_spacing(match):
                property_name = match.group(1)
                direction = match.group(2) or ''
                value = int(match.group(3))
                # Cap at 100px maximum
                new_value = min(value, 100)
                changes_made.append(f"Reduced {property_name}{direction} from {value}px to {new_value}px in {css_file}")
                return f"{property_name}{direction}: {new_value}px"

            content = re.sub(
                r'(padding|margin)(-\w+)?:\s*([3-9]\d{2,})px',
                reduce_spacing,
                content
            )
            modified = True

        if content != original:
            with open(css_file, 'w', encoding='utf-8') as f:
                f.write(content)
            files_modified.append(css_file)
            return True

        return False

    except Exception as e:
        print(f"Error processing {css_file}: {e}")
        return False

def fix_php_inline_styles(php_file):
    """Fix inline styles in PHP files"""
    try:
        with open(php_file, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()

        original = content

        # Fix excessive inline padding/margins
        content = re.sub(
            r'style=["\']([^"\']*)(padding|margin)(-\w+)?:\s*([3-9]\d{2,})px',
            lambda m: f'style="{m.group(1)}{m.group(2)}{m.group(3) or ""}: {min(int(m.group(4)), 100)}px',
            content
        )

        # Fix overflow hidden on main containers
        content = re.sub(
            r'(class=["\'][^"\']*(?:cyber-bg|cyber-main|main-content)[^"\']*["\'][^>]*style=["\'][^"\']*?)overflow:\s*hidden',
            r'\1overflow-y: auto',
            content
        )

        if content != original:
            with open(php_file, 'w', encoding='utf-8') as f:
                f.write(content)
            files_modified.append(php_file)
            changes_made.append(f"Fixed inline styles in {php_file}")
            return True

        return False

    except Exception as e:
        print(f"Error processing {php_file}: {e}")
        return False

def scan_and_fix():
    """Scan and fix all CSS and PHP files"""
    print("🔧 Starting comprehensive CSS and layout fix...\n")

    # Fix CSS files
    css_dir = os.path.join(BASE_DIR, "assets", "css")
    if os.path.exists(css_dir):
        print("📁 Processing CSS files...")
        for css_file in Path(css_dir).glob("*.css"):
            if fix_css_overflow_issues(str(css_file)):
                print(f"  ✓ Fixed: {css_file.name}")

    # Fix PHP files with inline styles
    print("\n📁 Processing PHP files...")
    php_count = 0
    for php_file in Path(BASE_DIR).rglob("*.php"):
        # Skip vendor and backup directories
        if 'vendor' in str(php_file) or '_backups' in str(php_file):
            continue

        if fix_php_inline_styles(str(php_file)):
            php_count += 1
            if php_count <= 10:  # Show first 10
                print(f"  ✓ Fixed: {php_file.relative_to(BASE_DIR)}")

    if php_count > 10:
        print(f"  ... and {php_count - 10} more files")

    # Summary
    print("\n" + "="*60)
    print(f"✅ SUMMARY")
    print("="*60)
    print(f"Files modified: {len(files_modified)}")
    print(f"Total changes: {len(changes_made)}")

    if changes_made:
        print("\n📝 Changes made:")
        for i, change in enumerate(changes_made[:20], 1):
            print(f"  {i}. {change}")
        if len(changes_made) > 20:
            print(f"  ... and {len(changes_made) - 20} more changes")

    print("\n✨ Done!\n")

if __name__ == "__main__":
    scan_and_fix()
