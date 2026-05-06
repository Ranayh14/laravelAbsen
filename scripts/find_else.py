import sys
filename = sys.argv[1]
with open(filename, 'r', encoding='utf-8', errors='ignore') as f:
    for i, line in enumerate(f):
        if '<?php else: ?>' in line:
            print(f"Start (else): {i}")
        if '<?php endif; ?>' in line:
            print(f"End (endif): {i}")
