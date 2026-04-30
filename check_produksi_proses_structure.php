<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking ProduksiProses table structure...\n";

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('produksi_proses');
echo "ProduksiProses Table Columns:\n";
foreach ($columns as $column) {
    echo "  - {$column}\n";
}

$hasUserId = \Illuminate\Support\Facades\Schema::hasColumn('produksi_proses', 'user_id');
echo "\nHas user_id column: " . ($hasUserId ? "YES" : "NO") . "\n";

// Check if table exists and has data
if (\Illuminate\Support\Facades\Schema::hasTable('produksi_proses')) {
    $totalCount = \Illuminate\Support\Facades\DB::table('produksi_proses')->count();
    echo "Total records: {$totalCount}\n";
    
    // Check if we can access production processes through Produksi model
    try {
        $produksiCount = \App\Models\Produksi::where('user_id', 1)->count();
        echo "Produksi records (user_id=1): {$produksiCount}\n";
        
        if ($produksiCount > 0) {
            $produksi = \App\Models\Produksi::where('user_id', 1)->first();
            $processCount = $produksi->proses()->count();
            echo "Processes for first produksi: {$processCount}\n";
        }
    } catch (Exception $e) {
        echo "Error accessing produksi data: " . $e->getMessage() . "\n";
    }
}

echo "\nProduksiProses structure check completed!\n";
