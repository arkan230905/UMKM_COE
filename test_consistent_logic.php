<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST LOGIC BARU (KONSISTEN DENGAN BOM INDEX) ===\n";

$produk = \App\Models\Produk::find(1);
$qtyProd = 3;

echo "Produk: " . $produk->nama_produk . "\n";
echo "Qty Produksi: " . $qtyProd . "\n\n";

// Logic baru yang konsisten dengan BOM index
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();

// Total Biaya Bahan dari BomJobBahanPendukung (sama seperti di BOM index)
$totalBahanPerUnit = 0;
if ($bomJobCosting) {
    $bahanPendukungDetails = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->get();
    foreach ($bahanPendukungDetails as $detail) {
        $totalBahanPerUnit += $detail->subtotal;
    }
}
$totalBahan = $totalBahanPerUnit * $qtyProd;

// Total BTKL dan BOP dari BOM Job Costing (sama seperti di BOM index)
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

echo "--- PERHITUNGAN PRODUKSI BARU ---\n";
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

echo "\n--- VERIFIKASI KONSISTENSI DENGAN BOM INDEX ---\n";
echo "Data BOM Index:\n";
echo "- Biaya Bahan: Rp " . number_format($totalBahanPerUnit, 0, ',', '.') . "\n";
echo "- Biaya BTKL: Rp " . number_format($totalBTKLPerUnit, 0, ',', '.') . "\n";
echo "- Biaya BOP: Rp " . number_format($totalBOPPerUnit, 0, ',', '.') . "\n";
echo "- Total BOM: Rp " . number_format($totalBahanPerUnit + $totalBTKLPerUnit + $totalBOPPerUnit, 0, ',', '.') . "\n";

echo "\nData Produksi (Qty " . $qtyProd . "):\n";
echo "- Total Bahan: Rp " . number_format($totalBahan, 0, ',', '.') . " (Rp " . number_format($totalBahanPerUnit, 0, ',', '.') . " × " . $qtyProd . ")\n";
echo "- Total BTKL: Rp " . number_format($totalBTKL, 0, ',', '.') . " (Rp " . number_format($totalBTKLPerUnit, 0, ',', '.') . " × " . $qtyProd . ")\n";
echo "- Total BOP: Rp " . number_format($totalBOP, 0, ',', '.') . " (Rp " . number_format($totalBOPPerUnit, 0, ',', '.') . " × " . $qtyProd . ")\n";
echo "- Total Biaya: Rp " . number_format($totalBiaya, 0, ',', '.') . "\n";
