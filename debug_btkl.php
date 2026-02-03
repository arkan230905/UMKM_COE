<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG BTKL DI BOM INDEX VS DATABASE ===\n";

$produk = \App\Models\Produk::find(1);
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();

echo "Produk: " . $produk->nama_produk . "\n";
echo "BomJobCosting ID: " . ($bomJobCosting ? $bomJobCosting->id : 'N/A') . "\n\n";

// 1. Cek BomJobBtkl details
echo "--- BOM JOB BTKL DETAILS ---\n";
if ($bomJobCosting) {
    $btklDetails = \App\Models\BomJobBtkl::where('bom_job_costing_id', $bomJobCosting->id)->get();
    foreach ($btklDetails as $detail) {
        echo "Detail ID: " . $detail->id . "\n";
        echo "Proses: " . ($detail->prosesProduksi->nama_proses ?? 'N/A') . "\n";
        echo "Jumlah Tenaga: '" . $detail->jumlah_tenaga . "'\n";
        echo "Tarif per Jam: Rp " . number_format($detail->tarif_per_jam, 0, ',', '.') . "\n";
        echo "Waktu (Jam): '" . $detail->waktu_jam . "'\n";
        echo "Total Biaya: Rp " . number_format($detail->total_biaya, 0, ',', '.') . "\n";
        echo "---\n";
    }
    
    $totalBTKLFromDB = $btklDetails->sum('total_biaya');
    echo "Total BTKL dari Database: Rp " . number_format($totalBTKLFromDB, 0, ',', '.') . "\n";
} else {
    echo "Tidak ada BomJobCosting\n";
}

// 2. Cek logic BomController@index
echo "\n--- LOGIC BOM CONTROLLER INDEX ---\n";
// Ini adalah logic yang menghasilkan Rp 2.000 di halaman BOM
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

echo "Biaya Bahan (BomJobBahanPendukung): Rp " . number_format($totalBiayaBahan, 0, ',', '.') . "\n";
echo "Biaya BTKL (BomJobBtkl): Rp " . number_format($totalBTKL, 0, ',', '.') . "\n";
echo "Biaya BOP (BomJobCosting): Rp " . number_format($totalBOP, 0, ',', '.') . "\n";
echo "Total BOM: Rp " . number_format($totalBiayaBahan + $totalBTKL + $totalBOP, 0, ',', '.') . "\n";

// 3. Cek apakah ada data lain yang mempengaruhi
echo "\n--- CEK DATA LAIN ---\n";
echo "Apakah ada perhitungan BTKL dari sumber lain?\n";
echo "Cek BomJobCosting fields:\n";
if ($bomJobCosting) {
    echo "- total_bop: " . $bomJobCosting->total_bop . "\n";
    echo "- total_btkl (field): " . ($bomJobCosting->total_btkl ?? 'N/A') . "\n";
    echo "- created_at: " . $bomJobCosting->created_at . "\n";
    echo "- updated_at: " . $bomJobCosting->updated_at . "\n";
}
