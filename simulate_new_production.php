<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SIMULASI PRODUCTION CALCULATION ===\n";

// Simulasi data produksi baru
$produk = \App\Models\Produk::find(1);
$qtyProd = 2; // 2 unit
$totalBahan = 8000; // 100g x 40.000 x 2 unit = 8000

echo "Produk: " . $produk->nama_produk . "\n";
echo "Qty Produksi: " . $qtyProd . "\n";
echo "Total Bahan: Rp " . number_format($totalBahan, 0, ',', '.') . "\n";

// Gunakan logic baru
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
$totalBTKL = 0;
$totalBOP = 0;

if ($bomJobCosting) {
    // Hitung total BTKL dari BomJobBtkl
    $btklDetails = \App\Models\BomJobBtkl::where('bom_job_costing_id', $bomJobCosting->id)->get();
    $totalBTKL = $btklDetails->sum('total_biaya') * $qtyProd;
    
    // Ambil total BOP dari BomJobCosting
    $totalBOP = $bomJobCosting->total_bop * $qtyProd;
    
    echo "\n--- HASIL PERHITUNGAN ---\n";
    echo "Total BTKL: Rp " . number_format($totalBTKL, 0, ',', '.') . "\n";
    echo "Total BOP: Rp " . number_format($totalBOP, 0, ',', '.') . "\n";
} else {
    echo "BOM Job Costing tidak ditemukan\n";
}

$totalBiaya = $totalBahan + $totalBTKL + $totalBOP;
echo "Total Biaya: Rp " . number_format($totalBiaya, 0, ',', '.') . "\n";
echo "Unit Cost: Rp " . number_format($totalBiaya / $qtyProd, 0, ',', '.') . "\n";

echo "\n=== PERBANDINGAN SEBELUM/SESUDAH ===\n";
echo "Sebelum (logic lama):\n";
echo "- Total Bahan: Rp " . number_format($totalBahan, 0, ',', '.') . "\n";
echo "- Total BTKL: Rp 0\n";
echo "- Total BOP: Rp 0\n";
echo "- Total Biaya: Rp " . number_format($totalBahan, 0, ',', '.') . "\n";

echo "\nSesudah (logic baru):\n";
echo "- Total Bahan: Rp " . number_format($totalBahan, 0, ',', '.') . "\n";
echo "- Total BTKL: Rp " . number_format($totalBTKL, 0, ',', '.') . "\n";
echo "- Total BOP: Rp " . number_format($totalBOP, 0, ',', '.') . "\n";
echo "- Total Biaya: Rp " . number_format($totalBiaya, 0, ',', '.') . "\n";
