<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG BOM SHOW METHOD ===\n\n";

echo "1. CEK CURRENT BOM CONTROLLER SHOW METHOD:\n\n";

try {
    $bomControllerFile = 'c:\UMKM_COE\app\Http\Controllers\BomController.php';
    $bomControllerContent = file_get_contents($bomControllerFile);
    
    // Check if fallback logic was added
    if (strpos($bomControllerContent, 'FALLBACK: If no BTKL data found') !== false) {
        echo "✅ Fallback logic found in BomController\n";
    } else {
        echo "❌ Fallback logic not found\n";
    }
    
    // Check if the logic is in the right place
    if (preg_match('/public function show\(.*?\n    }/s', $bomControllerContent, $matches)) {
        $showMethod = $matches[0];
        
        if (strpos($showMethod, 'FALLBACK') !== false) {
            echo "✅ Fallback logic is inside show method\n";
        } else {
            echo "❌ Fallback logic not inside show method\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking BomController: " . $e->getMessage() . "\n";
}

echo "\n2. SIMULASI BOMCONTROLLER@SHOW LOGIC:\n\n";

try {
    echo "Simulating BomController@show for product ID 2...\n";
    
    // Get product
    $produk = \App\Models\Produk::find(2);
    echo "Product: " . $produk->nama_produk . "\n";
    
    // Get BomJobCosting
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 2)
        ->with([
            'detailBBB.bahanBaku.satuan',
            'detailBTKL.btkl.jabatan',
            'detailBahanPendukung.bahanPendukung.satuan',
            'detailBOP'
        ])
        ->first();
    
    if ($bomJobCosting) {
        echo "BomJobCosting found:\n";
        echo "  Total BBB: " . $bomJobCosting->total_bbb . "\n";
        echo "  Total BTKL: " . $bomJobCosting->total_btkl . "\n";
        echo "  Total Bahan Pendukung: " . $bomJobCosting->total_bahan_pendukung . "\n";
        echo "  Total BOP: " . $bomJobCosting->total_bop . "\n";
        echo "  Total HPP: " . $bomJobCosting->total_hpp . "\n";
        
        // Check detail relationships
        echo "\nChecking detail relationships:\n";
        
        // Check detailBBB
        if ($bomJobCosting->detailBBB) {
            echo "  detailBBB: " . $bomJobCosting->detailBBB->count() . " records\n";
            foreach ($bomJobCosting->detailBBB as $detail) {
                echo "    - " . ($detail->bahanBaku->nama_bahan ?? 'Unknown') . ": " . $detail->subtotal . "\n";
            }
        } else {
            echo "  detailBBB: No records\n";
        }
        
        // Check detailBTKL
        if ($bomJobCosting->detailBTKL) {
            echo "  detailBTKL: " . $bomJobCosting->detailBTKL->count() . " records\n";
        } else {
            echo "  detailBTKL: No records\n";
        }
        
        // Check detailBahanPendukung
        if ($bomJobCosting->detailBahanPendukung) {
            echo "  detailBahanPendukung: " . $bomJobCosting->detailBahanPendukung->count() . " records\n";
        } else {
            echo "  detailBahanPendukung: No records\n";
        }
        
        // Check detailBOP
        if ($bomJobCosting->detailBOP) {
            echo "  detailBOP: " . $bomJobCosting->detailBOP->count() . " records\n";
        } else {
            echo "  detailBOP: No records\n";
        }
        
    } else {
        echo "❌ No BomJobCosting found\n";
    }
    
} catch (\Exception $e) {
    echo "Error simulating logic: " . $e->getMessage() . "\n";
}

echo "\n3. CEK MODEL RELATIONSHIPS:\n\n";

try {
    echo "Checking BomJobCosting model relationships...\n";
    
    $bomJobCosting = new \App\Models\BomJobCosting();
    
    // Get all methods
    $reflection = new ReflectionClass($bomJobCosting);
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    $relationshipMethods = [];
    foreach ($methods as $method) {
        if (strpos($method->getName(), 'detail') === 0 || 
            strpos($method->getName(), 'bom') === 0) {
            $relationshipMethods[] = $method->getName();
        }
    }
    
    echo "Relationship methods found: " . implode(', ', $relationshipMethods) . "\n";
    
    // Test specific relationships
    $testBomJobCosting = \App\Models\BomJobCosting::where('produk_id', 2)->first();
    
    if ($testBomJobCosting) {
        foreach ($relationshipMethods as $method) {
            try {
                $relation = $testBomJobCosting->$method();
                if ($relation) {
                    echo "  $method: " . get_class($relation) . " - " . $relation->count() . " records\n";
                } else {
                    echo "  $method: No relation\n";
                }
            } catch (\Exception $e) {
                echo "  $method: Error - " . $e->getMessage() . "\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking relationships: " . $e->getMessage() . "\n";
}

echo "\n4. IDENTIFY THE PROBLEM:\n\n";

try {
    echo "Based on investigation:\n";
    
    // Check if relationships exist but return empty
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 2)->first();
    
    if ($bomJobCosting) {
        $problem = [];
        
        // Check BBB
        $bbbCount = \Illuminate\Support\Facades\DB::table('bom_job_bbb')
            ->where('user_id', 1)
            ->where('produk_id', 2)
            ->count();
        
        if ($bbbCount > 0 && (!$bomJobCosting->detailBBB || $bomJobCosting->detailBBB->count() == 0)) {
            $problem[] = "detailBBB relationship not working";
        }
        
        // Check BTKL
        $btklCount = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
            ->where('user_id', 1)
            ->where('bom_job_costing_id', $bomJobCosting->id)
            ->count();
        
        if ($bomJobCosting->total_btkl > 0 && (!$bomJobCosting->detailBTKL || $bomJobCosting->detailBTKL->count() == 0)) {
            $problem[] = "detailBTKL relationship not working";
        }
        
        // Check BOP
        $bopCount = \Illuminate\Support\Facades\DB::table('bom_job_bop')
            ->where('user_id', 1)
            ->where('bom_job_costing_id', $bomJobCosting->id)
            ->count();
        
        if ($bomJobCosting->total_bop > 0 && (!$bomJobCosting->detailBOP || $bomJobCosting->detailBOP->count() == 0)) {
            $problem[] = "detailBOP relationship not working";
        }
        
        if (!empty($problem)) {
            echo "❌ Problems found:\n";
            foreach ($problem as $p) {
                echo "  - $p\n";
            }
        } else {
            echo "✅ No relationship problems detected\n";
        }
        
        echo "\nRoot cause analysis:\n";
        echo "- Data exists in BomJobCosting totals\n";
        echo "- Detail tables are empty (except BBB)\n";
        echo "- Relationships return empty because tables are empty\n";
        echo "- Fallback logic should trigger but may not be working\n";
        
    }
    
} catch (\Exception $e) {
    echo "Error identifying problem: " . $e->getMessage() . "\n";
}

echo "\n5. RECOMMENDED SOLUTION:\n\n";

echo "The issue is that the fallback logic may not be properly implemented or positioned.\n";
echo "Need to:\n";
echo "1. Ensure fallback logic is executed after checking relationships\n";
echo "2. Use BomJobCosting totals directly when detail tables are empty\n";
echo "3. Fix the view to display fallback data correctly\n\n";

echo "=== DEBUG COMPLETE ===\n";
