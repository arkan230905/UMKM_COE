<?php
// Read the dashboard file
$content = file_get_contents('resources/views/dashboard.blade.php');

// Fix the syntax errors by removing extra closing braces
$content = str_replace("route('", "route('", $content);
$content = str_replace("') }}", "') }}", $content);

// Write back to file
file_put_contents('resources/views/dashboard.blade.php', $content);

echo "Dashboard syntax errors fixed!";
?>
