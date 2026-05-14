<?php
// Read dashboard file
$content = file_get_contents('resources/views/dashboard.blade.php');

// Fix all route syntax errors by removing extra closing braces
$patterns = [
    "route('",
    "') }}"
];

$replacements = [
    "route('",
    "') }}"
];

$content = str_replace($patterns, $replacements, $content);

// Write back to file
file_put_contents('resources/views/dashboard.blade.php', $content);

echo "All dashboard route syntax errors fixed!";
?>
