<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Harga Cabe ===" . PHP_EOL;

// Fix Cabe Hijau (ID: 10)
echo PHP_EOL . "Fix Cabe Hijau (ID: 10):" . PHP_EOL;
$cabeHijau = \App\Models\BahanBaku::find(10);
if ($cabeHijau) {
    echo "- Harga Saat Ini: Rp " . number_format($cabeHijau->harga_satuan, 0, ',', '.') . "/kg" . PHP_EOL;
    echo "- Harga Rata-Rata: Rp " . number_format($cabeHijau->harga_rata_rata, 0, ',', '.') . "/kg" . PHP_EOL;
    echo "- Stok: " . number_format($cabeHijau->stok, 2, ',', '.') . " kg" . PHP_EOL;
    
    // Manual calculation berdasarkan pembelian 5kg @ Rp 30.000
    $jumlahPembelian = 5;
    $hargaPembelian = 30000;
    $stokLama = 5; // Asumi stok awal 5kg
    $hargaRataRataLama = 6000; // Harga rata-rata lama
    
    $stokBaru = $stokLama + $jumlahPembelian; // 5 + 5 = 10kg
    $totalHargaLama = $hargaRataRataLama * $stokLama; // 6000 * 5 = 30.000
    $totalHargaBaru = $hargaPembelian * $jumlahPembelian; // 30.000 * 5 = 150.000
    $hargaRataRataBaru = ($totalHargaLama + $totalHargaBaru) / $stokBaru; // (30.000 + 150.000) / 10 = 18.000
    
    echo "- Perhitungan Manual:" . PHP_EOL;
    echo "  Stok Lama: " . number_format($stokLama, 2, ',', '.') . " kg" . PHP_EOL;
    echo "  Harga Lama: Rp " . number_format($hargaRataRataLama, 0, ',', '.') . "/kg" . PHP_EOL;
    echo "  Pembelian: " . number_format($jumlahPembelian, 2, ',', '.') . " kg @ Rp " . number_format($hargaPembelian, 0, ',', '.') . "/kg" . PHP_EOL;
    echo "  Total Harga Lama: Rp " . number_format($totalHargaLama, 0, ',', '.') . PHP_EOL;
    echo "  Total Harga Baru: Rp " . number_format($totalHargaBaru, 0, ',', '.') . PHP_EOL;
    echo "  Stok Baru: " . number_format($stokBaru, 2, ',', '.') . " kg" . PHP_EOL;
    echo "  Harga Rata-Rata Baru: Rp " . number_format($hargaRataRataBaru, 0, ',', '.') . "/kg" . PHP_EOL;
    
    // Update database
    $cabeHijau->update([
        'harga_rata_rata' => $hargaRataRataBaru,
        'harga_satuan' => $hargaRataRataBaru,
        'stok' => $stokBaru
    ]);
    
    echo "- Updated! Harga rata-rata sekarang: Rp " . number_format($cabeHijau->fresh()->harga_rata_rata, 0, ',', '.') . "/kg" . PHP_EOL;
}

// Fix Cabe Merah (ID: 11)
echo PHP_EOL . "Fix Cabe Merah (ID: 11):" . PHP_EOL;
$cabeMerah = \App\Models\BahanBaku::find(11);
if ($cabeMerah) {
    echo "- Harga Saat Ini: Rp " . number_format($cabeMerah->harga_satuan, 0, ',', '.') . "/kg" . PHP_EOL;
    echo "- Harga Rata-Rata: Rp " . number_format($cabeMerah->harga_rata_rata, 0, ',', '.') . "/kg" . PHP_EOL;
    echo "- Stok: " . number_format($cabeMerah->stok, 2, ',', '.') . " kg" . PHP_EOL;
    
    // Manual calculation berdasarkan pembelian 3kg @ Rp 30.000
    $jumlahPembelian = 3;
    $hargaPembelian = 30000;
    $stokLama = 5; // Asumi stok awal 5kg
    $hargaRataRataLama = 6000; // Harga rata-rata lama
    
    $stokBaru = $stokLama + $jumlahPembelian; // 5 + 3 = 8kg
    $totalHargaLama = $hargaRataRataLama * $stokLama; // 6000 * 5 = 30.000
    $totalHargaBaru = $hargaPembelian * $jumlahPembelian; // 30.000 * 3 = 90.000
    $hargaRataRataBaru = ($totalHargaLama + $totalHargaBaru) / $stokBaru; // (30.000 + 90.000) / 8 = 15.000
    
    echo "- Perhitungan Manual:" . PHP_EOL;
    echo "  Stok Lama: " . number_format($stokLama, 2, ',', '.') . " kg" . PHP_EOL;
    echo "  Harga Lama: Rp " . number_format($hargaRataRataLama, 0, ',', '.') . "/kg" . PHP_EOL;
    echo "  Pembelian: " . number_format($jumlahPembelian, 2, ',', '.') . " kg @ Rp " . number_format($hargaPembelian, 0, ',', '.') . "/kg" . PHP_EOL;
    echo "  Total Harga Lama: Rp " . number_format($totalHargaLama, 0, ',', '.') . PHP_EOL;
    echo "  Total Harga Baru: Rp " . number_format($totalHargaBaru, 0, ',', '.') . PHP_EOL;
    echo "  Stok Baru: " . number_format($stokBaru, 2, ',', '.') . " kg" . PHP_EOL;
    echo "  Harga Rata-Rata Baru: Rp " . number_format($hargaRataRataBaru, 0, ',', '.') . "/kg" . PHP_EOL;
    
    // Update database
    $cabeMerah->update([
        'harga_rata_rata' => $hargaRataRataBaru,
        'harga_satuan' => $hargaRataRataBaru,
        'stok' => $stokBaru
    ]);
    
    echo "- Updated! Harga rata-rata sekarang: Rp " . number_format($cabeMerah->fresh()->harga_rata_rata, 0, ',', '.') . "/kg" . PHP_EOL;
}

echo PHP_EOL . "=== Fix Selesai ===" . PHP_EOL;
