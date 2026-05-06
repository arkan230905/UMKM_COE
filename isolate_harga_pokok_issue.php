<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ISOLATE HARGA_POKOK ISSUE ===\n\n";

echo "1. TEST TANPA OBSERVER SAMA SEKALI:\n\n";

try {
    // Completely remove all observers
    \App\Models\Produk::flushEventListeners();
    
    echo "All observers removed\n";
    
    // Test update
    $produk = \App\Models\Produk::find(2);
    
    if ($produk) {
        echo "Testing update without ANY observers...\n";
        
        $produk->harga_pokok = 8888.88;
        $produk->save();
        
        echo "✅ Update completed\n";
        
        // Check result
        $produk->refresh();
        echo "Harga pokok after save: " . $produk->harga_pokok . "\n";
        
        if ($produk->harga_pokok == 8888.88) {
            echo "✅ SUCCESS: Works without observers - observer is the problem\n";
        } else {
            echo "❌ FAILED: Still doesn't work - problem is NOT observer\n";
            
            // Check if there's a mutator or accessor
            echo "Checking for mutators/accessors...\n";
            
            // Check if there's a setHargaPokokAttribute method
            if (method_exists($produk, 'setHargaPokokAttribute')) {
                echo "❌ Found setHargaPokokAttribute mutator - this might be the problem\n";
            } else {
                echo "✅ No setHargaPokokAttribute mutator found\n";
            }
            
            // Check if there's a getHargaPokokAttribute method
            if (method_exists($produk, 'getHargaPokokAttribute')) {
                echo "❌ Found getHargaPokokAttribute accessor - this might be the problem\n";
            } else {
                echo "✅ No getHargaPokokAttribute accessor found\n";
            }
        }
        
        // Reset
        $produk->harga_pokok = 0;
        $produk->save();
    }
    
    // Re-add observer
    \App\Models\Produk::observe(new \App\Observers\ProdukObserver());
    
} catch (\Exception $e) {
    echo "Error testing without observers: " . $e->getMessage() . "\n";
}

echo "\n2. CEK MODEL MUTATORS/ACCESSORS:\n\n";

try {
    $modelFile = 'c:\UMKM_COE\app\Models\Produk.php';
    $modelContent = file_get_contents($modelFile);
    
    // Check for harga_pokok related methods
    if (strpos($modelContent, 'getHargaPokokAttribute') !== false) {
        echo "❌ Found getHargaPokokAttribute in model\n";
        
        // Extract the method
        if (preg_match('/public function getHargaPokokAttribute\(\)(.*?)^}/sm', $modelContent, $matches)) {
            echo "Method content:\n";
            echo $matches[0] . "\n";
        }
    } else {
        echo "✅ No getHargaPokokAttribute found\n";
    }
    
    if (strpos($modelContent, 'setHargaPokokAttribute') !== false) {
        echo "❌ Found setHargaPokokAttribute in model\n";
    } else {
        echo "✅ No setHargaPokokAttribute found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking model methods: " . $e->getMessage() . "\n";
}

echo "\n3. CEK BOOT METHOD UNTUK MUTATORS:\n\n";

try {
    $modelFile = 'c:\UMKM_COE\app\Models\Produk.php';
    $modelContent = file_get_contents($modelFile);
    
    // Check boot method for any harga_pokok manipulation
    if (strpos($modelContent, 'harga_pokok') !== false) {
        echo "❌ Found harga_pokok references in model\n";
        
        // Find all lines with harga_pokok
        $lines = explode("\n", $modelContent);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, 'harga_pokok') !== false) {
                echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
    } else {
        echo "✅ No harga_pokok references in model\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking boot method: " . $e->getMessage() . "\n";
}

echo "\n4. CEK APAKAH ADA HPP FIELD:\n\n";

try {
    $produk = \App\Models\Produk::find(2);
    
    if ($produk) {
        echo "Checking for hpp field...\n";
        echo "hpp: " . ($produk->hpp ?? 'NULL') . "\n";
        echo "harga_pokok: " . ($produk->harga_pokok ?? 'NULL') . "\n";
        
        // Check if there's a relationship between hpp and harga_pokok
        if (method_exists($produk, 'getHargaPokokAttribute')) {
            echo "❌ getHargaPokokAttribute exists - might be returning hpp value\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking hpp field: " . $e->getMessage() . "\n";
}

echo "\n5. SOLUTION:\n\n";

echo "Based on analysis:\n";
echo "1. If observer is the problem, we need to fix it completely\n";
echo "2. If there's a mutator/accessor, we need to fix it\n";
echo "3. If there's a boot method issue, we need to fix it\n";
echo "4. If there's an hpp field relationship, we need to fix it\n\n";

echo "=== ISOLATION COMPLETE ===\n";
