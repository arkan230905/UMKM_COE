<?php
/**
 * AUTOMATIC DEPLOYMENT SCRIPT FOR HOSTING
 * 
 * Script ini akan otomatis:
 * 1. Pull code terbaru dari GitHub
 * 2. Clear semua cache Laravel
 * 3. Verify deployment
 * 
 * Upload ke /var/www/html/ dan jalankan: php run_deployment_hosting.php
 */

echo "=== AUTOMATIC DEPLOYMENT SCRIPT ===\n\n";

$baseDir = '/var/www/html';

// Step 1: Pull latest code
echo "Step 1: Pulling latest code from GitHub...\n";
chdir($baseDir);

// Fix git ownership
exec('git config --global --add safe.directory /var/www/html 2>&1', $output1);

// Try to pull
exec('git pull origin main 2>&1', $output2, $returnVar);

if ($returnVar === 0) {
    echo "✅ Code pulled successfully!\n";
    foreach ($output2 as $line) {
        echo "   $line\n";
    }
} else {
    echo "⚠️  Pull failed, trying with sudo...\n";
    exec('sudo git pull origin main 2>&1', $output3, $returnVar2);
    
    if ($returnVar2 === 0) {
        echo "✅ Code pulled successfully with sudo!\n";
    } else {
        echo "❌ Failed to pull code. Output:\n";
        foreach ($output3 as $line) {
            echo "   $line\n";
        }
    }
}

echo "\n";

// Step 2: Clear all caches
echo "Step 2: Clearing all Laravel caches...\n";

$cacheCommands = [
    'cache:clear' => 'Application cache',
    'config:clear' => 'Configuration cache',
    'route:clear' => 'Route cache',
    'view:clear' => 'View cache',
];

foreach ($cacheCommands as $command => $description) {
    echo "   Clearing $description...\n";
    exec("php artisan $command 2>&1", $output, $returnVar);
    
    if ($returnVar === 0) {
        echo "   ✅ $description cleared\n";
    } else {
        echo "   ⚠️  Warning: Failed to clear $description\n";
    }
}

// Clear compiled views manually
echo "   Clearing compiled views manually...\n";
$viewPath = $baseDir . '/storage/framework/views';
if (is_dir($viewPath)) {
    $files = glob($viewPath . '/*');
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file);
            $count++;
        }
    }
    echo "   ✅ Deleted $count compiled view files\n";
}

echo "\n";

// Step 3: Set permissions
echo "Step 3: Setting permissions...\n";
exec('sudo chown -R www-data:www-data storage bootstrap/cache 2>&1', $output);
exec('sudo chmod -R 775 storage bootstrap/cache 2>&1', $output);
echo "✅ Permissions set\n";

echo "\n";

// Step 4: Verify deployment
echo "Step 4: Verifying deployment...\n";

$filesToCheck = [
    'app/Http/Controllers/CoaController.php',
    'app/Http/Controllers/VendorController.php',
    'app/Http/Controllers/PembelianController.php',
    'app/Http/Controllers/PenjualanController.php',
    'app/Http/Controllers/LaporanController.php',
    'app/Http/Controllers/ProduksiController.php',
    'app/Http/Controllers/PresensiController.php',
    'app/Http/Controllers/PenggajianController.php',
    'app/Http/Controllers/ExpensePaymentController.php',
    'app/Http/Controllers/PelunasanUtangController.php',
];

$fixedCount = 0;
$totalCount = count($filesToCheck);

foreach ($filesToCheck as $file) {
    $fullPath = $baseDir . '/' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        
        if (strpos($content, "where('user_id', auth()->id())") !== false) {
            echo "   ✅ " . basename($file) . " - HAS user_id filter\n";
            $fixedCount++;
        } else {
            echo "   ⚠️  " . basename($file) . " - NO user_id filter\n";
        }
    } else {
        echo "   ❌ " . basename($file) . " - FILE NOT FOUND\n";
    }
}

echo "\n";
echo "=== DEPLOYMENT SUMMARY ===\n";
echo "Fixed controllers: $fixedCount / $totalCount\n";
echo "\n";

if ($fixedCount === $totalCount) {
    echo "✅ ALL CONTROLLERS HAVE BEEN SECURED!\n";
} else {
    echo "⚠️  Some controllers may still need review\n";
}

echo "\n";
echo "=== DEPLOYMENT COMPLETE ===\n";
echo "\n";
echo "NEXT STEPS:\n";
echo "1. Test COA edit page: http://jobcost.eadtmanufaktur.com/master-data/coa/280/edit\n";
echo "2. Login as different users and verify data isolation\n";
echo "3. Test all transaksi pages\n";
echo "4. Test all laporan pages\n";
echo "\n";
echo "If you see any issues, check logs:\n";
echo "   tail -f storage/logs/laravel.log\n";
