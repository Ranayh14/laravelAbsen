import sys
filename = sys.argv[1]
with open(filename, 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()
    print(f"Total lines: {len(lines)}")
    if len(lines) > 6937:
        print(f"Line 6937: {lines[6937]}")
        print(f"Line 6938: {lines[6938]}")
    else:
        print("Line 6937 not found")
