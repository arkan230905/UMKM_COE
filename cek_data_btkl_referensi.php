<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DATA BTKL UNTUK REFERENSI ===" . PHP_EOL;

// Cek master BTKL
echo "1. MASTER BTKL:" . PHP_EOL;
echo "================" . PHP_EOL;

$masterBtkls = \App\Models\Btkl::orderBy('nama_btkl')->get();
foreach ($masterBtkls as $btkl) {
    echo "- {$btkl->nama_btkl}: Rp " . number_format($btkl->tarif, 2, ',', '.') . "/jam" . PHP_EOL;
}

// Cek BomJobBTKL untuk produk
echo PHP_EOL . "2. DETAIL BTKL PER PRODUK:" . PHP_EOL;
echo "===========================" . PHP_EOL;

$bomJobBtkls = \App\Models\BomJobBTKL::with(['bomJobCosting.produk', 'btkl'])->get();
foreach ($bomJobBtkls as $jobBtkl) {
    $produk = $jobBtkl->bomJobCosting->produk ?? null;
    if ($produk) {
        $total = $jobBtkl->durasi_jam * $jobBtkl->tarif_per_jam;
        echo "Produk: {$produk->nama_produk}" . PHP_EOL;
        echo "  Proses: {$jobBtkl->nama_proses}" . PHP_EOL;
        echo "  Durasi: {$jobBtkl->durasi_jam} jam" . PHP_EOL;
        echo "  Tarif: Rp " . number_format($jobBtkl->tarif_per_jam, 2, ',', '.') . "/jam" . PHP_EOL;
        echo "  Total: Rp " . number_format($total, 2, ',', '.') . PHP_EOL;
        echo PHP_EOL;
    }
}

// Cek total BTKL per produk
echo "3. TOTAL BTKL PER PRODUK:" . PHP_EOL;
echo "========================" . PHP_EOL;

$produks = \App\Models\Produk::with(['bomJobCosting.detailBTKL'])->get();
foreach ($produks as $produk) {
    if ($produk->bomJobCosting && $produk->bomJobCosting->detailBTKL) {
        $totalBtkl = $produk->bomJobCosting->detailBTKL->sum(function($item) {
            return $item->durasi_jam * $item->tarif_per_jam;
        });
        
        echo "Produk: {$produk->nama_produk}" . PHP_EOL;
        echo "  Total BTKL: Rp " . number_format($totalBtkl, 2, ',', '.') . PHP_EOL;
        echo "  Jumlah Proses: " . $produk->bomJobCosting->detailBTKL->count() . PHP_EOL;
        
        if ($produk->bomJobCosting->total_btkl != $totalBtkl) {
            echo "  ⚠️  WARNING: Total tidak cocok!" . PHP_EOL;
            echo "    Database: Rp " . number_format($produk->bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
            echo "    Hitungan: Rp " . number_format($totalBtkl, 2, ',', '.') . PHP_EOL;
        }
        echo PHP_EOL;
    }
}

echo "✅ Selesai!" . PHP_EOL;
