<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST OBSERVER WITH DEBUG ===\n\n";

echo "1. TEST UPDATE DENGAN DEBUG LOGGING:\n\n";

try {
    // Clear recent logs
    $logFile = 'c:\UMKM_COE\storage\logs\laravel.log';
    if (file_exists($logFile)) {
        // Keep only last 5 lines to see new logs clearly
        $lines = file($logFile);
        $recentLines = array_slice($lines, -5);
        file_put_contents($logFile, implode('', $recentLines));
    }
    
    // Test update with debug logging
    $produk = \App\Models\Produk::find(2);
    
    if ($produk) {
        echo "Testing update with debug logging...\n";
        
        $produk->harga_pokok = 5555.55;
        $produk->save();
        
        echo "✅ Update completed\n";
        
        // Check result
        $produk->refresh();
        echo "Harga pokok after save: " . $produk->harga_pokok . "\n";
        
        if ($produk->harga_pokok == 5555.55) {
            echo "✅ Harga pokok preserved\n";
        } else {
            echo "❌ Harga pokok was reset\n";
        }
        
        // Check new logs
        echo "\nNew log entries:\n";
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $newLines = array_slice($lines, -10);
            foreach ($newLines as $line) {
                echo trim($line) . "\n";
            }
        }
        
        // Reset
        $produk->harga_pokok = 0;
        $produk->save();
    }
    
} catch (\Exception $e) {
    echo "Error testing with debug: " . $e->getMessage() . "\n";
}

echo "\n2. ANALISIS HASIL:\n\n";

echo "Based on the debug logs:\n";
echo "1. Check what fields are detected as changed\n";
echo "2. Check if non-pricing changes is empty\n";
echo "3. Check if recalculation is skipped\n";
echo "4. Check if there are multiple events triggered\n\n";

echo "3. ALTERNATIVE SOLUTION:\n\n";

echo "If observer still causes issues, we can:\n";
echo "1. Temporarily disable observer for HPP updates\n";
echo "2. Use withoutEvents() method for HPP updates\n";
echo "3. Check if there are other events causing the reset\n\n";

echo "=== TEST COMPLETE ===\n";
