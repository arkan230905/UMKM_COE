<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== INVESTIGASI BOM_JOB_COSTINGS USER_ID ===\n\n";

echo "1. CEK DATA BOM_JOB_COSTINGS SAAT INI:\n\n";

try {
    $records = \Illuminate\Support\Facades\DB::table('bom_job_costings')
        ->select('id', 'produk_id', 'user_id', 'total_hpp', 'created_at')
        ->get();
    
    echo "Total records: " . $records->count() . "\n\n";
    
    $nullUserIdCount = 0;
    $validUserIdCount = 0;
    
    foreach ($records as $record) {
        echo "ID: " . $record->id . ", Produk ID: " . $record->produk_id . ", User ID: " . ($record->user_id ?? 'NULL') . ", Total HPP: " . $record->total_hpp . ", Created: " . $record->created_at . "\n";
        
        if ($record->user_id === null) {
            $nullUserIdCount++;
        } else {
            $validUserIdCount++;
        }
    }
    
    echo "\nSummary:\n";
    echo "- Records with NULL user_id: " . $nullUserIdCount . "\n";
    echo "- Records with valid user_id: " . $validUserIdCount . "\n";
    
} catch (\Exception $e) {
    echo "Error checking bom_job_costings: " . $e->getMessage() . "\n";
}

echo "\n2. CEK BOM_CONTROLLER@STORE METHOD:\n\n";

try {
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BomController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    // Find the store method
    if (preg_match('/public function store\(.*?\n    }/s', $controllerContent, $matches)) {
        $storeMethod = $matches[0];
        
        echo "Checking BomController@store method:\n";
        
        // Check for user_id usage
        if (strpos($storeMethod, 'user_id') !== false) {
            echo "✅ Contains user_id references\n";
        } else {
            echo "❌ No user_id references found\n";
        }
        
        // Check for auth()->id()
        if (strpos($storeMethod, 'auth()->id()') !== false) {
            echo "✅ Uses auth()->id()\n";
        } else {
            echo "❌ No auth()->id() found\n";
        }
        
        // Check for BomJobCosting creation
        if (strpos($storeMethod, 'BomJobCosting::') !== false) {
            echo "✅ Contains BomJobCosting creation\n";
            
            // Look for the specific creation pattern
            if (preg_match('/BomJobCosting::(create|updateOrCreate)\((.*?)\)/s', $storeMethod, $creationMatch)) {
                echo "Creation pattern found:\n";
                echo $creationMatch[0] . "\n";
                
                if (strpos($creationMatch[0], 'user_id') !== false) {
                    echo "✅ user_id included in creation\n";
                } else {
                    echo "❌ user_id missing from creation\n";
                }
            }
        } else {
            echo "❌ No BomJobCosting creation found\n";
        }
        
    } else {
        echo "❌ Store method not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking controller: " . $e->getMessage() . "\n";
}

echo "\n3. CEK BOM_JOB_COSTING MODEL FILLABLE:\n\n";

try {
    $modelFile = 'c:\UMKM_COE\app\Models\BomJobCosting.php';
    
    if (file_exists($modelFile)) {
        $modelContent = file_get_contents($modelFile);
        
        // Find fillable array
        if (preg_match('/protected \$fillable = \[(.*?)\];/s', $modelContent, $matches)) {
            $fillable = $matches[1];
            
            echo "BomJobCosting fillable fields:\n";
            echo $fillable . "\n\n";
            
            if (strpos($fillable, 'user_id') !== false) {
                echo "✅ user_id is in fillable\n";
            } else {
                echo "❌ user_id is NOT in fillable\n";
            }
        } else {
            echo "❌ Fillable array not found\n";
        }
        
    } else {
        echo "❌ BomJobCosting model not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking model: " . $e->getMessage() . "\n";
}

echo "\n4. CEK DATA TERKAIT LAINYA:\n\n";

try {
    echo "Checking related data integrity:\n";
    
    // Check bom_job_bbb
    $bbbRecords = \Illuminate\Support\Facades\DB::table('bom_job_bbb')
        ->select('user_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
        ->groupBy('user_id')
        ->get();
    
    echo "bom_job_bbb user distribution:\n";
    foreach ($bbbRecords as $record) {
        echo "  User " . ($record->user_id ?? 'NULL') . ": " . $record->count . " records\n";
    }
    
    // Check products
    $productRecords = \Illuminate\Support\Facades\DB::table('produks')
        ->select('user_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
        ->groupBy('user_id')
        ->get();
    
    echo "\nproduks user distribution:\n";
    foreach ($productRecords as $record) {
        echo "  User " . ($record->user_id ?? 'NULL') . ": " . $record->count . " records\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking related data: " . $e->getMessage() . "\n";
}

echo "\n5. IDENTIFIKASI MASALAH:\n\n";

echo "Based on investigation:\n";
echo "1. bom_job_costings has NULL user_id records\n";
echo "2. Need to check if BomController@store includes user_id\n";
echo "3. Need to check if BomJobCosting model allows user_id\n";
echo "4. Need to verify creation logic\n\n";

echo "Potential causes:\n";
echo "- user_id not included in BomJobCosting creation\n";
echo "- user_id not in fillable array\n";
echo "- auth()->id() not called in controller\n";
echo "- Model observer overriding user_id\n\n";

echo "=== INVESTIGATION COMPLETE ===\n";
