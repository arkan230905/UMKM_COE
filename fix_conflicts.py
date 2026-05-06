#!/usr/bin/env python3
import re
import subprocess
import sys

# Find all files with conflict markers
try:
    result = subprocess.run(['git', 'grep', '-l', '^<<<<<<< HEAD'], 
                          capture_output=True, text=True, check=False)
    files = result.stdout.strip().split('\n') if result.stdout else []
except Exception as e:
    print(f"Error finding files: {e}")
    sys.exit(1)

if not files or files == ['']:
    print("No conflict markers found.")
    sys.exit(0)

fixed_count = 0
for filepath in files:
    if not filepath:
        continue
        
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Remove conflict markers - keep the version after =======
        # Pattern: <<<<<<< HEAD\n...content...\n=======\n...content...\n>>>>>>> hash
        pattern = r'<<<<<<< HEAD\n.*?\n=======\n(.*?)\n>>>>>>> [a-f0-9]+\n?'
        new_content = re.sub(pattern, r'\1\n', content, flags=re.DOTALL)
        
        # If still has markers, try simpler pattern
        if '<<<<<<< HEAD' in new_content:
            pattern2 = r'<<<<<<< HEAD.*?=======(.*?)>>>>>>> [a-f0-9]+'
            new_content = re.sub(pattern2, r'\1', new_content, flags=re.DOTALL)
        
        with open(filepath, 'w', encoding='utf-8', newline='') as f:
            f.write(new_content)
        
        print(f"✓ Fixed: {filepath}")
        fixed_count += 1
    except Exception as e:
        print(f"✗ Error fixing {filepath}: {e}")

print(f"\n✓ Done! Fixed {fixed_count} files.")
