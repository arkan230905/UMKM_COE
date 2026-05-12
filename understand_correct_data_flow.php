<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== MEMAHAMI ALUR DATA YANG BENAR ===\n\n";

echo "1. CEK STRUKTUR bom_job_bbb:\n\n";

try {
    $bbbData = \App\Models\BomJobBBB::with(['bahanBaku'])->get();
    
    echo "Data di bom_job_bbb:\n";
    foreach ($bbbData as $bbb) {
        echo "ID: " . $bbb->id . "\n";
        echo "BOM Job Costing ID: " . $bbb->bom_job_costing_id . "\n";
        echo "User ID: " . $bbb->user_id . "\n";
        echo "Bahan Baku ID: " . $bbb->bahan_baku_id . "\n";
        echo "Produk ID: ??? (tidak ada field produk_id)\n";
        echo "Bahan Baku: " . ($bbb->bahanBaku->nama_bahan_baku ?? 'N/A') . "\n";
        echo "Jumlah: " . $bbb->jumlah . " " . $bbb->satuan . "\n";
        echo "Subtotal: Rp " . number_format($bbb->subtotal, 2, ',', '.') . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking bom_job_bbb: " . $e->getMessage() . "\n";
}

echo "\n2. CEK STRUKTUR bom_job_costings:\n\n";

try {
    $jobCostings = \App\Models\BomJobCosting::with('produk')->get();
    
    echo "Data di bom_job_costings:\n";
    foreach ($jobCostings as $jc) {
        echo "ID: " . $jc->id . "\n";
        echo "Produk ID: " . $jc->produk_id . "\n";
        echo "User ID: " . $jc->user_id . "\n";
        echo "Produk: " . ($jc->produk->nama_produk ?? 'N/A') . "\n";
        echo "Total BBB: " . $jc->total_bbb . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking bom_job_costings: " . $e->getMessage() . "\n";
}

echo "\n3. CEK BAHAN BAKU RELATION:\n\n";

try {
    $bahanBakus = \App\Models\BahanBaku::all();
    
    echo "Data di bahan_bakus:\n";
    foreach ($bahanBakus as $bb) {
        echo "ID: " . $bb->id . "\n";
        echo "Nama: " . $bb->nama_bahan_baku . "\n";
        echo "User ID: " . $bb->user_id . "\n";
        echo "Produk ID: " . $bb->produk_id . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking bahan_bakus: " . $e->getMessage() . "\n";
}

echo "\n4. ANALISIS ALUR DATA YANG BENAR:\n\n";

echo "❌ KESALAHAN PEMAHAMAN SAYA:\n";
echo "- Saya kira bom_job_bbb.bahan_baku_id = produk_id\n";
echo "- Ternyata bom_job_bbb.bahan_baku_id = bahan_bakus.id\n";
echo "- Dan bahan_bakus.produk_id = produks.id\n\n";

echo "✅ ALUR DATA YANG BENAR:\n";
echo "1. User input biaya bahan → simpan ke bom_job_bbb\n";
echo "2. bom_job_bbb.bahan_baku_id → relasi ke bahan_bakus.id\n";
echo "3. bahan_bakus.produk_id → relasi ke produks.id\n";
echo "4. Dari bom_job_bbb bisa dapat produk_id melalui bahan_bakus\n\n";

echo "5. QUERY YANG BENAR UNTUK PRODUK SELECTION:\n\n";

try {
    echo "Query yang benar:\n";
    echo "\$produkIds = BomJobBBB::where('user_id', auth()->id())\n";
    echo "    ->join('bahan_bakus', 'bom_job_b_b_b.bahan_baku_id', '=', 'bahan_bakus.id')\n";
    echo "    ->pluck('bahan_bakus.produk_id')\n";
    echo "    ->unique();\n\n";
    
    // Test correct query
    $produkIds = \App\Models\BomJobBBB::where('user_id', 1)
        ->join('bahan_bakus', 'bom_job_bbb.bahan_baku_id', '=', 'bahan_bakus.id')
        ->pluck('bahan_bakus.produk_id')
        ->unique();
    
    echo "Produk IDs yang ditemukan: " . $produkIds->implode(', ') . "\n\n";
    
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
            ->join('bahan_bakus', 'bom_job_bbb.bahan_baku_id', '=', 'bahan_bakus.id')
            ->where('bahan_bakus.produk_id', $product->id)
            ->get();
        
        foreach ($bbbDetails as $bbb) {
            echo "  - " . $bbb->nama_bahan_baku . ": " . $bbb->jumlah . " " . $bbb->satuan . " @ Rp " . number_format($bbb->harga_satuan, 2, ',', '.') . "\n";
        }
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error testing correct query: " . $e->getMessage() . "\n";
}

echo "\n6. PERBAIKAN YANG DIBUTUHKAN DI BomController@create:\n\n";

echo "SEBELUM (salah):\n";
echo "\$produkIds = BomJobBBB::where('user_id', auth()->id())->distinct('bahan_baku_id')->pluck('bahan_baku_id');\n";
echo "❌ Ini mengambil bahan_baku_id, bukan produk_id\n\n";

echo "SETELAH (benar):\n";
echo "\$produkIds = BomJobBBB::where('user_id', auth()->id())\n";
echo "    ->join('bahan_bakus', 'bom_job_bbb.bahan_baku_id', '=', 'bahan_bakus.id')\n";
echo "    ->pluck('bahan_bakus.produk_id')\n";
echo "    ->unique();\n";
echo "✅ Ini mengambil produk_id yang benar\n\n";

echo "=== ANALISIS SELESAI ===\n";
