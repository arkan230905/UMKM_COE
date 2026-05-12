<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG OBSERVER DEEP ===\n\n";

echo "1. CEK APAKAH OBSERVER MASIH AKTIF:\n\n";

try {
    // Test observer behavior by simulating update
    $produk = \App\Models\Produk::find(2);
    
    if ($produk) {
        echo "Current product state:\n";
        echo "  Harga Pokok: " . $produk->harga_pokok . "\n";
        
        // Check what happens when we update
        $produk->harga_pokok = 1234.56;
        
        // Check dirty fields BEFORE save
        $dirtyFields = $produk->getDirty();
        echo "Dirty fields before save: " . json_encode($dirtyFields) . "\n";
        
        // Simulate observer logic
        $pricingFields = ['harga_bom', 'harga_jual', 'biaya_bahan', 'margin_percent', 'harga_pokok'];
        $nonPricingChanges = array_diff(array_keys($dirtyFields), $pricingFields);
        
        echo "Non-pricing changes: " . json_encode(array_values($nonPricingChanges)) . "\n";
        
        if (empty($nonPricingChanges)) {
            echo "✅ Observer should SKIP recalculation\n";
        } else {
            echo "❌ Observer will RUN recalculation\n";
        }
        
        // Save and check result
        $produk->save();
        $produk->refresh();
        
        echo "Harga pokok after save: " . $produk->harga_pokok . "\n";
        
        if ($produk->harga_pokok == 1234.56) {
            echo "✅ Harga pokok preserved\n";
        } else {
            echo "❌ Harga pokok was reset\n";
            
            // Check if there's another observer or event
            echo "Checking for other observers...\n";
        }
        
        // Reset
        $produk->harga_pokok = 0;
        $produk->save();
    }
    
} catch (\Exception $e) {
    echo "Error testing observer: " . $e->getMessage() . "\n";
}

echo "\n2. CEK APAKAH ADA EVENT LAIN:\n\n";

try {
    // Check if there are other events that might be triggering
    echo "Checking for other model events...\n";
    
    // Check if there are any other observers
    $observers = [
        'App\Observers\ProdukObserver',
        'App\Observers\BomJobCostingObserver',
        'App\Observers\BomJobBBBObserver',
    ];
    
    foreach ($observers as $observerClass) {
        if (class_exists($observerClass)) {
            echo "✅ Found observer: " . $observerClass . "\n";
        } else {
            echo "❌ Observer not found: " . $observerClass . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking observers: " . $e->getMessage() . "\n";
}

echo "\n3. CEK BOOT METHOD DI PRODUK MODEL:\n\n";

try {
    $modelFile = 'c:\UMKM_COE\app\Models\Produk.php';
    $modelContent = file_get_contents($modelFile);
    
    if (strpos($modelContent, 'boot()') !== false) {
        echo "✅ Found boot method in Produk model\n";
        
        // Extract boot method
        if (preg_match('/protected static function boot\(\)(.*?)^}/sm', $modelContent, $matches)) {
            $bootMethod = $matches[0];
            echo "Boot method content:\n";
            echo $bootMethod . "\n";
            
            // Check for problematic events
            if (strpos($bootMethod, 'updated') !== false) {
                echo "⚠️ Found 'updated' event in boot method\n";
            }
            
            if (strpos($bootMethod, 'saving') !== false) {
                echo "⚠️ Found 'saving' event in boot method\n";
            }
        }
    } else {
        echo "❌ No boot method found in Produk model\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking boot method: " . $e->getMessage() . "\n";
}

echo "\n4. TEST DENGAN OBSERVER DI NONAKTIFKAN:\n\n";

try {
    echo "Testing without observer by temporarily removing it...\n";
    
    // Temporarily remove observer
    \App\Models\Produk::flushEventListeners();
    
    $produk = \App\Models\Produk::find(2);
    if ($produk) {
        $produk->harga_pokok = 9999.99;
        $produk->save();
        $produk->refresh();
        
        echo "Harga pokok without observer: " . $produk->harga_pokok . "\n";
        
        if ($produk->harga_pokok == 9999.99) {
            echo "✅ Works without observer - observer is the problem\n";
        } else {
            echo "❌ Still doesn't work - problem is elsewhere\n";
        }
        
        // Reset
        $produk->harga_pokok = 0;
        $produk->save();
    }
    
    // Re-add observer
    \App\Models\Produk::observe(new \App\Observers\ProdukObserver());
    
} catch (\Exception $e) {
    echo "Error testing without observer: " . $e->getMessage() . "\n";
}

echo "\n5. CEK LARAVEL LOGS UNTUK EVENT TERBARU:\n\n";

try {
    $logFile = 'c:\UMKM_COE\storage\logs\laravel.log';
    
    if (file_exists($logFile)) {
        $lines = file($logFile);
        $lastLines = array_slice($lines, -10);
        
        echo "Last 10 lines of Laravel log:\n";
        foreach ($lastLines as $line) {
            echo trim($line) . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking logs: " . $e->getMessage() . "\n";
}

echo "\n6. SOLUTION:\n\n";

echo "Based on analysis:\n";
echo "1. If observer is the problem, we need to fix the logic\n";
echo "2. If boot method is the problem, we need to fix it\n";
echo "3. If there are other events, we need to identify them\n\n";

echo "=== DEBUG COMPLETE ===\n";
