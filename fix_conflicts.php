<?php

// Find all files with conflict markers
exec('git grep -l "^<<<<<<< HEAD"', $files, $return_code);

if (empty($files)) {
    echo "No conflict markers found.\n";
    exit(0);
}

$fixed_count = 0;
foreach ($files as $filepath) {
    if (empty($filepath)) continue;
    
    if (!file_exists($filepath)) {
        echo "✗ File not found: $filepath\n";
        continue;
    }
    
    $content = file_get_contents($filepath);
    
    // Remove conflict markers - keep the version after =======
    // Pattern: <<<<<<< HEAD\n...content...\n=======\n...content...\n>>>>>>> hash
    $pattern = '/<<<<<<< HEAD.*?=======(.*?)>>>>>>> [a-f0-9]+\s*/s';
    $new_content = preg_replace($pattern, '$1', $content);
    
    file_put_contents($filepath, $new_content);
    
    echo "✓ Fixed: $filepath\n";
    $fixed_count++;
}

echo "\n✓ Done! Fixed $fixed_count files.\n";
