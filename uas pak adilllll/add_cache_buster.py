import os
import glob

directory = r"c:\laragon\www\uas pak adilllll"
pattern = os.path.join(directory, "*.php")

target_str = '<link rel="stylesheet" href="style.css">'
replacement_str = '<link rel="stylesheet" href="style.css?v=<?= time() ?>">'

for filepath in glob.glob(pattern):
    with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
    
    if target_str in content:
        content = content.replace(target_str, replacement_str)
        with open(filepath, 'w', encoding='utf-8', errors='ignore') as f:
            f.write(content)
        print(f"Updated {os.path.basename(filepath)}")

print("Done.")
