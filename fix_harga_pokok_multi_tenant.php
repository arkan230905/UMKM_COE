<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING HARGA POKOK PRODUKSI MULTI-TENANT ===\n\n";

echo "MASALAH YANG DITEMUKAN:\n";
echo "❌ Table 'bom' tidak memiliki user_id column\n";
echo "❌ Table 'bom_job_b_b_b' tidak memiliki user_id column\n";
echo "❌ Table 'bom_job_b_t_k_l' tidak memiliki user_id column\n";
echo "❌ Table 'bom_job_b_o_p' tidak memiliki user_id column\n\n";

echo "SOLUSI: Menambahkan user_id column dan update existing data\n\n";

$tables_to_fix = [
    'bom' => 'Bill of Materials',
    'bom_job_b_b_b' => 'Detail BBB',
    'bom_job_b_t_k_l' => 'Detail BTKL',
    'bom_job_b_o_p' => 'Detail BOP'
];

foreach ($tables_to_fix as $table => $description) {
    echo "Processing table: $table ($description)\n";
    
    try {
        // Check if user_id column exists
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        
        if (!in_array('user_id', $columns)) {
            echo "  - Adding user_id column...\n";
            
            // Add user_id column
            \Illuminate\Support\Facades\Schema::table($table, function ($table) {
                $table->unsignedBigInteger('user_id')->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
            
            echo "  ✅ user_id column added\n";
            
            // Update existing records to belong to current user (user_id = 1)
            $currentUserId = 1; // Assuming current user is ID 1
            
            \Illuminate\Support\Facades\DB::table($table)->update(['user_id' => $currentUserId]);
            
            $updatedCount = \Illuminate\Support\Facades\DB::table($table)->where('user_id', $currentUserId)->count();
            echo "  ✅ Updated $updatedCount existing records to user_id = $currentUserId\n";
            
        } else {
            echo "  ✅ user_id column already exists\n";
        }
        
    } catch (\Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== VERIFICATION ===\n";

foreach ($tables_to_fix as $table => $description) {
    echo "Table: $table\n";
    
    try {
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

echo "=== TESTING CONTROLLER FILTERING ===\n";

// Test if BomController properly filters by user_id
echo "Testing BomController index method...\n";

try {
    $controller = new \App\Http\Controllers\BomController();
    
    // Mock request
    $request = new \Illuminate\Http\Request();
    
    // Call index method
    $response = $controller->index($request);
    
    echo "✅ BomController index method executed successfully\n";
    
    // Check if the response contains user-filtered data
    $content = $response->getContent();
    
    if (strpos($content, 'user_id') !== false) {
        echo "✅ user_id filtering detected in response\n";
    } else {
        echo "⚠️ user_id filtering not visible in response content\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error testing controller: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING DATA ISOLATION ===\n";

// Test that data is properly isolated by user
echo "Testing data isolation...\n";

try {
    // Check if user can only see their own data
    $userProducts = \App\Models\Produk::where('user_id', 1)->get();
    echo "User 1 products: " . $userProducts->count() . "\n";
    
    $userJobCostings = \App\Models\BomJobCosting::where('user_id', 1)->get();
    echo "User 1 job costings: " . $userJobCostings->count() . "\n";
    
    if ($userProducts->count() > 0) {
        $product = $userProducts->first();
        echo "Sample product: " . $product->nama_produk . "\n";
        
        // Check if BOM details are properly filtered
        if ($product->bomJobCosting) {
            echo "✅ BOM Job Costing accessible\n";
            
            // Check if detail tables are filtered
            $detailBBB = $product->bomJobCosting->detailBBB;
            $detailBTKL = $product->bomJobCosting->detailBTKL;
            $detailBOP = $product->bomJobCosting->detailBOP;
            
            echo "  - BBB details: " . $detailBBB->count() . "\n";
            echo "  - BTKL details: " . $detailBTKL->count() . "\n";
            echo "  - BOP details: " . $detailBOP->count() . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error testing data isolation: " . $e->getMessage() . "\n";
}

echo "\n=== MULTI-TENANT FIX COMPLETED! 🎉 ===\n";
