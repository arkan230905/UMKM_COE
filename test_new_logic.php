<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST NEW PRODUCTION LOGIC ===\n";

$produk = \App\Models\Produk::find(1);
$qtyProd = 3;

echo "Produk: " . $produk->nama_produk . "\n";
echo "Qty Produksi: " . $qtyProd . "\n\n";

// Logic baru dari ProduksiController
$bom = \App\Models\Bom::where('produk_id', $produk->id)->first();
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();

// Total Bahan Baku dari BOM (per unit)
$totalBahanPerUnit = $bom ? $bom->details->sum('total_harga') : 0;
$totalBahan = $totalBahanPerUnit * $qtyProd;

// Total BTKL dan BOP dari BOM Job Costing (per unit)
$totalBTKLPerUnit = 0;
$totalBOPPerUnit = 0;

if ($bomJobCosting) {
    // Hitung total BTKL dari BomJobBtkl
    $btklDetails = \App\Models\BomJobBtkl::where('bom_job_costing_id', $bomJobCosting->id)->get();
    $totalBTKLPerUnit = $btklDetails->sum('total_biaya');
    
    // Ambil total BOP dari BomJobCosting
    $totalBOPPerUnit = $bomJobCosting->total_bop;
}

$totalBTKL = $totalBTKLPerUnit * $qtyProd;
$totalBOP = $totalBOPPerUnit * $qtyProd;
$totalBiaya = $totalBahan + $totalBTKL + $totalBOP;

echo "--- PERHITUNGAN PRODUKSI ---\n";
echo "Total Bahan Per Unit: Rp " . number_format($totalBahanPerUnit, 0, ',', '.') . "\n";
echo "Total BTKL Per Unit: Rp " . number_format($totalBTKLPerUnit, 0, ',', '.') . "\n";
echo "Total BOP Per Unit: Rp " . number_format($totalBOPPerUnit, 0, ',', '.') . "\n";
echo "Total BOM Per Unit: Rp " . number_format($totalBahanPerUnit + $totalBTKLPerUnit + $totalBOPPerUnit, 0, ',', '.') . "\n\n";

echo "--- TOTAL UNTUK QTY " . $qtyProd . " ---\n";
echo "Total Bahan: Rp " . number_format($totalBahan, 0, ',', '.') . "\n";
echo "Total BTKL: Rp " . number_format($totalBTKL, 0, ',', '.') . "\n";
echo "Total BOP: Rp " . number_format($totalBOP, 0, ',', '.') . "\n";
echo "Total Biaya: Rp " . number_format($totalBiaya, 0, ',', '.') . "\n";
echo "Unit Cost: Rp " . number_format($totalBiaya / $qtyProd, 0, ',', '.') . "\n";

echo "\n--- VERIFIKASI ---\n";
echo "Apakah Total Bahan = Total BBB BOM × qty? " . ($totalBahan == ($totalBahanPerUnit * $qtyProd) ? "YA" : "TIDAK") . "\n";
echo "Apakah Total BTKL = Total BTKL BOM × qty? " . ($totalBTKL == ($totalBTKLPerUnit * $qtyProd) ? "YA" : "TIDAK") . "\n";
echo "Apakah Total BOP = Total BOP BOM × qty? " . ($totalBOP == ($totalBOPPerUnit * $qtyProd) ? "YA" : "TIDAK") . "\n";
echo "Apakah Total Biaya = (Total Bahan + Total BTKL + Total BOP)? " . ($totalBiaya == ($totalBahan + $totalBTKL + $totalBOP) ? "YA" : "TIDAK") . "\n";
