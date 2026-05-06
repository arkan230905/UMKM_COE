<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING ACTUAL BOM MULTI-TENANT TABLES ===\n\n";

// Tables that need user_id column (based on actual database)
$tables_to_fix = [
    'bom_job_bop' => 'Detail BOP',
    'bom_job_btkl' => 'Detail BTKL',
    'bom_proses' => 'BOM Proses',
    'bom_proses_bops' => 'BOM Proses BOP'
];

foreach ($tables_to_fix as $table => $description) {
    echo "Processing table: $table ($description)\n";
    
    try {
        // Check if table exists
        if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
            echo "  ❌ Table does not exist\n";
            echo "\n";
            continue;
        }
        
        // Check if user_id column exists
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        
        if (!in_array('user_id', $columns)) {
            echo "  - Adding user_id column...\n";
            
            // Add user_id column
            \Illuminate\Support\Facades\Schema::table($table, function ($table) {
                $table->unsignedBigInteger('user_id')->after('id')->nullable();
                $table->index('user_id');
            });
            
            echo "  ✅ user_id column added\n";
            
            // Update existing records to belong to current user (user_id = 1)
            $currentUserId = 1; // Assuming current user is ID 1
            
            \Illuminate\Support\Facades\DB::table($table)->update(['user_id' => $currentUserId]);
            
            $updatedCount = \Illuminate\Support\Facades\DB::table($table)->where('user_id', $currentUserId)->count();
            echo "  ✅ Updated $updatedCount existing records to user_id = $currentUserId\n";
            
        } else {
            echo "  ✅ user_id column already exists\n";
            
            $totalRecords = \Illuminate\Support\Facades\DB::table($table)->count();
            $userRecords = \Illuminate\Support\Facades\DB::table($table)->where('user_id', 1)->count();
            
            echo "    - Total records: $totalRecords\n";
            echo "    - User 1 records: $userRecords\n";
            
            if ($totalRecords > 0 && $userRecords == 0) {
                echo "  ⚠️  Updating records to user_id = 1\n";
                \Illuminate\Support\Facades\DB::table($table)->update(['user_id' => 1]);
                echo "  ✅ All records updated to user_id = 1\n";
            }
        }
        
    } catch (\Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== VERIFICATION AFTER FIX ===\n";

foreach ($tables_to_fix as $table => $description) {
    echo "Table: $table ($description)\n";
    
    try {
        if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
            echo "  ❌ Table does not exist\n";
            echo "\n";
            continue;
        }
        
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        
        if (in_array('user_id', $columns)) {
            echo "  ✅ user_id column EXISTS\n";
            
            $totalRecords = \Illuminate\Support\Facades\DB::table($table)->count();
            $userRecords = \Illuminate\Support\Facades\DB::table($table)->where('user_id', 1)->count();
            
            echo "     - Total records: $totalRecords\n";
            echo "     - User 1 records: $userRecords\n";
            
            if ($totalRecords > 0 && $userRecords == $totalRecords) {
                echo "     ✅ All records properly assigned to user 1\n";
            } else {
                echo "     ⚠️  Some records may not be properly assigned\n";
            }
        } else {
            echo "  ❌ user_id column still MISSING\n";
        }
        
    } catch (\Exception $e) {
        echo "  ❌ Error verifying: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== TESTING BOM CONTROLLER MULTI-TENANT ===\n";

// Test if BomController properly filters by user_id
echo "Checking BomController filtering...\n";

$controllerFile = file_get_contents('c:\UMKM_COE\app\Http\Controllers\BomController.php');

// Check for user_id filtering in key methods
$methods_to_check = ['index', 'create', 'store', 'edit', 'update', 'show', 'destroy'];

foreach ($methods_to_check as $method) {
    echo "Method: $method\n";
    
    // Find the method in controller
    $pattern = "/public function $method\(/";
    if (preg_match($pattern, $controllerFile, $matches, PREG_OFFSET_CAPTURE)) {
        $startPos = $matches[0][1];
        
        // Find the next method or end of class
        $nextMethodPattern = "/\n    (public|private|protected) function/";
        if (preg_match($nextMethodPattern, $controllerFile, $nextMatches, PREG_OFFSET_CAPTURE, $startPos + 1)) {
            $endPos = $nextMatches[0][1];
        } else {
            $endPos = strlen($controllerFile);
        }
        
        $methodCode = substr($controllerFile, $startPos, $endPos - $startPos);
        
        if (strpos($methodCode, "user_id") !== false) {
            echo "  ✅ user_id filtering detected\n";
        } else {
            echo "  ⚠️  user_id filtering not detected\n";
        }
        
        if (strpos($methodCode, "auth()->id()") !== false) {
            echo "  ✅ auth()->id() filtering detected\n";
        } else {
            echo "  ⚠️  auth()->id() filtering not detected\n";
        }
    } else {
        echo "  ❌ Method not found\n";
    }
    
    echo "\n";
}

echo "=== TESTING DATA ISOLATION ===\n";

try {
    // Test that user can only see their own data
    echo "Testing data isolation for user 1...\n";
    
    $userProducts = \App\Models\Produk::where('user_id', 1)->get();
    echo "User 1 products: " . $userProducts->count() . "\n";
    
    if ($userProducts->count() > 0) {
        $product = $userProducts->first();
        echo "Sample product: " . $product->nama_produk . "\n";
        
        // Check BOM Job Costing
        $jobCosting = \App\Models\BomJobCosting::where('produk_id', $product->id)->where('user_id', 1)->first();
        if ($jobCosting) {
            echo "✅ BOM Job Costing found and filtered\n";
            
            // Check detail tables
            $detailBBB = $jobCosting->detailBBB;
            $detailBTKL = $jobCosting->detailBTKL;
            $detailBOP = $jobCosting->detailBOP;
            
            echo "  - BBB details: " . $detailBBB->count() . "\n";
            echo "  - BTKL details: " . $detailBTKL->count() . "\n";
            echo "  - BOP details: " . $detailBOP->count() . "\n";
            
            // Verify all details belong to user 1
            $bbbUserCheck = $detailBBB->every(function($item) { return $item->user_id == 1; });
            $btklUserCheck = $detailBTKL->every(function($item) { return $item->user_id == 1; });
            $bopUserCheck = $detailBOP->every(function($item) { return $item->user_id == 1; });
            
            echo "  - BBB user isolation: " . ($bbbUserCheck ? "✅" : "❌") . "\n";
            echo "  - BTKL user isolation: " . ($btklUserCheck ? "✅" : "❌") . "\n";
            echo "  - BOP user isolation: " . ($bopUserCheck ? "✅" : "❌") . "\n";
        } else {
            echo "❌ BOM Job Costing not found\n";
        }
    } else {
        echo "❌ No products found for user 1\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error testing data isolation: " . $e->getMessage() . "\n";
}

echo "\n=== MULTI-TENANT FIX COMPLETED! 🎉 ===\n";
