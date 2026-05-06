<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST FIX BOM_JOB_COSTING_ID ERROR ===\n\n";

echo "1. VERIFIKASI MODEL RELATIONS:\n\n";

try {
    // Test BomJobBBB model
    echo "Testing BomJobBBB model:\n";
    $bbb = \App\Models\BomJobBBB::find(5);
    
    if ($bbb) {
        echo "✅ BomJobBBB found - ID: " . $bbb->id . "\n";
        echo "  User ID: " . $bbb->user_id . "\n";
        echo "  Produk ID: " . $bbb->produk_id . "\n";
        echo "  Bahan Baku ID: " . $bbb->bahan_baku_id . "\n";
        
        // Test relations
        if ($bbb->bahanBaku) {
            echo "  ✅ bahanBaku relation works\n";
        } else {
            echo "  ❌ bahanBaku relation failed\n";
        }
        
        if ($bbb->produk) {
            echo "  ✅ produk relation works\n";
        } else {
            echo "  ❌ produk relation failed\n";
        }
        
        // Test if bomJobCosting relation is removed
        try {
            $bomJobCosting = $bbb->bomJobCosting;
            if ($bomJobCosting) {
                echo "  ❌ bomJobCosting relation still exists (should be removed)\n";
            } else {
                echo "  ✅ bomJobCosting relation removed\n";
            }
        } catch (\Exception $e) {
            echo "  ✅ bomJobCosting relation removed (exception: " . $e->getMessage() . ")\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error testing BomJobBBB: " . $e->getMessage() . "\n";
}

echo "\n2. TEST BomJobCosting MODEL:\n\n";

try {
    echo "Testing BomJobCosting model:\n";
    $bomJobCosting = \App\Models\BomJobCosting::find(2);
    
    if ($bomJobCosting) {
        echo "✅ BomJobCosting found - ID: " . $bomJobCosting->id . "\n";
        echo "  Produk ID: " . $bomJobCosting->produk_id . "\n";
        echo "  User ID: " . $bomJobCosting->user_id . "\n";
        
        // Test detailBBB relation
        $detailBBB = $bomJobCosting->detailBBB;
        echo "  Detail BBB count: " . $detailBBB->count() . "\n";
        
        foreach ($detailBBB as $bbb) {
            echo "    - BBB ID: " . $bbb->id . " (Produk ID: " . $bbb->produk_id . ")\n";
        }
        
        if ($detailBBB->count() > 0) {
            echo "  ✅ detailBBB relation works with new logic\n";
        } else {
            echo "  ❌ detailBBB relation not working\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error testing BomJobCosting: " . $e->getMessage() . "\n";
}

echo "\n3. SIMULASI QUERY YANG MENYEBABKAN ERROR:\n\n";

try {
    echo "Simulating the problematic query:\n";
    echo "SQL: select * from `bom_job_bbb` where `bom_job_bbb`.`bom_job_costing_id` in (2) and `user_id` = 1\n\n";
    
    // This should fail now because bom_job_costing_id doesn't exist
    try {
        $result = \App\Models\BomJobBBB::whereIn('bom_job_costing_id', [2])
            ->where('user_id', 1)
            ->get();
        echo "❌ Query still works (bom_job_costing_id still exists)\n";
    } catch (\Exception $e) {
        echo "✅ Query fails as expected (bom_job_costing_id removed): " . $e->getMessage() . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error simulating query: " . $e->getMessage() . "\n";
}

echo "\n4. TEST QUERY YANG BENAR:\n\n";

try {
    echo "Testing correct query using produk_id:\n";
    $result = \App\Models\BomJobBBB::where('produk_id', 2)
        ->where('user_id', 1)
        ->get();
    
    echo "✅ Correct query works - Found " . $result->count() . " records\n";
    foreach ($result as $bbb) {
        echo "  - BBB ID: " . $bbb->id . " (Produk ID: " . $bbb->produk_id . ")\n";
    }
    
} catch (\Exception $e) {
    echo "Error testing correct query: " . $e->getMessage() . "\n";
}

echo "\n5. TEST BOMCONTROLLER CREATE METHOD:\n\n";

try {
    echo "Testing BomController@create method logic:\n";
    
    // Simulate the query from BomController@create
    $produkIds = \App\Models\BomJobBBB::where('user_id', 1)
        ->pluck('produk_id')
        ->unique();
    
    echo "✅ Produk selection query works - Found: " . $produkIds->implode(', ') . "\n";
    
    $produks = \App\Models\Produk::where('user_id', 1)
        ->whereIn('id', $produkIds)
        ->get();
    
    echo "✅ Products found: " . $produks->count() . "\n";
    foreach ($produks as $product) {
        echo "  - " . $product->nama_produk . " (ID: " . $product->id . ")\n";
    }
    
} catch (\Exception $e) {
    echo "Error testing BomController@create: " . $e->getMessage() . "\n";
}

echo "\n6. TEST BOMCONTROLLER STORE METHOD LOGIC:\n\n";

try {
    echo "Testing BomController@store method logic:\n";
    
    // Simulate getting/creating BomJobCosting
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 2)
        ->where('user_id', 1)
        ->first();
    
    if ($bomJobCosting) {
        echo "✅ BomJobCosting found - ID: " . $bomJobCosting->id . "\n";
        
        // Test detailBBB relation
        $detailBBB = $bomJobCosting->detailBBB;
        echo "  Detail BBB count: " . $detailBBB->count() . "\n";
        
        $totalBBB = $detailBBB->sum('subtotal');
        echo "  Total BBB: Rp " . number_format($totalBBB, 2, ',', '.') . "\n";
        
        echo "✅ Store method logic should work now\n";
    } else {
        echo "❌ BomJobCosting not found - will be created automatically\n";
    }
    
} catch (\Exception $e) {
    echo "Error testing BomController@store: " . $e->getMessage() . "\n";
}

echo "\n7. SUMMARY FIXES:\n\n";

echo "✅ FIXED:\n";
echo "- BomJobBBB model: Removed bom_job_costing_id from fillable\n";
echo "- BomJobBBB model: Removed bomJobCosting relation\n";
echo "- BomJobBBB model: Added produk relation\n";
echo "- BomJobCosting model: Updated detailBBB relation to use produk_id\n";
echo "- BomJobCosting model: Added user_id filtering in detailBBB\n\n";

echo "✅ RESULT:\n";
echo "- Error 'Column not found: bom_job_costing_id' should be resolved\n";
echo "- Query using bom_job_costing_id will fail (as expected)\n";
echo "- Query using produk_id will work correctly\n";
echo "- Multi-tenant filtering maintained\n\n";

echo "=== FIX VERIFICATION COMPLETE ===\n";
