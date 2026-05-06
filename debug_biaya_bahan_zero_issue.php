<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG BIAYA BAHAN ZERO ISSUE ===\n\n";

echo "1. CHECK CURRENT BOMCONTROLLER CODE:\n\n";

try {
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BiayaBahanController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    // Check if our changes are still there
    if (strpos($controllerContent, 'Get BBB data directly from bom_job_bbb table') !== false) {
        echo "✅ Our BBB query changes are present\n";
    } else {
        echo "❌ Our BBB query changes are NOT present\n";
    }
    
    // Check if the old complex logic is still there
    if (strpos($controllerContent, 'detailBBB->sum(\'subtotal\')') !== false) {
        echo "❌ Old complex logic still present\n";
    } else {
        echo "✅ Old complex logic removed\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking controller: " . $e->getMessage() . "\n";
}

echo "\n2. TEST ACTUAL CONTROLLER EXECUTION:\n\n";

try {
    echo "Simulating actual BiayaBahanController@index execution...\n";
    
    // Get products
    $query = \App\Models\Produk::query()->where('user_id', 1);
    $produks = $query->orderBy('nama_produk')->get();
    
    echo "Found " . $produks->count() . " products\n";
    
    $produkBiaya = [];
    
    foreach ($produks as $produk) {
        echo "\nProcessing product: " . $produk->nama_produk . " (ID: " . $produk->id . ")\n";
        
        // Get BomJobCosting
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)
            ->where('user_id', 1)
            ->first();
        
        echo "  BomJobCosting: " . ($bomJobCosting ? "Found (ID: " . $bomJobCosting->id . ")" : "Not found") . "\n";
        
        // Get BBB data using the exact same query as in controller
        $bbbData = \Illuminate\Support\Facades\DB::table('bom_job_bbb as bbb')
            ->leftJoin('bahan_bakus as bb', 'bbb.bahan_baku_id', '=', 'bb.id')
            ->leftJoin('satuans as s', 'bb.satuan_id', '=', 's.id')
            ->where('bbb.user_id', 1)
            ->where('bbb.produk_id', $produk->id)
            ->select(
                'bbb.id',
                'bb.nama_bahan',
                'bbb.jumlah as qty',
                'bbb.satuan',
                'bbb.harga_satuan',
                'bbb.subtotal',
                's.nama as satuan_nama'
            )
            ->get();
        
        echo "  BBB records: " . $bbbData->count() . "\n";
        
        if ($bbbData->count() > 0) {
            foreach ($bbbData as $bbb) {
                echo "    - " . $bbb->nama_bahan . ": " . $bbb->subtotal . "\n";
            }
        }
        
        $totalBiayaBahanBaku = $bbbData->sum('subtotal') ?? 0;
        echo "  Total BBB: " . $totalBiayaBahanBaku . "\n";
        
        // Check if this will be added to produkBiaya
        if ($bbbData->count() > 0 || $bomJobCosting) {
            $produkBiaya[] = [
                'produk' => $produk,
                'total_biaya_bahan_baku' => $totalBiayaBahanBaku,
                'detail_bahan_baku' => $bbbData->toArray(),
                'total_biaya_bahan_pendukung' => 0,
                'detail_bahan_pendukung' => [],
                'total_biaya_bahan' => $totalBiayaBahanBaku,
                'bom_job_costing' => $bomJobCosting
            ];
            echo "  ✅ Added to produkBiaya array\n";
        } else {
            echo "  ❌ NOT added to produkBiaya array (no data)\n";
        }
    }
    
    echo "\nFinal produkBiaya count: " . count($produkBiaya) . "\n";
    
    foreach ($produkBiaya as $index => $data) {
        echo "  " . ($index + 1) . ". " . $data['produk']->nama_produk . " - Total: " . $data['total_biaya_bahan'] . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error testing controller: " . $e->getMessage() . "\n";
}

echo "\n3. CHECK WHAT THE VIEW EXPECTS:\n\n";

try {
    $viewFile = 'c:\UMKM_COE\resources\views\master-data\biaya-bahan\index.blade.php';
    
    if (file_exists($viewFile)) {
        $viewContent = file_get_contents($viewFile);
        
        echo "Checking view expectations...\n";
        
        // Check what variables the view uses
        if (strpos($viewContent, '$produkBiaya') !== false) {
            echo "✅ View uses \$produkBiaya\n";
            
            // Find how it accesses the data
            if (preg_match('/\$produkBiaya\s*as\s*\$(\w+)/', $viewContent, $matches)) {
                echo "  Variable name: $" . $matches[1] . "\n";
            }
            
            // Check how it accesses total
            if (strpos($viewContent, 'total_biaya_bahan') !== false) {
                echo "✅ View accesses total_biaya_bahan\n";
            }
            
            if (strpos($viewContent, 'total_biaya_bahan_baku') !== false) {
                echo "✅ View accesses total_biaya_bahan_baku\n";
            }
            
        } else {
            echo "❌ View does NOT use \$produkBiaya\n";
            
            // Check what it does use
            if (strpos($viewContent, '$produks') !== false) {
                echo "  View uses \$produks instead\n";
            }
        }
        
    } else {
        echo "❌ View file not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking view: " . $e->getMessage() . "\n";
}

echo "\n4. IDENTIFY THE EXACT PROBLEM:\n\n";

try {
    echo "Based on investigation:\n";
    
    // Check if the issue is in the controller logic
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BiayaBahanController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    // Look for the specific condition that adds to produkBiaya
    if (preg_match('/if\s*\(\$bbbData->count\(\)\s*>\s*0.*?\}/s', $controllerContent, $matches)) {
        echo "Found BBB condition: " . $matches[0] . "\n";
    }
    
    // Check if there are any other conditions
    if (strpos($controllerContent, '$produkBiaya[] =') !== false) {
        echo "✅ Found produkBiaya assignment\n";
    } else {
        echo "❌ No produkBiaya assignment found\n";
    }
    
    // Check if the logic uses the right condition
    if (strpos($controllerContent, 'if ($bbbData->count() > 0 || $bomJobCosting)') !== false) {
        echo "✅ Logic condition looks correct\n";
    } else {
        echo "❌ Logic condition may be wrong\n";
    }
    
} catch (\Exception $e) {
    echo "Error identifying problem: " . $e->getMessage() . "\n";
}

echo "\n5. RECOMMENDED FIX:\n\n";

echo "The issue is likely that:\n";
echo "1. The controller logic is not using our simplified query\n";
echo "2. OR the view is not expecting the right variable structure\n";
echo "3. OR the condition for adding to produkBiaya is not met\n\n";

echo "Need to:\n";
echo "1. Ensure controller uses the simplified BBB query\n";
echo "2. Ensure the condition for adding to produkBiaya works\n";
echo "3. Verify view expects the right data structure\n\n";

echo "=== DEBUG COMPLETE ===\n";
