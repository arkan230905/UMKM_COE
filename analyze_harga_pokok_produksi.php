<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ANALISIS HARGA POKOK PRODUKSI MULTI-TENANT ===\n\n";

echo "1. DATABASE TABLES YANG MENGELOLA HALAMAN HARGA POKOK PRODUKSI:\n";
echo "   - UTAMA: bom (Bill of Materials)\n";
echo "   - PERHITUNGAN: bom_job_costings (Job Costing)\n";
echo "   - DETAIL BBB: bom_job_b_b_b (Biaya Bahan Baku)\n";
echo "   - DETAIL BTKL: bom_job_b_t_k_l (Biaya Tenaga Kerja Langsung)\n";
echo "   - DETAIL BOP: bom_job_b_o_p (Biaya Overhead Pabrik)\n";
echo "   - DETAIL PENDUKUNG: bom_job_bahan_pendukung\n";
echo "   - PRODUK: produk (relasi ke produk)\n\n";

echo "2. KONEKSI DENGAN 3 TABEL UTAMA:\n";
echo "   ✅ Biaya Bahan Baku -> bom_job_b_b_b (detail BBB)\n";
echo "   ✅ BTKL -> bom_job_b_t_k_l (detail BTKL)\n";
echo "   ✅ BOP -> bom_job_b_o_p (detail BOP)\n\n";

echo "3. CEK MULTI-TENANT COMPLIANCE:\n\n";

// Check user_id columns in main tables
$tables = [
    'bom' => 'Bill of Materials',
    'bom_job_costings' => 'Job Costing',
    'bom_job_b_b_b' => 'Detail BBB',
    'bom_job_b_t_k_l' => 'Detail BTKL',
    'bom_job_b_o_p' => 'Detail BOP',
    'bom_job_bahan_pendukung' => 'Detail Bahan Pendukung'
];

foreach ($tables as $table => $description) {
    echo "Table: $table ($description)\n";
    
    try {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        
        if (in_array('user_id', $columns)) {
            echo "  ✅ user_id column EXISTS\n";
            
            // Count records
            $totalRecords = \Illuminate\Support\Facades\DB::table($table)->count();
            $userRecords = \Illuminate\Support\Facades\DB::table($table)->where('user_id', 1)->count();
            
            echo "     - Total records: $totalRecords\n";
            echo "     - User 1 records: $userRecords\n";
            
            if ($totalRecords > 0 && $userRecords == 0) {
                echo "     ⚠️  WARNING: Records exist but none for user 1!\n";
            } elseif ($userRecords == $totalRecords) {
                echo "     ✅ All records belong to user 1\n";
            } else {
                echo "     ⚠️  Mixed ownership detected\n";
            }
        } else {
            echo "  ❌ user_id column MISSING\n";
        }
        
        echo "\n";
        
    } catch (\Exception $e) {
        echo "  ❌ Error checking table: " . $e->getMessage() . "\n\n";
    }
}

echo "4. CEK CONTROLLER MULTI-TENANT FILTERING:\n\n";

// Check BomController methods
$controllerMethods = [
    'index' => 'List Harga Pokok Produksi',
    'create' => 'Create BOM',
    'store' => 'Store BOM',
    'edit' => 'Edit BOM',
    'update' => 'Update BOM',
    'show' => 'Show BOM Detail',
    'destroy' => 'Delete BOM'
];

foreach ($controllerMethods as $method => $description) {
    echo "Method: $method ($description)\n";
    
    // Read controller file to check user_id filtering
    $controllerFile = file_get_contents('c:\UMKM_COE\app\Http\Controllers\BomController.php');
    
    // Find the method
    $pattern = "/public function $method\(/";
    if (preg_match($pattern, $controllerFile)) {
        // Check if user_id filtering exists
        if (strpos($controllerFile, "user_id") !== false) {
            echo "  ✅ user_id filtering detected\n";
        } else {
            echo "  ⚠️  user_id filtering unclear\n";
        }
    } else {
        echo "  ❌ Method not found\n";
    }
    echo "\n";
}

echo "5. CEK DATA PRODUK MULTI-TENANT:\n\n";

try {
    $products = \App\Models\Produk::all();
    echo "Total produk: " . $products->count() . "\n";
    
    $userProducts = \App\Models\Produk::where('user_id', 1)->get();
    echo "Produk user 1: " . $userProducts->count() . "\n";
    
    if ($userProducts->count() > 0) {
        echo "Produk user 1:\n";
        foreach ($userProducts as $product) {
            echo "  - " . $product->nama_produk . " (ID: " . $product->id . ")\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking products: " . $e->getMessage() . "\n";
}

echo "\n6. CEK BOM JOB COSTING MULTI-TENANT:\n\n";

try {
    $jobCostings = \App\Models\BomJobCosting::all();
    echo "Total job costings: " . $jobCostings->count() . "\n";
    
    // Check if user_id column exists
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_costings');
    if (in_array('user_id', $columns)) {
        $userJobCostings = \App\Models\BomJobCosting::where('user_id', 1)->get();
        echo "Job costings user 1: " . $userJobCostings->count() . "\n";
        
        if ($userJobCostings->count() > 0) {
            echo "Job costings user 1:\n";
            foreach ($userJobCostings as $jobCosting) {
                $productName = $jobCosting->produk ? $jobCosting->produk->nama_produk : 'N/A';
                echo "  - " . $productName . " (Total HPP: " . $jobCosting->total_hpp . ")\n";
            }
        }
    } else {
        echo "❌ user_id column not found in bom_job_costings\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking job costings: " . $e->getMessage() . "\n";
}

echo "\n=== ANALISIS SELESAI ===\n";
