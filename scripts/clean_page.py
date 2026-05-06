import sys

filename = sys.argv[1]
ids_to_remove = sys.argv[2].split(',')

with open(filename, 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

lines_to_delete = set()

for target_id in ids_to_remove:
    target_id = target_id.strip()
    start_line = -1
    for i, line in enumerate(lines):
        if f'id="{target_id}"' in line:
            start_line = i
            break
    
    if start_line != -1:
        indent_len = len(lines[start_line]) - len(lines[start_line].lstrip())
        
        end_line = -1
        # Look for closing div with same indent
        for i in range(start_line + 1, len(lines)):
            curr_indent = len(lines[i]) - len(lines[i].lstrip())
            # We assume standard formatting: </div> on its own line
            if lines[i].strip() == '</div>' and curr_indent == indent_len:
                end_line = i
                break
        
        if end_line != -1:
            print(f"Removing block {target_id}: Lines {start_line+1} to {end_line+1}")
            for i in range(start_line, end_line + 1):
                lines_to_delete.add(i)
        else:
            print(f"Warning: Could not find closing div for {target_id}")
    else:
        print(f"Warning: Could not find block {target_id}")

new_lines = [line for i, line in enumerate(lines) if i not in lines_to_delete]

with open(filename, 'w', encoding='utf-8') as f:
    f.writelines(new_lines)

print(f"Cleaned {filename}. Removed {len(lines_to_delete)} lines.")
