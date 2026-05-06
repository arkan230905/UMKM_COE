<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== INVESTIGATE BIAYA BAHAN PAGE ===\n\n";

echo "1. CHECK ROUTE FOR BIAYA BAHAN:\n\n";

try {
    $routes = app('router')->getRoutes();
    $biayaBahanRoute = null;
    
    foreach ($routes as $route) {
        if (strpos($route->uri(), 'biaya-bahan') !== false) {
            $biayaBahanRoute = $route;
            break;
        }
    }
    
    if ($biayaBahanRoute) {
        echo "✅ Found biaya-bahan route: " . $biayaBahanRoute->uri() . "\n";
        echo "   Controller: " . $biayaBahanRoute->getAction('uses') . "\n";
    } else {
        echo "❌ No biaya-bahan route found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking routes: " . $e->getMessage() . "\n";
}

echo "\n2. CHECK BOMCONTROLLER INDEX METHOD:\n\n";

try {
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BomController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    // Find index method
    if (preg_match('/public function index\(\).*?\n    \}/s', $controllerContent, $matches)) {
        $indexMethod = $matches[0];
        echo "✅ Found index method\n";
        
        // Check if it uses user_id filtering
        if (strpos($indexMethod, 'user_id') !== false) {
            echo "✅ Index method uses user_id filtering\n";
        } else {
            echo "❌ Index method does NOT use user_id filtering\n";
        }
        
        // Check if it queries bom_job_bbb
        if (strpos($indexMethod, 'bom_job_bbb') !== false) {
            echo "✅ Index method queries bom_job_bbb\n";
        } else {
            echo "❌ Index method does NOT query bom_job_bbb\n";
        }
        
    } else {
        echo "❌ Could not find index method\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking controller: " . $e->getMessage() . "\n";
}

echo "\n3. VERIFY DATA IN DATABASE:\n\n";

try {
    echo "Checking bom_job_bbb data for user_id = 1:\n";
    
    $bbbData = \Illuminate\Support\Facades\DB::table('bom_job_bbb')
        ->where('user_id', 1)
        ->get();
    
    echo "Found " . $bbbData->count() . " records for user_id = 1\n";
    
    foreach ($bbbData as $bbb) {
        echo "  - ID: " . $bbb->id . ", Produk ID: " . $bbb->produk_id . ", Bahan Baku ID: " . $bbb->bahan_baku_id . "\n";
        echo "    Jumlah: " . $bbb->jumlah . ", Subtotal: " . $bbb->subtotal . "\n";
    }
    
    // Check if user is logged in
    echo "\nCurrent user check:\n";
    if (auth()->check()) {
        echo "✅ User logged in: ID " . auth()->id() . "\n";
    } else {
        echo "❌ No user logged in\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking database: " . $e->getMessage() . "\n";
}

echo "\n4. TEST SIMULATED QUERY:\n\n";

try {
    echo "Simulating BomController index query...\n";
    
    // Simulate the query that should be in index method
    $query = \App\Models\BomJobCosting::with(['produk', 'detailBBB.bahanBaku.satuan'])
        ->where('user_id', 1);
    
    $results = $query->get();
    
    echo "Query results: " . $results->count() . " records\n";
    
    foreach ($results as $result) {
        echo "  - BomJobCosting ID: " . $result->id . "\n";
        echo "    Produk: " . ($result->produk ? $result->produk->nama_produk : 'No product') . "\n";
        echo "    Detail BBB: " . ($result->detailBBB ? $result->detailBBB->count() . " records" : "No details") . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error testing query: " . $e->getMessage() . "\n";
}

echo "\n5. CHECK VIEW FILE:\n\n";

try {
    $viewFile = 'c:\UMKM_COE\resources\views\master-data\bom\index.blade.php';
    
    if (file_exists($viewFile)) {
        echo "✅ Index view file exists\n";
        
        $viewContent = file_get_contents($viewFile);
        
        // Check if view expects the right variables
        if (strpos($viewContent, '$produks') !== false) {
            echo "✅ View expects \$produks variable\n";
        } else {
            echo "❌ View does NOT expect \$produks variable\n";
        }
        
        if (strpos($viewContent, 'foreach') !== false) {
            echo "✅ View has foreach loop\n";
        } else {
            echo "❌ View does NOT have foreach loop\n";
        }
        
    } else {
        echo "❌ Index view file not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking view: " . $e->getMessage() . "\n";
}

echo "\n6. IDENTIFY THE PROBLEM:\n\n";

try {
    echo "Based on investigation:\n";
    
    // Check if the issue is with the controller logic
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BomController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    // Look for the actual index method implementation
    if (preg_match('/public function index\(\)\s*\{(.*?)\}/s', $controllerContent, $matches)) {
        $indexBody = $matches[1];
        
        echo "Current index method implementation:\n";
        echo $indexBody . "\n\n";
        
        // Check what the method is actually doing
        if (strpos($indexBody, 'BomJobCosting') !== false) {
            echo "✅ Method queries BomJobCosting\n";
        } else {
            echo "❌ Method does NOT query BomJobCosting\n";
        }
        
        if (strpos($indexBody, 'produk') !== false) {
            echo "✅ Method queries produk\n";
        } else {
            echo "❌ Method does NOT query produk\n";
        }
        
        if (strpos($indexBody, 'compact') !== false) {
            echo "✅ Method uses compact to pass variables\n";
        } else {
            echo "❌ Method does NOT use compact\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error identifying problem: " . $e->getMessage() . "\n";
}

echo "\n7. RECOMMENDED SOLUTION:\n\n";

echo "The issue is likely in the BomController@index method.\n";
echo "It should:\n";
echo "1. Query BomJobCosting with user_id filtering\n";
echo "2. Include relationships to produk and detailBBB\n";
echo "3. Pass the correct variables to the view\n";
echo "4. The view should iterate over the results\n\n";

echo "=== INVESTIGATION COMPLETE ===\n";
