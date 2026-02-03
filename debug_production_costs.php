<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test the new BTKL and BOP calculation logic
echo "=== TEST BTKL & BOP CALCULATION ===\n";

$produk = \App\Models\Produk::find(1);
$qtyProd = 3; // Same as the production example
$totalBahan = 12000; // From the production example

echo "Produk: " . $produk->nama_produk . "\n";
echo "Qty Produksi: " . $qtyProd . "\n";
echo "Total Bahan: Rp " . number_format($totalBahan, 0, ',', '.') . "\n";

// BTKL & BOP: ambil dari data BOM yang sudah dihitung
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
$totalBTKL = 0;
$totalBOP = 0;

if ($bomJobCosting) {
    echo "\n--- BOM Job Costing Found ---\n";
    echo "BOM Job Costing ID: " . $bomJobCosting->id . "\n";
    echo "Total BOP (per unit): Rp " . number_format($bomJobCosting->total_bop, 0, ',', '.') . "\n";
    
    // Hitung total BTKL dari BomJobBtkl
    $btklDetails = \App\Models\BomJobBtkl::where('bom_job_costing_id', $bomJobCosting->id)->get();
    echo "BTKL Details Count: " . $btklDetails->count() . "\n";
    
    $totalBTKLPerUnit = $btklDetails->sum('total_biaya');
    echo "Total BTKL (per unit): Rp " . number_format($totalBTKLPerUnit, 0, ',', '.') . "\n";
    
    $totalBTKL = $totalBTKLPerUnit * $qtyProd;
    $totalBOP = $bomJobCosting->total_bop * $qtyProd;
    
    echo "\n--- CALCULATED TOTALS ---\n";
    echo "Total BTKL (for " . $qtyProd . " units): Rp " . number_format($totalBTKL, 0, ',', '.') . "\n";
    echo "Total BOP (for " . $qtyProd . " units): Rp " . number_format($totalBOP, 0, ',', '.') . "\n";
} else {
    echo "\n--- NO BOM Job Costing Found - Using Fallback ---\n";
    $btklRate = (float) (config('app.btkl_percent') ?? 0.2);
    $bopRate  = (float) (config('app.bop_percent') ?? 0.1);
    echo "BTKL Rate: " . ($btklRate * 100) . "%\n";
    echo "BOP Rate: " . ($bopRate * 100) . "%\n";
    
    $totalBTKL = $totalBahan * $btklRate;
    $totalBOP  = $totalBahan * $bopRate;
    
    echo "Total BTKL: Rp " . number_format($totalBTKL, 0, ',', '.') . "\n";
    echo "Total BOP: Rp " . number_format($totalBOP, 0, ',', '.') . "\n";
}

$totalBiaya = $totalBahan + $totalBTKL + $totalBOP;
echo "\n--- FINAL TOTALS ---\n";
echo "Total Bahan: Rp " . number_format($totalBahan, 0, ',', '.') . "\n";
echo "Total BTKL: Rp " . number_format($totalBTKL, 0, ',', '.') . "\n";
echo "Total BOP: Rp " . number_format($totalBOP, 0, ',', '.') . "\n";
echo "Total Biaya: Rp " . number_format($totalBiaya, 0, ',', '.') . "\n";
echo "Unit Cost: Rp " . number_format($totalBiaya / $qtyProd, 0, ',', '.') . "\n";
