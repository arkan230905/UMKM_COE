<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK STRUKTUR DATABASE YANG SEBENARNYA ===\n\n";

echo "1. CEK STRUKTUR bahan_bakus:\n\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bahan_bakus');
    echo "Columns di bahan_bakus:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    echo "\nData di bahan_bakus:\n";
    $bahanBakus = \App\Models\BahanBaku::all();
    foreach ($bahanBakus as $bb) {
        echo "ID: " . $bb->id . "\n";
        echo "Nama: '" . $bb->nama_bahan_baku . "'\n";
        echo "User ID: " . $bb->user_id . "\n";
        echo "Produk ID: " . ($bb->produk_id ?? 'NULL') . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking bahan_bakus: " . $e->getMessage() . "\n";
}

echo "\n2. CEK STRUKTUR bom_job_bbb:\n\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_bbb');
    echo "Columns di bom_job_bbb:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    echo "\nData di bom_job_bbb:\n";
    $bbbData = \App\Models\BomJobBBB::all();
    foreach ($bbbData as $bbb) {
        echo "ID: " . $bbb->id . "\n";
        echo "BOM Job Costing ID: " . $bbb->bom_job_costing_id . "\n";
        echo "User ID: " . $bbb->user_id . "\n";
        echo "Bahan Baku ID: " . $bbb->bahan_baku_id . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking bom_job_bbb: " . $e->getMessage() . "\n";
}

echo "\n3. CEK STRUKTUR bom_job_costings:\n\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_costings');
    echo "Columns di bom_job_costings:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    echo "\nData di bom_job_costings:\n";
    $jobCostings = \App\Models\BomJobCosting::with('produk')->get();
    foreach ($jobCostings as $jc) {
        echo "ID: " . $jc->id . "\n";
        echo "Produk ID: " . $jc->produk_id . "\n";
        echo "User ID: " . $jc->user_id . "\n";
        echo "Produk: " . ($jc->produk->nama_produk ?? 'N/A') . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking bom_job_costings: " . $e->getMessage() . "\n";
}

echo "\n4. ANALISIS KONEKSI DATA:\n\n";

try {
    echo "Cek koneksi bom_job_bbb -> bom_job_costings -> produk:\n\n";
    
    $bbbData = \App\Models\BomJobBBB::with(['bomJobCosting.produk'])->get();
    
    foreach ($bbbData as $bbb) {
        echo "BBB ID: " . $bbb->id . "\n";
        echo "BOM Job Costing ID: " . $bbb->bom_job_costing_id . "\n";
        
        if ($bbb->bomJobCosting) {
            echo "Produk: " . $bbb->bomJobCosting->produk->nama_produk . "\n";
            echo "Produk ID: " . $bbb->bomJobCosting->produk->id . "\n";
            echo "User ID: " . $bbb->bomJobCosting->user_id . "\n";
        } else {
            echo "Tidak ada BomJobCosting terkait\n";
        }
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking connections: " . $e->getMessage() . "\n";
}

echo "\n5. QUERY YANG BENAR UNTUK PRODUK SELECTION:\n\n";

try {
    echo "Dari data bom_job_bbb, kita dapat produk_id melalui bom_job_costings\n\n";
    
    // Get unique produk_id from bom_job_bbb through bom_job_costings
    $produkIds = \App\Models\BomJobBBB::where('user_id', 1)
        ->join('bom_job_costings', 'bom_job_bbb.bom_job_costing_id', '=', 'bom_job_costings.id')
        ->pluck('bom_job_costings.produk_id')
        ->unique();
    
    echo "Produk IDs dari bom_job_bbb: " . $produkIds->implode(', ') . "\n\n";
    
    $produks = \App\Models\Produk::where('user_id', 1)
        ->whereIn('id', $produkIds)
        ->with(['bomJobCosting' => function($query) {
            $query->where('user_id', 1);
        }])
        ->get();
    
    echo "Produk yang memiliki data biaya bahan:\n";
    foreach ($produks as $product) {
        echo "- " . $product->nama_produk . " (ID: " . $product->id . ")\n";
        
        // Show biaya bahan details
        $bbbDetails = \App\Models\BomJobBBB::where('user_id', 1)
            ->join('bom_job_costings', 'bom_job_bbb.bom_job_costing_id', '=', 'bom_job_costings.id')
            ->where('bom_job_costings.produk_id', $product->id)
            ->get();
        
        foreach ($bbbDetails as $bbb) {
            echo "  - BBB ID: " . $bbb->id . " (BOM Job Costing ID: " . $bbb->bom_job_costing_id . ")\n";
        }
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error testing final query: " . $e->getMessage() . "\n";
}

echo "\n6. KESIMPULAN ALUR DATA YANG BENAR:\n\n";

echo "✅ ALUR DATA:\n";
echo "1. User input biaya bahan → bom_job_bbb (dengan bom_job_costing_id)\n";
echo "2. bom_job_bbb.bom_job_costing_id → relasi ke bom_job_costings.id\n";
echo "3. bom_job_costings.produk_id → relasi ke produks.id\n";
echo "4. Dari bom_job_bbb bisa dapat produk_id melalui bom_job_costings\n\n";

echo "✅ QUERY YANG BENAR:\n";
echo "\$produkIds = BomJobBBB::where('user_id', auth()->id())\n";
echo "    ->join('bom_job_costings', 'bom_job_bbb.bom_job_costing_id', '=', 'bom_job_costings.id')\n";
echo "    ->pluck('bom_job_costings.produk_id')\n";
echo "    ->unique();\n\n";

echo "=== ANALISIS SELESAI ===\n";
