<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK DETAIL BTKL PRODUK AYAM KETUMBAR ===" . PHP_EOL;

// 1. Cek BomJobCosting untuk Ayam Ketumbar (ID 1)
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 1)->first();
if ($bomJobCosting) {
    echo "BomJobCosting ID: {$bomJobCosting->id}" . PHP_EOL;
    echo "Produk: {$bomJobCosting->produk->nama_produk}" . PHP_EOL;
    echo "Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
    
    // 2. Cek detail BTKL
    echo PHP_EOL . "Detail BTKL:" . PHP_EOL;
    $btklDetails = \App\Models\BomJobBTKL::with('btkl')
        ->where('bom_job_costing_id', $bomJobCosting->id)
        ->get();
    
    if ($btklDetails->isEmpty()) {
        echo "Tidak ada data BTKL!" . PHP_EOL;
    } else {
        foreach ($btklDetails as $index => $btkl) {
            echo ($index + 1) . ". ";
            echo "Kode: " . ($btkl->btkl->kode ?? 'N/A') . PHP_EOL;
            echo "   Nama Proses: " . ($btkl->nama_proses ?? 'N/A') . PHP_EOL;
            echo "   Jabatan: " . ($btkl->btkl->nama ?? 'N/A') . PHP_EOL;
            echo "   Jumlah: " . ($btkl->durasi_jam ?? 0) . " jam" . PHP_EOL;
            echo "   Tarif: Rp " . number_format($btkl->tarif_per_jam, 2, ',', '.') . "/jam" . PHP_EOL;
            echo "   Kapasitas: " . ($btkl->kapasitas_per_jam ?? 0) . " unit/jam" . PHP_EOL;
            echo "   Subtotal: Rp " . number_format($btkl->subtotal, 2, ',', '.') . PHP_EOL;
            echo "   Biaya per unit: ";
            
            // Hitung biaya per unit
            $kapasitas = $btkl->kapasitas_per_jam ?? 1;
            $biayaPerUnit = $kapasitas > 0 ? $btkl->tarif_per_jam / $kapasitas : 0;
            echo "Rp " . number_format($biayaPerUnit, 2, ',', '.') . PHP_EOL;
            echo PHP_EOL;
        }
        
        echo "Total Biaya Per Produk: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
    }
} else {
    echo "BomJobCosting untuk Ayam Ketumbar tidak ditemukan!" . PHP_EOL;
}

// 3. Cek master data BTKL yang tersedia
echo PHP_EOL . "MASTER DATA BTKL:" . PHP_EOL;
$masterBTKL = \App\Models\Btkl::all();
foreach ($masterBTKL as $btkl) {
    echo "- Kode: " . ($btkl->kode ?? 'N/A') . PHP_EOL;
    echo "  Nama: " . ($btkl->nama ?? 'N/A') . PHP_EOL;
    echo "  Tarif: Rp " . number_format($btkl->tarif_per_jam ?? 0, 2, ',', '.') . "/jam" . PHP_EOL;
    echo "  Kapasitas: " . ($btkl->kapasitas_per_jam ?? 0) . " unit/jam" . PHP_EOL;
    echo PHP_EOL;
}
