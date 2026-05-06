<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX BOM_JOB_BBB STRUCTURE ===\n\n";

echo "1. CEK STRUKTUR SAAT INI:\n\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_bbb');
    echo "Columns di bom_job_bbb saat ini:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    if (!in_array('produk_id', $columns)) {
        echo "\n❌ produk_id column TIDAK ADA\n";
        echo "Perlu ditambahkan produk_id column\n\n";
    } else {
        echo "\n✅ produk_id column SUDAH ADA\n\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking structure: " . $e->getMessage() . "\n";
}

echo "2. TAMBAHKAN produk_id COLUMN (Jika belum ada):\n\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_bbb');
    
    if (!in_array('produk_id', $columns)) {
        echo "Menambahkan produk_id column...\n";
        
        \Illuminate\Support\Facades\Schema::table('bom_job_bbb', function ($table) {
            $table->unsignedBigInteger('produk_id')->after('user_id')->nullable();
            $table->index('produk_id');
        });
        
        echo "✅ produk_id column berhasil ditambahkan\n\n";
        
        // Update existing data
        echo "Update existing data...\n";
        $bbbData = \App\Models\BomJobBBB::with(['bomJobCosting.produk'])->get();
        
        foreach ($bbbData as $bbb) {
            if ($bbb->bomJobCosting && $bbb->bomJobCosting->produk) {
                $bbb->produk_id = $bbb->bomJobCosting->produk->id;
                $bbb->save();
                echo "  - Update BBB ID " . $bbb->id . " → produk_id " . $bbb->produk_id . "\n";
            }
        }
        
        echo "✅ Data existing berhasil diupdate\n\n";
        
    } else {
        echo "✅ produk_id column sudah ada, tidak perlu ditambahkan\n\n";
    }
    
} catch (\Exception $e) {
    echo "Error adding column: " . $e->getMessage() . "\n";
}

echo "3. VERIFIKASI STRUKTUR BARU:\n\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_bbb');
    echo "Columns di bom_job_bbb setelah perbaikan:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    echo "\nData di bom_job_bbb:\n";
    $bbbData = \App\Models\BomJobBBB::with(['bomJobCosting.produk'])->get();
    
    foreach ($bbbData as $bbb) {
        echo "ID: " . $bbb->id . "\n";
        echo "User ID: " . $bbb->user_id . "\n";
        echo "Produk ID: " . $bbb->produk_id . "\n";
        echo "Bahan Baku ID: " . $bbb->bahan_baku_id . "\n";
        echo "BOM Job Costing ID: " . $bbb->bom_job_costing_id . "\n";
        
        if ($bbb->bomJobCosting && $bbb->bomJobCosting->produk) {
            echo "Produk: " . $bbb->bomJobCosting->produk->nama_produk . "\n";
        }
        
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error verifying structure: " . $e->getMessage() . "\n";
}

echo "\n4. QUERY YANG BENAR SETELAH PERBAIKAN:\n\n";

try {
    echo "Query yang benar untuk produk selection:\n";
    echo "\$produkIds = BomJobBBB::where('user_id', auth()->id())\n";
    echo "    ->pluck('produk_id')\n";
    echo "    ->unique();\n\n";
    
    // Test the corrected query
    $produkIds = \App\Models\BomJobBBB::where('user_id', 1)
        ->pluck('produk_id')
        ->unique();
    
    echo "Produk IDs yang ditemukan: " . $produkIds->implode(', ') . "\n\n";
    
    $produks = \App\Models\Produk::where('user_id', 1)
        ->whereIn('id', $produkIds)
        ->get();
    
    echo "Produk yang akan ditampilkan:\n";
    foreach ($produks as $product) {
        echo "- " . $product->nama_produk . " (ID: " . $product->id . ")\n";
        
        // Show biaya bahan details
        $bbbDetails = \App\Models\BomJobBBB::where('user_id', 1)
            ->where('produk_id', $product->id)
            ->get();
        
        foreach ($bbbDetails as $bbb) {
            echo "  - BBB ID: " . $bbb->id . " (Produk ID: " . $bbb->produk_id . ")\n";
            echo "  - Jumlah: " . $bbb->jumlah . " " . $bbb->satuan . "\n";
            echo "  - Subtotal: Rp " . number_format($bbb->subtotal, 2, ',', '.') . "\n";
        }
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error testing corrected query: " . $e->getMessage() . "\n";
}

echo "\n5. UPDATE BomController@create METHOD:\n\n";

echo "Perlu update BomController@create untuk menggunakan query yang benar:\n";
echo "SEBELUM:\n";
echo "\$produkIds = BomJobBBB::where('bom_job_bbb.user_id', auth()->id())\n";
echo "    ->join('bom_job_costings', 'bom_job_bbb.bom_job_costing_id', '=', 'bom_job_costings.id')\n";
echo "    ->pluck('bom_job_costings.produk_id')\n\n";

echo "SETELAH:\n";
echo "\$produkIds = BomJobBBB::where('user_id', auth()->id())\n";
echo "    ->pluck('produk_id')\n";
echo "    ->unique();\n\n";

echo "=== PERBAIKAN STRUKTUR SELESAI ===\n";
