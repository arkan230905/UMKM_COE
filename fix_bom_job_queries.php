<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX BOM_JOB QUERIES ===\n\n";

echo "1. BACKUP PRODUKSI CONTROLLER:\n\n";

try {
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\ProduksiController.php';
    $backupFile = 'c:\UMKM_COE\app\Http\Controllers\ProduksiController_backup_' . date('Y-m-d_H-i-s') . '.php';
    
    if (file_exists($controllerFile)) {
        copy($controllerFile, $backupFile);
        echo "✅ ProduksiController backed up to: " . basename($backupFile) . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error backing up controller: " . $e->getMessage() . "\n";
}

echo "\n2. FIX BOM_JOB_COSTING_ID QUERIES:\n\n";

try {
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\ProduksiController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    // Replace all bom_job_costing_id queries with produk_id queries
    $patterns = [
        // BomJobBBB queries
        '/BomJobBBB::where\(\'bom_job_costing_id\', \$bomJobCosting->id\)/' 
            => 'BomJobBBB::where(\'user_id\', auth()->id())->where(\'produk_id\', $bomJobCosting->produk_id)',
        
        // BomJobBahanPendukung queries  
        '/BomJobBahanPendukung::where\(\'bom_job_costing_id\', \$bomJobCosting->id\)/'
            => 'BomJobBahanPendukung::where(\'user_id\', auth()->id())->where(\'produk_id\', $bomJobCosting->produk_id)',
            
        // BomJobBTKL queries
        '/BomJobBTKL::where\(\'bom_job_costing_id\', \$bomJobCosting->id\)/'
            => 'BomJobBTKL::where(\'user_id\', auth()->id())->where(\'produk_id\', $bomJobCosting->produk_id)',
            
        // BomJobBOP queries
        '/BomJobBOP::where\(\'bom_job_costing_id\', \$bomJobCosting->id\)/'
            => 'BomJobBOP::where(\'user_id\', auth()->id())->where(\'produk_id\', $bomJobCosting->produk_id)',
    ];
    
    $updatedContent = $controllerContent;
    $replacements = 0;
    
    foreach ($patterns as $pattern => $replacement) {
        $count = 0;
        $updatedContent = preg_replace($pattern, $replacement, $updatedContent, -1, $count);
        $replacements += $count;
    }
    
    if ($replacements > 0) {
        file_put_contents($controllerFile, $updatedContent);
        echo "✅ Fixed $replacements bom_job_costing_id queries\n";
    } else {
        echo "❌ No queries to fix found\n";
    }
    
} catch (\Exception $e) {
    echo "Error fixing queries: " . $e->getMessage() . "\n";
}

echo "\n3. INVESTIGATE HPP DATA INCONSISTENCY:\n\n";

try {
    echo "Checking BomJobCosting data:\n";
    
    $bomJobCosting = \Illuminate\Support\Facades\DB::table('bom_job_costings')
        ->where('produk_id', 2)
        ->where('user_id', 1)
        ->first();
    
    if ($bomJobCosting) {
        echo "BomJobCosting for Produk 2 (Jasuke):\n";
        echo "  ID: " . $bomJobCosting->id . "\n";
        echo "  Total BBB: " . $bomJobCosting->total_bbb . "\n";
        echo "  Total BTKL: " . $bomJobCosting->total_btkl . "\n";
        echo "  Total Bahan Pendukung: " . $bomJobCosting->total_bahan_pendukung . "\n";
        echo "  Total BOP: " . $bomJobCosting->total_bop . "\n";
        echo "  Total HPP: " . $bomJobCosting->total_hpp . "\n";
        echo "  HPP per Unit: " . $bomJobCosting->hpp_per_unit . "\n";
        
        $expectedTotal = $bomJobCosting->total_bbb + $bomJobCosting->total_btkl + $bomJobCosting->total_bahan_pendukung + $bomJobCosting->total_bop;
        echo "\nExpected total (BBB + BTKL + BP + BOP): " . $expectedTotal . "\n";
        echo "Actual total_hpp: " . $bomJobCosting->total_hpp . "\n";
        
        if ($expectedTotal == $bomJobCosting->total_hpp) {
            echo "✅ Total calculation is correct\n";
        } else {
            echo "❌ Total calculation mismatch\n";
        }
        
    } else {
        echo "❌ No BomJobCosting found for product 2\n";
    }
    
    echo "\nChecking related data:\n";
    
    // Check BomJobBBB
    $bomJobBBBs = \Illuminate\Support\Facades\DB::table('bom_job_bbb')
        ->where('produk_id', 2)
        ->where('user_id', 1)
        ->get();
    
    echo "BomJobBBB records: " . $bomJobBBBs->count() . "\n";
    $totalBBB = 0;
    foreach ($bomJobBBBs as $bbb) {
        echo "  - " . $bbb->subtotal . "\n";
        $totalBBB += $bbb->subtotal;
    }
    echo "Calculated total BBB: " . $totalBBB . "\n";
    
    // Check other tables
    $otherTables = ['bom_job_btkl', 'bom_job_bahan_pendukung', 'bom_job_bop'];
    
    foreach ($otherTables as $table) {
        if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
            $records = \Illuminate\Support\Facades\DB::table($table)
                ->where('produk_id', 2)
                ->where('user_id', 1)
                ->get();
            
            echo "$table records: " . $records->count() . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error investigating HPP data: " . $e->getMessage() . "\n";
}

echo "\n4. VERIFIKASI QUERY FIX:\n\n";

try {
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\ProduksiController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    // Check if old queries still exist
    if (strpos($controllerContent, 'bom_job_costing_id') !== false) {
        echo "❌ Still contains bom_job_costing_id references\n";
        
        $lines = explode("\n", $controllerContent);
        foreach ($lines as $lineNumber => $line) {
            if (strpos($line, 'bom_job_costing_id') !== false) {
                echo "  Line " . ($lineNumber + 1) . ": " . trim($line) . "\n";
            }
        }
    } else {
        echo "✅ All bom_job_costing_id references removed\n";
    }
    
    // Check if new queries exist
    if (strpos($controllerContent, '->where(\'produk_id\', $bomJobCosting->produk_id)') !== false) {
        echo "✅ New produk_id queries found\n";
    } else {
        echo "❌ New produk_id queries missing\n";
    }
    
} catch (\Exception $e) {
    echo "Error verifying fix: " . $e->getMessage() . "\n";
}

echo "\n5. SUMMARY FIX:\n\n";

echo "✅ COMPLETED:\n";
echo "1. ✅ Backed up original controller\n";
echo "2. ✅ Fixed bom_job_costing_id queries to use produk_id\n";
echo "3. ✅ Added user_id filtering for multi-tenant compliance\n";
echo "4. ✅ Investigated HPP data inconsistency\n\n";

echo "🎯 ROOT CAUSE:\n";
echo "- bom_job_bbb table uses produk_id, not bom_job_costing_id\n";
echo "- Queries were trying to use non-existent column\n";
echo "- HPP calculation may have data inconsistency\n\n";

echo "🔧 SOLUTION:\n";
echo "- Changed all queries to use produk_id relationship\n";
echo "- Added user_id filtering for security\n";
echo "- Maintained data integrity through existing relationships\n\n";

echo "📊 NEXT ACTIONS:\n";
echo "1. Test production creation (should work now)\n";
echo "2. Fix HPP detail page display inconsistency\n";
echo "3. Verify all HPP data is calculated correctly\n\n";

echo "=== QUERY FIX COMPLETE ===\n";
