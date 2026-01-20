<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Harga Cabe ===" . PHP_EOL;

// Check Cabe Hijau (ID: 10)
echo PHP_EOL . "Cabe Hijau (ID: 10):" . PHP_EOL;
$cabeHijau = \App\Models\BahanBaku::find(10);
if ($cabeHijau) {
    echo "- Harga Satuan Saat Ini: Rp " . number_format($cabeHijau->harga_satuan, 0, ',', '.') . "/kg" . PHP_EOL;
    echo "- Harga Rata-Rata: Rp " . number_format($cabeHijau->harga_rata_rata, 0, ',', '.') . "/kg" . PHP_EOL;
    echo "- Stok: " . number_format($cabeHijau->stok, 2, ',', '.') . " kg" . PHP_EOL;
    
    // Check pembelian details
    $pembelianDetails = \App\Models\PembelianDetail::where('bahan_baku_id', 10)
        ->join('pembelians', 'pembelian_details.pembelian_id', '=', 'pembelians.id')
        ->select('pembelian_details.*', 'pembelians.tanggal')
        ->orderBy('pembelians.tanggal', 'desc')
        ->get();
    
    echo "- Riwayat Pembelian:" . PHP_EOL;
    foreach ($pembelianDetails as $detail) {
        echo "  * {$detail->tanggal} | {$detail->jumlah} {$detail->satuan} @ Rp " . number_format($detail->harga_satuan, 0, ',', '.') . " = Rp " . number_format($detail->subtotal, 0, ',', '.') . PHP_EOL;
    }
}

// Check Cabe Merah (ID: 11)
echo PHP_EOL . "Cabe Merah (ID: 11):" . PHP_EOL;
$cabeMerah = \App\Models\BahanBaku::find(11);
if ($cabeMerah) {
    echo "- Harga Satuan Saat Ini: Rp " . number_format($cabeMerah->harga_satuan, 0, ',', '.') . "/kg" . PHP_EOL;
    echo "- Harga Rata-Rata: Rp " . number_format($cabeMerah->harga_rata_rata, 0, ',', '.') . "/kg" . PHP_EOL;
    echo "- Stok: " . number_format($cabeMerah->stok, 2, ',', '.') . " kg" . PHP_EOL;
    
    // Check pembelian details
    $pembelianDetails = \App\Models\PembelianDetail::where('bahan_baku_id', 11)
        ->join('pembelians', 'pembelian_details.pembelian_id', '=', 'pembelians.id')
        ->select('pembelian_details.*', 'pembelians.tanggal')
        ->orderBy('pembelians.tanggal', 'desc')
        ->get();
    
    echo "- Riwayat Pembelian:" . PHP_EOL;
    foreach ($pembelianDetails as $detail) {
        echo "  * {$detail->tanggal} | {$detail->jumlah} {$detail->satuan} @ Rp " . number_format($detail->harga_satuan, 0, ',', '.') . " = Rp " . number_format($detail->subtotal, 0, ',', '.') . PHP_EOL;
    }
}

// Manual calculation check
echo PHP_EOL . "Manual Calculation Check:" . PHP_EOL;

// Cabe Hijau - misal pembelian 5kg @ Rp 30.000
$jumlahHijau = 5;
$hargaHijau = 30000;
$stokHijau = $cabeHijau->stok ?? 0;
$hargaRataRataLamaHijau = $cabeHijau->harga_rata_rata ?? 0;

$stokBaruHijau = $stokHijau + $jumlahHijau;
$hargaRataRataBaruHijau = ($hargaRataRataLamaHijau * $stokHijau + $hargaHijau * $jumlahHijau) / $stokBaruHijau;

echo "- Cabe Hijau Manual:" . PHP_EOL;
echo "  Stok Lama: " . number_format($stokHijau, 2, ',', '.') . " kg" . PHP_EOL;
echo "  Harga Lama: Rp " . number_format($hargaRataRataLamaHijau, 0, ',', '.') . "/kg" . PHP_EOL;
echo "  Pembelian: " . number_format($jumlahHijau, 2, ',', '.') . " kg @ Rp " . number_format($hargaHijau, 0, ',', '.') . "/kg" . PHP_EOL;
echo "  Stok Baru: " . number_format($stokBaruHijau, 2, ',', '.') . " kg" . PHP_EOL;
echo "  Harga Rata-Rata Baru: Rp " . number_format($hargaRataRataBaruHijau, 0, ',', '.') . "/kg" . PHP_EOL;

// Cabe Merah - misal pembelian 3kg @ Rp 30.000
$jumlahMerah = 3;
$hargaMerah = 30000;
$stokMerah = $cabeMerah->stok ?? 0;
$hargaRataRataLamaMerah = $cabeMerah->harga_rata_rata ?? 0;

$stokBaruMerah = $stokMerah + $jumlahMerah;
$hargaRataRataBaruMerah = ($hargaRataRataLamaMerah * $stokMerah + $hargaMerah * $jumlahMerah) / $stokBaruMerah;

echo PHP_EOL . "- Cabe Merah Manual:" . PHP_EOL;
echo "  Stok Lama: " . number_format($stokMerah, 2, ',', '.') . " kg" . PHP_EOL;
echo "  Harga Lama: Rp " . number_format($hargaRataRataLamaMerah, 0, ',', '.') . "/kg" . PHP_EOL;
echo "  Pembelian: " . number_format($jumlahMerah, 2, ',', '.') . " kg @ Rp " . number_format($hargaMerah, 0, ',', '.') . "/kg" . PHP_EOL;
echo "  Stok Baru: " . number_format($stokBaruMerah, 2, ',', '.') . " kg" . PHP_EOL;
echo "  Harga Rata-Rata Baru: Rp " . number_format($hargaRataRataBaruMerah, 0, ',', '.') . "/kg" . PHP_EOL;
