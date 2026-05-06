<?php
/**
 * DEPLOY AND CLEAR CACHE SCRIPT FOR HOSTING
 * 
 * Script ini akan:
 * 1. Pull latest code dari GitHub
 * 2. Clear semua cache Laravel
 * 3. Verify deployment
 * 
 * Upload script ini ke /var/www/html/ di hosting
 * Jalankan: php deploy_and_clear_cache_hosting.php
 */

echo "=== DEPLOY AND CLEAR CACHE SCRIPT ===\n\n";

// Step 1: Pull latest code
echo "Step 1: Pulling latest code from GitHub...\n";
$output = [];
$returnVar = 0;

// Fix git ownership issue first
exec('git config --global --add safe.directory /var/www/html 2>&1', $output, $returnVar);

// Pull code
exec('cd /var/www/html && git pull origin main 2>&1', $output, $returnVar);

if ($returnVar === 0) {
    echo "✅ Code pulled successfully!\n";
    foreach ($output as $line) {
        echo "   $line\n";
    }
} else {
    echo "❌ Failed to pull code!\n";
    foreach ($output as $line) {
        echo "   $line\n";
    }
    echo "\nTrying alternative method...\n";
    
    // Try with sudo if permission denied
    exec('sudo git pull origin main 2>&1', $output2, $returnVar2);
    if ($returnVar2 === 0) {
        echo "✅ Code pulled successfully with sudo!\n";
    } else {
        echo "❌ Still failed. Please run manually:\n";
        echo "   ssh simcost@103.134.154.77\n";
        echo "   cd /var/www/html\n";
        echo "   sudo git pull origin main\n";
    }
}

echo "\n";

// Step 2: Clear all caches
echo "Step 2: Clearing all Laravel caches...\n";

$cacheCommands = [
    'php artisan cache:clear' => 'Application cache',
    'php artisan config:clear' => 'Configuration cache',
    'php artisan route:clear' => 'Route cache',
    'php artisan view:clear' => 'View cache',
];

foreach ($cacheCommands as $command => $description) {
    echo "   Clearing $description...\n";
    $output = [];
    exec("cd /var/www/html && $command 2>&1", $output, $returnVar);
    
    if ($returnVar === 0) {
        echo "   ✅ $description cleared\n";
    } else {
        echo "   ⚠️  Warning: $description clear failed\n";
        foreach ($output as $line) {
            echo "      $line\n";
        }
    }
}

// Clear compiled views manually
echo "   Clearing compiled views manually...\n";
$viewPath = '/var/www/html/storage/framework/views';
if (is_dir($viewPath)) {
    $files = glob($viewPath . '/*');
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $count++;
        }
    }
    echo "   ✅ Deleted $count compiled view files\n";
} else {
    echo "   ⚠️  View directory not found\n";
}

echo "\n";

// Step 3: Verify deployment
echo "Step 3: Verifying deployment...\n";

$filesToCheck = [
    '/var/www/html/app/Http/Controllers/CoaController.php',
    '/var/www/html/app/Http/Controllers/VendorController.php',
    '/var/www/html/app/Http/Controllers/PembelianController.php',
    '/var/www/html/app/Http/Controllers/PenjualanController.php',
    '/var/www/html/app/Http/Controllers/LaporanController.php',
];

foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Check if user_id filter exists
        if (strpos($content, "where('user_id', auth()->id())") !== false) {
            echo "   ✅ " . basename($file) . " - HAS user_id filter\n";
        } else {
            echo "   ⚠️  " . basename($file) . " - NO user_id filter found (might be OK if not needed)\n";
        }
    } else {
        echo "   ❌ " . basename($file) . " - FILE NOT FOUND\n";
    }
}

echo "\n";

// Step 4: Check database connection
echo "Step 4: Checking database connection...\n";
try {
    require_once '/var/www/html/vendor/autoload.php';
    $app = require_once '/var/www/html/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $pdo = DB::connection()->getPdo();
    echo "   ✅ Database connection OK\n";
    
    // Count users
    $userCount = DB::table('users')->count();
    echo "   ℹ️  Total users in database: $userCount\n";
    
} catch (Exception $e) {
    echo "   ⚠️  Database check failed: " . $e->getMessage() . "\n";
}

echo "\n";
echo "=== DEPLOYMENT COMPLETE ===\n";
echo "\n";
echo "NEXT STEPS:\n";
echo "1. Test COA edit page: http://jobcost.eadtmanufaktur.com/master-data/coa/280/edit\n";
echo "2. Login as different users and verify data isolation\n";
echo "3. Check transaksi pembelian and penjualan\n";
echo "4. Check all laporan pages\n";
echo "\n";
echo "If you see any issues, check the logs:\n";
echo "   tail -f /var/www/html/storage/logs/laravel.log\n";
