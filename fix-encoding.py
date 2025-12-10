#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import sys
import re

def fix_encoding(filepath):
    """Fix double-encoded UTF-8 in PHP file"""
    
    # Read file in binary mode
    with open(filepath, 'rb') as f:
        content_bytes = f.read()
    
    # Try to decode as UTF-8
    try:
        content = content_bytes.decode('utf-8')
    except UnicodeDecodeError:
        content = content_bytes.decode('latin-1')
    
    # Define replacements for corrupted strings
    replacements = [
        (r'unerwÃƒÆ.Ã‚Â¤Ãƒâ€šÃ‚Â¼nschte', 'unerwünschte'),
        (r'PrÃƒÆ.Ã‚Â¤Ãƒâ€šÃ‚Â¼fung', 'Prüfung'),
        (r'BerechtigungsPrÃƒÆ.Ã‚Â¤Ãƒâ€šÃ‚Â¼fung', 'Berechtigungsprüfung'),
        (r'fÃƒÆ.Ã‚Â¤Ãƒâ€šÃ‚Â¼r', 'für'),
    ]
    
    changed = False
    for pattern, replacement in replacements:
        if re.search(pattern, content):
            content = re.sub(pattern, replacement, content)
            print(f"✓ Replaced pattern: {pattern[:20]}...")
            changed = True
    
    # Add missing return statement after wp_send_json_error in ajax_sync_calendars
    pattern = r"(wp_send_json_error\( array\(\s*'message' => __\( 'Keine Berechtigung [^)]+\)\s*\) \);)\s*\n\s*\}"
    if re.search(pattern, content):
        content = re.sub(pattern, r"\1\n\t\treturn;\n\t}", content)
        print("✓ Added missing return statement")
        changed = True
    
    if changed:
        # Write back as UTF-8 without BOM
        with open(filepath, 'w', encoding='utf-8', newline='\n') as f:
            f.write(content)
        print(f"✓ File saved: {filepath}")
        return 0
    else:
        print("No changes needed")
        return 1

if __name__ == '__main__':
    if len(sys.argv) != 2:
        print("Usage: python fix-encoding.py <filepath>")
        sys.exit(1)
    
    sys.exit(fix_encoding(sys.argv[1]))
