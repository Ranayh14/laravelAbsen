import sys
import os

try:
    src = sys.argv[1]
    dst = sys.argv[2]
    skip = int(sys.argv[3])
    count = int(sys.argv[4])

    print(f"Reading {src}...")
    with open(src, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()
    
    print(f"Total lines: {len(lines)}")
    print(f"Extracting lines {skip} to {skip+count}...")
    
    content = lines[skip:skip+count]
    
    with open(dst, 'w', encoding='utf-8') as f:
        f.writelines(content)
        
    print(f"Wrote {len(content)} lines to {dst}")

except Exception as e:
    print(f"Error: {e}")
    sys.exit(1)
