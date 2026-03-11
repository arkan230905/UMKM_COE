<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG KETEMPLING BIAYA ===" . PHP_EOL;

// Cek produk Ketempling
$ketempling = \App\Models\Produk::where('nama_produk', 'Ketempling')->first();
if (!$ketempling) {
    echo "Produk Ketempling tidak ditemukan!" . PHP_EOL;
    exit;
}

echo "Produk: {$ketempling->nama_produk} (ID: {$ketempling->id})" . PHP_EOL;

// Cek BomJobCosting
$bomJobCosting = \App\Models\BomJobCosting::with(['detailBBB.bahanBaku', 'detailBahanPendukung.bahanPendukung'])
    ->where('produk_id', $ketempling->id)
    ->first();

echo PHP_EOL . "BomJobCosting: " . ($bomJobCosting ? "ADA (ID: {$bomJobCosting->id})" : "TIDAK ADA") . PHP_EOL;

if ($bomJobCosting) {
    echo "Total BBB: " . $bomJobCosting->total_bbb . PHP_EOL;
    echo "Total Bahan Pendukung: " . $bomJobCosting->total_bahan_pendukung . PHP_EOL;
    
    // Cek detail BBB
    echo PHP_EOL . "Detail BBB:" . PHP_EOL;
    if ($bomJobCosting->detailBBB) {
        foreach ($bomJobCosting->detailBBB as $bbb) {
            $namaBahan = $bbb->bahanBaku ? $bbb->bahanBaku->nama_bahan : 'Unknown';
            $hargaSatuan = $bbb->harga_satuan ?? 0;
            $jumlah = $bbb->jumlah ?? 0;
            $subtotal = $bbb->subtotal ?? 0;
            
            echo "- {$namaBahan}: {$jumlah} x " . number_format($hargaSatuan, 2, ',', '.') . 
                 " = " . number_format($subtotal, 2, ',', '.') . PHP_EOL;
            echo "  Status: " . ($bbb->harga_satuan == 0 && !empty($bbb->catatan_hapus) ? 'DIHAPUS' : 'AKTIF') . PHP_EOL;
            echo "  Catatan: " . ($bbb->catatan_hapus ?? 'Tidak ada') . PHP_EOL;
        }
    } else {
        echo "Tidak ada detail BBB" . PHP_EOL;
    }
    
    // Cek detail Bahan Pendukung
    echo PHP_EOL . "Detail Bahan Pendukung:" . PHP_EOL;
    if ($bomJobCosting->detailBahanPendukung) {
        foreach ($bomJobCosting->detailBahanPendukung as $bp) {
            $namaBahan = $bp->bahanPendukung ? $bp->bahanPendukung->nama_bahan : 'Unknown';
            $hargaSatuan = $bp->harga_satuan ?? 0;
            $jumlah = $bp->jumlah ?? 0;
            $subtotal = $bp->subtotal ?? 0;
            
            echo "- {$namaBahan}: {$jumlah} x " . number_format($hargaSatuan, 2, ',', '.') . 
                 " = " . number_format($subtotal, 2, ',', '.') . PHP_EOL;
            echo "  Status: " . ($bp->harga_satuan == 0 && !empty($bp->catatan_hapus) ? 'DIHAPUS' : 'AKTIF') . PHP_EOL;
        }
    } else {
        echo "Tidak ada detail Bahan Pendukung" . PHP_EOL;
    }
    
    // Hitung total yang seharusnya
    $totalBiayaBahanBaku = $bomJobCosting->detailBBB->sum('subtotal') ?? 0;
    $totalBiayaBahanPendukung = $bomJobCosting->detailBahanPendukung->sum('subtotal') ?? 0;
    $totalBiaya = $totalBiayaBahanBaku + $totalBiayaBahanPendukung;
    
    echo PHP_EOL . "PERHITUNGAN:" . PHP_EOL;
    echo "Total BBB (sum): " . number_format($totalBiayaBahanBaku, 2, ',', '.') . PHP_EOL;
    echo "Total Bahan Pendukung (sum): " . number_format($totalBiayaBahanPendukung, 2, ',', '.') . PHP_EOL;
    echo "Total Biaya: " . number_format($totalBiaya, 2, ',', '.') . PHP_EOL;
    
    echo PHP_EOL . "STATUS LOGIC:" . PHP_EOL;
    echo "Jumlah BBB: " . $bomJobCosting->detailBBB->count() . PHP_EOL;
    echo "Jumlah Bahan Pendukung: " . $bomJobCosting->detailBahanPendukung->count() . PHP_EOL;
    echo "Total Biaya > 0: " . ($totalBiaya > 0 ? 'YES' : 'NO') . PHP_EOL;
    echo "Status seharusnya: " . ($totalBiaya > 0 ? 'Lengkap' : 'Kosong') . PHP_EOL;
} else {
    // Cek BOM lama
    $bom = \App\Models\Bom::with('details.bahanBaku')
        ->where('produk_id', $ketempling->id)
        ->first();
    
    echo PHP_EOL . "BOM: " . ($bom ? "ADA (ID: {$bom->id})" : "TIDAK ADA") . PHP_EOL;
    
    if ($bom) {
        echo "Total Biaya: " . $bom->total_biaya . PHP_EOL;
        echo "Jumlah Details: " . $bom->details->count() . PHP_EOL;
    }
}

echo PHP_EOL . "✅ Debug selesai!" . PHP_EOL;
