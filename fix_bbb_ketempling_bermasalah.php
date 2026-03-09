<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX BBB KETAMPLING BERMASALAH ===" . PHP_EOL;

try {
    // 1. Cari BomJobCosting ketempling
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();
    if (!$bomJobCosting) {
        echo "BomJobCosting untuk ketempling tidak ditemukan!" . PHP_EOL;
        exit;
    }
    
    echo "BomJobCosting ID: {$bomJobCosting->id}" . PHP_EOL;
    echo "Produk: {$bomJobCosting->produk->nama_produk}" . PHP_EOL . PHP_EOL;
    
    // 2. Cek dan hapus BBB yang bermasalah
    echo "Mengecek BBB yang bermasalah..." . PHP_EOL;
    
    $bbbDetails = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
    $deletedCount = 0;
    
    foreach ($bbbDetails as $bbb) {
        $isProblematic = false;
        $reason = '';
        
        // Cek apakah bahan_baku_id valid
        if ($bbb->bahan_baku_id) {
            $bahanBaku = \App\Models\BahanBaku::find($bbb->bahan_baku_id);
            if (!$bahanBaku) {
                $isProblematic = true;
                $reason = "Bahan Baku ID {$bbb->bahan_baku_id} tidak ditemukan";
            }
        } else {
            $isProblematic = true;
            $reason = "Bahan Baku ID kosong";
        }
        
        // Cek apakah harga 0 (sudah dihapus)
        if ($bbb->harga_satuan == 0 && $bbb->subtotal == 0) {
            $isProblematic = true;
            $reason .= ($reason ? ', ' : '') . "Harga 0 (sudah dihapus)";
        }
        
        if ($isProblematic) {
            echo "Menghapus BBB ID {$bbb->id}: {$reason}" . PHP_EOL;
            $bbb->delete();
            $deletedCount++;
        } else {
            echo "BBB ID {$bbb->id} OK - " . ($bbb->bahanBaku->nama_bahan ?? 'Unknown') . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "Total BBB yang dihapus: {$deletedCount}" . PHP_EOL;
    
    // 3. Tambah BBB default jika kosong
    $remainingBBB = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->count();
    echo "BBB tersisa: {$remainingBBB}" . PHP_EOL;
    
    if ($remainingBBB == 0) {
        echo PHP_EOL . "Menambahkan BBB default..." . PHP_EOL;
        
        // Cari bahan baku yang masih ada
        $availableBahan = \App\Models\BahanBaku::where('harga_satuan', '>', 0)->first();
        
        if ($availableBahan) {
            $newBBB = \App\Models\BomJobBBB::create([
                'bom_job_costing_id' => $bomJobCosting->id,
                'bahan_baku_id' => $availableBahan->id,
                'jumlah' => 0.5,
                'satuan' => $availableBahan->satuan->nama ?? 'KG',
                'harga_satuan' => $availableBahan->harga_satuan,
                'subtotal' => 0.5 * $availableBahan->harga_satuan
            ]);
            
            echo "BBB ditambahkan: {$availableBahan->nama_bahan} - 0.5 {$newBBB->satuan} @ Rp " . 
                 number_format($availableBahan->harga_satuan, 2, ',', '.') . 
                 " = Rp " . number_format($newBBB->subtotal, 2, ',', '.') . PHP_EOL;
        } else {
            echo "⚠️ Tidak ada bahan baku yang tersedia, melewati penambahan BBB" . PHP_EOL;
        }
    }
    
    // 4. Recalculate
    echo PHP_EOL . "Menghitung ulang BomJobCosting..." . PHP_EOL;
    $bomJobCosting->recalculate();
    
    echo "Hasil:" . PHP_EOL;
    echo "- Total BBB: Rp " . number_format($bomJobCosting->total_bbb, 2, ',', '.') . PHP_EOL;
    echo "- Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
    echo "- Total BOP: Rp " . number_format($bomJobCosting->total_bop, 2, ',', '.') . PHP_EOL;
    echo "- Total HPP: Rp " . number_format($bomJobCosting->total_hpp, 2, ',', '.') . PHP_EOL;
    
    // 5. Update produk
    $produk = $bomJobCosting->produk;
    $produk->update([
        'harga_bom' => $bomJobCosting->total_hpp,
        'harga_pokok' => $bomJobCosting->total_hpp
    ]);
    
    echo PHP_EOL . "Data produk:" . PHP_EOL;
    echo "- Harga BOM: Rp " . number_format($produk->harga_bom, 2, ',', '.') . PHP_EOL;
    echo "- Harga Pokok: Rp " . number_format($produk->harga_pokok, 2, ',', '.') . PHP_EOL;
    
    // 6. Verifikasi akhir
    echo PHP_EOL . "VERIFIKASI AKHIR:" . PHP_EOL;
    
    $finalBBB = \App\Models\BomJobBBB::with('bahanBaku')
        ->where('bom_job_costing_id', $bomJobCosting->id)
        ->get();
    
    if ($finalBBB->isEmpty()) {
        echo "⚠️ Tidak ada BBB tersisa" . PHP_EOL;
    } else {
        foreach ($finalBBB as $bbb) {
            $status = $bbb->bahanBaku ? 'OK' : 'MASALAH';
            $nama = $bbb->bahanBaku ? $bbb->bahanBaku->nama_bahan : 'NULL';
            echo "- BBB ID {$bbb->id}: {$nama} ({$status})" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "✅ Fix completed! Sekarang seharusnya tidak ada error lagi." . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}
