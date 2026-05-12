<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKING ACTUAL DATABASE TABLES ===\n\n";

// Get all tables in database
$tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');

echo "Tables containing 'bom':\n";
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    if (strpos(strtolower($tableName), 'bom') !== false) {
        echo "  - $tableName\n";
        
        // Check if user_id column exists
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($tableName);
        $hasUserId = in_array('user_id', $columns);
        echo "    user_id column: " . ($hasUserId ? "✅ EXISTS" : "❌ MISSING") . "\n";
        
        // Count records
        $count = \Illuminate\Support\Facades\DB::table($tableName)->count();
        echo "    Total records: $count\n";
        
        if ($hasUserId) {
            $userCount = \Illuminate\Support\Facades\DB::table($tableName)->where('user_id', 1)->count();
            echo "    User 1 records: $userCount\n";
        }
        
        echo "\n";
    }
}

echo "Tables related to harga pokok produksi:\n";
$relatedTables = [
    'produk',
    'bahan_bakus',
    'proses_produksis',
    'bop_proses',
    'biaya_bahans'
];

foreach ($relatedTables as $tableName) {
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable($tableName)) {
            echo "  - $tableName\n";
            
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($tableName);
            $hasUserId = in_array('user_id', $columns);
            echo "    user_id column: " . ($hasUserId ? "✅ EXISTS" : "❌ MISSING") . "\n";
            
            $count = \Illuminate\Support\Facades\DB::table($tableName)->count();
            echo "    Total records: $count\n";
            
            if ($hasUserId) {
                $userCount = \Illuminate\Support\Facades\DB::table($tableName)->where('user_id', 1)->count();
                echo "    User 1 records: $userCount\n";
            }
            
            echo "\n";
        }
    } catch (\Exception $e) {
        echo "  - $tableName: ❌ Error checking\n";
    }
}

echo "=== CHECKING BOM CONTROLLER REFERENCES ===\n";

// Check what tables the BomController actually uses
$controllerFile = file_get_contents('c:\UMKM_COE\app\Http\Controllers\BomController.php');

$patterns = [
    'BomJobCosting' => 'bom_job_costings',
    'BomJobBBB' => 'bom_job_b_b_b',
    'BomJobBTKL' => 'bom_job_b_t_k_l',
    'BomJobBOP' => 'bom_job_b_o_p',
    'BomJobBahanPendukung' => 'bom_job_bahan_pendukung'
];

foreach ($patterns as $model => $expectedTable) {
    if (strpos($controllerFile, $model) !== false) {
        echo "Model $model found in controller\n";
        
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable($expectedTable)) {
                echo "  Table $expectedTable: ✅ EXISTS\n";
                
                $columns = \Illuminate\Support\Facades\Schema::getColumnListing($expectedTable);
                $hasUserId = in_array('user_id', $columns);
                echo "  user_id column: " . ($hasUserId ? "✅ EXISTS" : "❌ MISSING") . "\n";
                
                $count = \Illuminate\Support\Facades\DB::table($expectedTable)->count();
                echo "  Total records: $count\n";
                
                if ($hasUserId) {
                    $userCount = \Illuminate\Support\Facades\DB::table($expectedTable)->where('user_id', 1)->count();
                    echo "  User 1 records: $userCount\n";
                }
            } else {
                echo "  Table $expectedTable: ❌ NOT FOUND\n";
            }
        } catch (\Exception $e) {
            echo "  Error checking $expectedTable: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
}

echo "=== ACTUAL TABLES ANALYSIS COMPLETE ===\n";
