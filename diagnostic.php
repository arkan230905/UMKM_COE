<?php
/**
 * Diagnostic Script
 * Check what code is actually running on the server
 */

// Set the base path
$basePath = __DIR__;

// Read the PerusahaanController file
$controllerPath = $basePath . '/app/Http/Controllers/PerusahaanController.php';
$content = file_get_contents($controllerPath);

// Check if the file contains the old code
if (strpos($content, "Perusahaan::where('user_id', auth()->id())") !== false) {
    echo "❌ OLD CODE DETECTED: File still contains 'where user_id' filter\n";
    echo "The deployment hasn't updated the files yet!\n";
} else if (strpos($content, "Perusahaan::first()") !== false) {
    echo "✅ NEW CODE DETECTED: File has been updated correctly\n";
} else {
    echo "⚠️  UNKNOWN STATE: Cannot determine code version\n";
}

// Check file modification time
$mtime = filemtime($controllerPath);
echo "\nFile last modified: " . date('Y-m-d H:i:s', $mtime) . "\n";
echo "Current time: " . date('Y-m-d H:i:s') . "\n";

// Check if opcache is enabled
if (extension_loaded('Zend OPcache')) {
    echo "\n⚠️  Zend OPcache is ENABLED\n";
    echo "This might be caching old code. You need to clear opcache!\n";
    echo "Run: php -r 'opcache_reset();'\n";
} else {
    echo "\n✅ Zend OPcache is not enabled\n";
}

// Check Laravel cache
echo "\n--- Laravel Cache Status ---\n";
$cacheDir = $basePath . '/bootstrap/cache';
if (is_dir($cacheDir)) {
    $files = scandir($cacheDir);
    $cacheFiles = array_filter($files, function($f) { return $f !== '.' && $f !== '..' && $f !== '.gitignore'; });
    echo "Cache files found: " . count($cacheFiles) . "\n";
    if (count($cacheFiles) > 0) {
        echo "⚠️  Cache files exist. They should be cleared!\n";
    }
}
?>
