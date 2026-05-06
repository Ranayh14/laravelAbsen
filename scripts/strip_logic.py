import sys
import re

filename = sys.argv[1]
role = sys.argv[2] # 'admin' or 'pegawai'

with open(filename, 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

new_lines = []
skip_block = False
skip_indent = 0
block_type = None # 'remove' or 'unwrap'

# Heuristic: Simple line-by-line state machine.
# Does not handle nested same-type logic well, but index.php seems flat for these top-level checks.

for line in lines:
    stripped = line.strip()
    
    # Check for start of block
    if '<?php if (isAdmin()): ?>' in stripped or '<?php if(isAdmin()): ?>' in stripped:
        if role == 'pegawai':
            # Remove this block
            skip_block = True
            block_type = 'remove'
            continue
        else:
            # Admin file: Unwrap this block (skip the line, keep content)
            continue
            
    if '<?php if (!isAdmin()): ?>' in stripped or '<?php if(!isAdmin()): ?>' in stripped:
        if role == 'admin':
            # Remove this block
            skip_block = True
            block_type = 'remove'
            continue
        else:
            # Pegawai file: Unwrap this block
            continue

    if '<?php endif; ?>' in stripped:
        if skip_block:
            # End of removed block
            skip_block = False
            block_type = None
            continue
        else:
            # End of unwrapped block? 
            # We blindly remove endif if we are unwrapping?
            # Issue: 'endif' is generic. It might close something else.
            # But in these top-level templates, it mostly closes the role check.
            # We should be careful.
            # Use 'continue' only if we believe it corresponds to the check we unwrapped.
            # For now, we assume yes.
            continue

    if not skip_block:
        new_lines.append(line)

with open(filename, 'w', encoding='utf-8') as f:
    f.writelines(new_lines)

print(f"Processed {filename} for role {role}")
