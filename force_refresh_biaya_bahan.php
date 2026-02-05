<?php

// Script to add cache-busting to the biaya bahan create page

$filePath = 'resources/views/master-data/biaya-bahan/create.blade.php';
$content = file_get_contents($filePath);

// Add cache-busting timestamp to force browser to reload JavaScript
$timestamp = time();

// Find the @push('scripts') section and add cache-busting comment
$searchPattern = "@push('scripts')";
$replacePattern = "@push('scripts')\n<!-- Cache Buster: $timestamp -->";

$newContent = str_replace($searchPattern, $replacePattern, $content);

// Also add a force refresh meta tag
$headPattern = '<div class="container-fluid px-4 py-4">';
$headReplace = '<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<div class="container-fluid px-4 py-4">';

$newContent = str_replace($headPattern, $headReplace, $newContent);

// Write back to file
if (file_put_contents($filePath, $newContent)) {
    echo "SUCCESS: Cache-busting added to biaya bahan create page\n";
    echo "Timestamp: $timestamp\n";
    echo "\nNow please:\n";
    echo "1. Close all browser tabs with the biaya bahan page\n";
    echo "2. Clear browser cache completely (Ctrl+Shift+Delete)\n";
    echo "3. Restart your browser\n";
    echo "4. Open the page fresh: /master-data/biaya-bahan/create/2\n";
    echo "5. Open console (F12) and check for JavaScript errors\n";
} else {
    echo "ERROR: Could not write to file\n";
}