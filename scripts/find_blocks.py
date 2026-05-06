import sys
import re

filename = sys.argv[1]
with open(filename, 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

ids = [
    'page-dashboard',
    'page-members',
    'page-laporan',
    'page-admin-monthly',
    'page-settings',
    'page-rekap',
    'page-laporan-bulanan',
    'page-monthly-form'
]

print(f"File: {filename}")
for target_id in ids:
    start_line = -1
    for i, line in enumerate(lines):
        if f'id="{target_id}"' in line:
            start_line = i
            break
    
    if start_line != -1:
        # Simple heuristic: find the closing div with same indentation?
        # Or just find the next "page-content"?
        # Or look for END of block.
        # Assuming indentation is consistent.
        # Let's check indentation of start line
        indent = len(lines[start_line]) - len(lines[start_line].lstrip())
        
        # Find closing div with same indent
        end_line = -1
        for i in range(start_line + 1, len(lines)):
            curr_indent = len(lines[i]) - len(lines[i].lstrip())
            if lines[i].strip() == '</div>' and curr_indent == indent:
                end_line = i
                break
        
        print(f"Block {target_id}: {start_line+1} to {end_line+1}")
    else:
        pass
