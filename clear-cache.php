<?php
/**
 * Cache Clearing Script
 * Run this after deployment to clear all Laravel caches
 */

// Set the base path
$basePath = __DIR__;

// Change to the application directory
chdir($basePath);

// Include Laravel's autoloader
require_once $basePath . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once $basePath . '/bootstrap/app.php';

// Get the kernel
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

// Run cache clearing commands
echo "Clearing application cache...\n";
$kernel->call('cache:clear');

echo "Clearing config cache...\n";
$kernel->call('config:clear');

echo "Clearing route cache...\n";
$kernel->call('route:clear');

echo "Clearing view cache...\n";
$kernel->call('view:clear');

echo "Clearing compiled classes...\n";
$kernel->call('optimize:clear');

echo "\n✅ All caches cleared successfully!\n";
?>
