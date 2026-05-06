<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== INVESTIGASI PRODUCT SELECTION DATA SOURCE ===\n\n";

echo "1. CEK DATA DI bom_job_bbb:\n\n";

try {
    $bbbData = \App\Models\BomJobBBB::with(['bomJobCosting.produk', 'bahanBaku'])->get();
    
    echo "Data di bom_job_bbb:\n";
    foreach ($bbbData as $bbb) {
        echo "ID: " . $bbb->id . "\n";
        echo "BOM Job Costing ID: " . $bbb->bom_job_costing_id . "\n";
        echo "User ID: " . $bbb->user_id . "\n";
        echo "Produk: " . ($bbb->bomJobCosting->produk->nama_produk ?? 'N/A') . "\n";
        echo "Bahan Baku: " . ($bbb->bahanBaku->nama_bahan_baku ?? 'N/A') . "\n";
        echo "Jumlah: " . $bbb->jumlah . " " . $bbb->satuan . "\n";
        echo "Harga Satuan: Rp " . number_format($bbb->harga_satuan, 2, ',', '.') . "\n";
        echo "Subtotal: Rp " . number_format($bbb->subtotal, 2, ',', '.') . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking bom_job_bbb: " . $e->getMessage() . "\n";
}

echo "\n2. CEK QUERY SAAT INI DI BomController@create:\n\n";

try {
    echo "Query saat ini:\n";
    echo "\$produks = Produk::where('user_id', auth()->id())\n";
    echo "    ->whereHas('bomJobCosting')\n";
    echo "    ->with('bomJobCosting')\n";
    echo "    ->get();\n\n";
    
    echo "❌ MASALAH: Query ini hanya cek bom_job_costings TANPA cek bom_job_bbb!\n";
    echo "❌ Produk muncul meskipun belum ada data biaya bahan di bom_job_bbb\n\n";
    
    // Test current query
    $currentProducts = \App\Models\Produk::where('user_id', 1)
        ->whereHas('bomJobCosting')
        ->with('bomJobCosting')
        ->get();
    
    echo "Hasil query saat ini:\n";
    foreach ($currentProducts as $product) {
        echo "- " . $product->nama_produk . "\n";
        echo "  BomJobCosting ID: " . $product->bomJobCosting->id . "\n";
        echo "  Total BBB: " . $product->bomJobCosting->total_bbb . "\n";
        
        // Check if there's actual bom_job_bbb data
        $bbbCount = \App\Models\BomJobBBB::where('bom_job_costing_id', $product->bomJobCosting->id)
            ->where('user_id', 1)
            ->count();
        echo "  Actual bom_job_bbb records: " . $bbbCount . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking current query: " . $e->getMessage() . "\n";
}

echo "\n3. QUERY YANG BENAR (HARUS MENGGUNAKAN bom_job_bbb):\n\n";

try {
    echo "Query yang benar:\n";
    echo "\$produks = Produk::where('user_id', auth()->id())\n";
    echo "    ->whereHas('bomJobCosting', function(\$query) {\n";
    echo "        \$query->whereHas('detailBBB', function(\$subQuery) {\n";
    echo "            \$subQuery->where('user_id', auth()->id());\n";
    echo "        });\n";
    echo "    })\n";
    echo "    ->with(['bomJobCosting.detailBBB.bahanBaku'])\n";
    echo "    ->get();\n\n";
    
    // Test correct query
    $correctProducts = \App\Models\Produk::where('user_id', 1)
        ->whereHas('bomJobCosting', function($query) {
            $query->whereHas('detailBBB', function($subQuery) {
                $subQuery->where('user_id', 1);
            });
        })
        ->with(['bomJobCosting.detailBBB.bahanBaku'])
        ->get();
    
    echo "Hasil query yang benar:\n";
    foreach ($correctProducts as $product) {
        echo "- " . $product->nama_produk . "\n";
        echo "  BomJobCosting ID: " . $product->bomJobCosting->id . "\n";
        echo "  Total BBB: " . $product->bomJobCosting->total_bbb . "\n";
        echo "  Detail BBB count: " . $product->bomJobCosting->detailBBB->count() . "\n";
        
        foreach ($product->bomJobCosting->detailBBB as $bbb) {
            echo "    - " . $bbb->bahanBaku->nama_bahan_baku . ": " . $bbb->jumlah . " " . $bbb->satuan . " @ Rp " . number_format($bbb->harga_satuan, 2, ',', '.') . "\n";
        }
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking correct query: " . $e->getMessage() . "\n";
}

echo "\n4. ANALISIS MASALAH:\n\n";

echo "❌ MASALAH UTAMA:\n";
echo "- Query saat ini hanya cek keberadaan BomJobCosting\n";
echo "- Tidak cek apakah ada data biaya bahan (bom_job_bbb)\n";
echo "- Produk muncul meskipun belum ada input biaya bahan\n";
echo "- Tidak ada user_id filtering untuk bom_job_bbb\n\n";

echo "✅ SOLUSI:\n";
echo "- Tambah whereHas untuk detailBBB dengan user_id filtering\n";
echo "- Include detailBBB.bahanBaku di eager loading\n";
echo "- Pastikan hanya produk dengan data biaya bahan user yang muncul\n\n";

echo "5. CEK RELASI MODEL:\n\n";

try {
    echo "Cek relasi BomJobCosting->detailBBB:\n";
    $bomJobCosting = \App\Models\BomJobCosting::find(2);
    
    if ($bomJobCosting) {
        $detailBBB = $bomJobCosting->detailBBB;
        echo "Detail BBB count: " . $detailBBB->count() . "\n";
        
        foreach ($detailBBB as $bbb) {
            echo "- " . $bbb->bahanBaku->nama_bahan_baku . " (User ID: " . $bbb->user_id . ")\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking relations: " . $e->getMessage() . "\n";
}

echo "\n=== INVESTIGASI SELESAI ===\n";
