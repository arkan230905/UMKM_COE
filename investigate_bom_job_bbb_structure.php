<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== INVESTIGASI BOM_JOB_BBB STRUCTURE ===\n\n";

echo "1. CEK STRUKTUR TABEL BOM_JOB_BBB:\n\n";

try {
    if (\Illuminate\Support\Facades\Schema::hasTable('bom_job_bbb')) {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_bbb');
        echo "bom_job_bbb table columns:\n";
        echo implode(', ', $columns) . "\n\n";
        
        // Check for user_id
        if (in_array('user_id', $columns)) {
            echo "✅ user_id column exists\n";
        } else {
            echo "❌ user_id column missing\n";
        }
        
        // Check for bom_job_costing_id
        if (in_array('bom_job_costing_id', $columns)) {
            echo "✅ bom_job_costing_id column exists\n";
        } else {
            echo "❌ bom_job_costing_id column missing (THIS IS THE PROBLEM)\n";
        }
        
        // Check for other possible foreign keys
        $possibleKeys = ['produk_id', 'bom_id'];
        foreach ($possibleKeys as $key) {
            if (in_array($key, $columns)) {
                echo "✅ $key column exists\n";
            } else {
                echo "❌ $key column missing\n";
            }
        }
        
    } else {
        echo "❌ bom_job_bbb table doesn't exist\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking table structure: " . $e->getMessage() . "\n";
}

echo "\n2. CEK DATA BOM_JOB_BBB SAAT INI:\n\n";

try {
    $records = \Illuminate\Support\Facades\DB::table('bom_job_bbb')
        ->select('*')
        ->limit(3)
        ->get();
    
    echo "bom_job_bbb records: " . $records->count() . "\n";
    
    foreach ($records as $record) {
        echo "Record: ";
        foreach ((array)$record as $key => $value) {
            echo "$key: $value, ";
        }
        echo "\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking data: " . $e->getMessage() . "\n";
}

echo "\n3. CEK STRUKTUR TABEL BOM_JOB_COSTINGS:\n\n";

try {
    if (\Illuminate\Support\Facades\Schema::hasTable('bom_job_costings')) {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_costings');
        echo "bom_job_costings table columns:\n";
        echo implode(', ', $columns) . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking bom_job_costings: " . $e->getMessage() . "\n";
}

echo "\n4. CEK RELATIONSHIP YANG DIHARAPKAN:\n\n";

try {
    echo "Checking how bom_job_bbb should relate to bom_job_costings:\n\n";
    
    // Check if there's a relationship through produk_id
    $bomJobBBB = \Illuminate\Support\Facades\DB::table('bom_job_bbb')->first();
    
    if ($bomJobBBB) {
        echo "Sample bom_job_bbb record:\n";
        foreach ((array)$bomJobBBB as $key => $value) {
            echo "  $key: $value\n";
        }
        
        // Check if we can find relationship through produk
        if (isset($bomJobBBB->produk_id)) {
            echo "\nChecking relationship through produk_id:\n";
            
            $bomJobCosting = \Illuminate\Support\Facades\DB::table('bom_job_costings')
                ->where('produk_id', $bomJobBBB->produk_id)
                ->first();
            
            if ($bomJobCosting) {
                echo "✅ Found bom_job_costing for produk_id " . $bomJobBBB->produk_id . "\n";
                echo "  BomJobCosting ID: " . $bomJobCosting->id . "\n";
                echo "  User ID: " . $bomJobCosting->user_id . "\n";
            } else {
                echo "❌ No bom_job_costing found for produk_id " . $bomJobBBB->produk_id . "\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking relationship: " . $e->getMessage() . "\n";
}

echo "\n5. CEK QUERY YANG MENYEBABKAN ERROR:\n\n";

try {
    echo "Checking the problematic query pattern...\n";
    
    // Look for where bom_job_costing_id is used
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\ProduksiController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    if (strpos($controllerContent, 'bom_job_costing_id') !== false) {
        echo "✅ Found bom_job_costing_id usage in ProduksiController\n";
        
        // Find the specific lines
        $lines = explode("\n", $controllerContent);
        foreach ($lines as $lineNumber => $line) {
            if (strpos($line, 'bom_job_costing_id') !== false) {
                echo "  Line " . ($lineNumber + 1) . ": " . trim($line) . "\n";
            }
        }
    } else {
        echo "❌ No bom_job_costing_id usage found in ProduksiController\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking controller: " . $e->getMessage() . "\n";
}

echo "\n6. ANALISIS MASALAH:\n\n";

echo "Based on investigation:\n";
echo "1. bom_job_bbb table missing bom_job_costing_id column\n";
echo "2. Query trying to use bom_job_costing_id = 5\n";
echo "3. Need to either:\n";
echo "   a) Add bom_job_costing_id column to bom_job_bbb, OR\n";
echo "   b) Change query to use existing relationship (produk_id)\n\n";

echo "Recommended solution:\n";
echo "- Use produk_id relationship instead of bom_job_costing_id\n";
echo "- bom_job_bbb should link to bom_job_costings through produk_id\n";
echo "- This maintains data integrity and avoids redundant foreign keys\n\n";

echo "=== INVESTIGATION COMPLETE ===\n";
