<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG KETIDAK KONSISTENSI DATA ===\n";

// 1. Cek data yang ditampilkan di halaman BOM
echo "--- DATA YANG DITAMPILKAN DI HALAMAN BOM ---\n";
$produk = \App\Models\Produk::find(1);
echo "Produk: " . $produk->nama_produk . "\n";

// Logic dari BomController@index (yang ditampilkan di halaman BOM)
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
$totalBiayaBahan = 0;
$totalBTKL = 0;
$btklCount = 0;
$totalBOP = $bomJobCosting->total_bop ?? 0;

if ($bomJobCosting) {
    // Hitung total biaya bahan dari BomJobBahanPendukung
    $bahanPendukungDetails = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->get();
    foreach ($bahanPendukungDetails as $detail) {
        $totalBiayaBahan += $detail->subtotal;
    }
    
    // Hitung total BTKL dari BomJobBtkl
    $btklDetails = \App\Models\BomJobBtkl::where('bom_job_costing_id', $bomJobCosting->id)->get();
    $totalBTKL = $btklDetails->sum('total_biaya');
    $btklCount = $btklDetails->count();
}

echo "Biaya Bahan (dari BomJobBahanPendukung): Rp " . number_format($totalBiayaBahan, 0, ',', '.') . "\n";
echo "Biaya BTKL (dari BomJobBtkl): Rp " . number_format($totalBTKL, 0, ',', '.') . "\n";
echo "Biaya BOP (dari BomJobCosting): Rp " . number_format($totalBOP, 0, ',', '.') . "\n";
echo "Total BOM: Rp " . number_format($totalBiayaBahan + $totalBTKL + $totalBOP, 0, ',', '.') . "\n\n";

// 2. Cek data yang digunakan di ProduksiController
echo "--- DATA YANG DIGUNAKAN DI PRODUKSI CONTROLLER ---\n";
$bom = \App\Models\Bom::where('produk_id', $produk->id)->first();
$totalBBB = $bom ? $bom->details->sum('total_harga') : 0;
echo "Total Bahan Baku (dari Bom.details): Rp " . number_format($totalBBB, 0, ',', '.') . "\n";

echo "\n--- PERBANDINGAN ---\n";
echo "Biaya Bahan (BOM Index): Rp " . number_format($totalBiayaBahan, 0, ',', '.') . "\n";
echo "Total Bahan Baku (Produksi): Rp " . number_format($totalBBB, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format(abs($totalBiayaBahan - $totalBBB), 0, ',', '.') . "\n";

// 3. Cek detail masing-masing
echo "\n--- DETAIL BOM DETAILS ---\n";
if ($bom) {
    foreach ($bom->details as $detail) {
        echo "Detail: " . ($detail->bahanBaku->nama_bahan ?? 'N/A') . "\n";
        echo "  Jumlah: " . $detail->jumlah . "\n";
        echo "  Harga per Satuan: Rp " . number_format($detail->harga_per_satuan, 0, ',', '.') . "\n";
        echo "  Total Harga: Rp " . number_format($detail->total_harga, 0, ',', '.') . "\n";
    }
}

echo "\n--- DETAIL BOM JOB BAHAN PENDUKUNG ---\n";
if ($bomJobCosting) {
    $bahanPendukungDetails = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->get();
    foreach ($bahanPendukungDetails as $detail) {
        echo "Detail: " . ($detail->bahanPendukung->nama_bahan ?? 'N/A') . "\n";
        echo "  Jumlah: " . $detail->jumlah . "\n";
        echo "  Harga Satuan: Rp " . number_format($detail->harga_satuan, 0, ',', '.') . "\n";
        echo "  Subtotal: Rp " . number_format($detail->subtotal, 0, ',', '.') . "\n";
    }
}
