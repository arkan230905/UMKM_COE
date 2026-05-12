<?php
/**
 * Force Deploy Script
 * Aggressively clears all caches and forces code reload
 */

$basePath = __DIR__;
chdir($basePath);

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║           FORCE DEPLOY & CACHE CLEARING                   ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Step 1: Pull latest code
echo "Step 1: Pulling latest code from GitHub...\n";
$output = shell_exec('git pull origin ghitha 2>&1');
echo $output . "\n";

// Step 2: Clear Laravel caches
echo "\nStep 2: Clearing Laravel caches...\n";
require_once $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

$commands = [
    'cache:clear',
    'config:clear',
    'route:clear',
    'view:clear',
    'optimize:clear',
];

foreach ($commands as $command) {
    echo "  - Running: php artisan $command\n";
    try {
        $kernel->call($command);
        echo "    ✅ Done\n";
    } catch (\Exception $e) {
        echo "    ⚠️  Error: " . $e->getMessage() . "\n";
    }
}

// Step 3: Clear bootstrap cache files
echo "\nStep 3: Clearing bootstrap cache files...\n";
$bootstrapCache = $basePath . '/bootstrap/cache';
if (is_dir($bootstrapCache)) {
    $files = glob($bootstrapCache . '/*');
    foreach ($files as $file) {
        if (is_file($file) && basename($file) !== '.gitignore') {
            unlink($file);
            echo "  - Deleted: " . basename($file) . "\n";
        }
    }
    echo "  ✅ Bootstrap cache cleared\n";
}

// Step 4: Clear PHP opcache
echo "\nStep 4: Clearing PHP opcache...\n";
if (extension_loaded('Zend OPcache')) {
    opcache_reset();
    echo "  ✅ OPcache cleared\n";
} else {
    echo "  ℹ️  OPcache not enabled\n";
}

// Step 5: Verify code
echo "\nStep 5: Verifying code changes...\n";
$controllerPath = $basePath . '/app/Http/Controllers/AkuntansiController.php';
$content = file_get_contents($controllerPath);

if (strpos($content, 'pr.harga_pokok') !== false && strpos($content, 'pr.hpp') === false) {
    echo "  ✅ Code is correct: pr.hpp has been replaced with pr.harga_pokok\n";
} else {
    echo "  ❌ Code verification failed!\n";
    if (strpos($content, 'pr.hpp') !== false) {
        echo "     Still contains old code: pr.hpp\n";
    }
}

// Step 6: Check file modification time
echo "\nStep 6: File information...\n";
$mtime = filemtime($controllerPath);
echo "  - Last modified: " . date('Y-m-d H:i:s', $mtime) . "\n";
echo "  - Current time: " . date('Y-m-d H:i:s') . "\n";

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║                  ✅ DEPLOYMENT COMPLETE                    ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "Next steps:\n";
echo "1. Visit: http://jobcost.eadtmanufaktur.com/akuntansi/laba-rugi\n";
echo "2. If error persists, restart PHP-FPM or Apache\n";
echo "3. Check logs: tail -f storage/logs/laravel.log\n";
?>
