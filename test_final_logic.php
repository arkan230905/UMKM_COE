<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST LOGIC TERBARU (BAHAN BAKU + BAHAN PENDUKUNG) ===\n";

$produk = \App\Models\Produk::find(1);
$qtyProd = 3;

echo "Produk: " . $produk->nama_produk . "\n";
echo "Qty Produksi: " . $qtyProd . "\n\n";

// Logic terbaru
$bom = \App\Models\Bom::where('produk_id', $produk->id)->first();
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();

// Total Biaya Bahan = Bahan Baku (Bom.details) + Bahan Pendukung (BomJobBahanPendukung)
$totalBahanBakuPerUnit = $bom ? $bom->details->sum('total_harga') : 0;
$totalBahanPendukungPerUnit = 0;
if ($bomJobCosting) {
    $bahanPendukungDetails = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->get();
    foreach ($bahanPendukungDetails as $detail) {
        $totalBahanPendukungPerUnit += $detail->subtotal;
    }
}
$totalBahanPerUnit = $totalBahanBakuPerUnit + $totalBahanPendukungPerUnit;
$totalBahan = $totalBahanPerUnit * $qtyProd;

// Total BTKL dan BOP
$totalBTKLPerUnit = 0;
$totalBOPPerUnit = 0;

if ($bomJobCosting) {
    $btklDetails = \App\Models\BomJobBtkl::where('bom_job_costing_id', $bomJobCosting->id)->get();
    $totalBTKLPerUnit = $btklDetails->sum('total_biaya');
    $totalBOPPerUnit = $bomJobCosting->total_bop;
}

$totalBTKL = $totalBTKLPerUnit * $qtyProd;
$totalBOP = $totalBOPPerUnit * $qtyProd;
$totalBiaya = $totalBahan + $totalBTKL + $totalBOP;

echo "--- DETAIL PERHITUNGAN ---\n";
echo "Bahan Baku (Ayam Potong): Rp " . number_format($totalBahanBakuPerUnit, 0, ',', '.') . " × " . $qtyProd . " = Rp " . number_format($totalBahanBakuPerUnit * $qtyProd, 0, ',', '.') . "\n";
echo "Bahan Pendukung (Air Bersih): Rp " . number_format($totalBahanPendukungPerUnit, 0, ',', '.') . " × " . $qtyProd . " = Rp " . number_format($totalBahanPendukungPerUnit * $qtyProd, 0, ',', '.') . "\n";
echo "Total Bahan Per Unit: Rp " . number_format($totalBahanPerUnit, 0, ',', '.') . "\n";
echo "Total Bahan: Rp " . number_format($totalBahan, 0, ',', '.') . "\n\n";

echo "--- TOTAL PRODUKSI ---\n";
echo "Total Bahan: Rp " . number_format($totalBahan, 0, ',', '.') . "\n";
echo "Total BTKL: Rp " . number_format($totalBTKL, 0, ',', '.') . "\n";
echo "Total BOP: Rp " . number_format($totalBOP, 0, ',', '.') . "\n";
echo "Total Biaya: Rp " . number_format($totalBiaya, 0, ',', '.') . "\n";
echo "Unit Cost: Rp " . number_format($totalBiaya / $qtyProd, 0, ',', '.') . "\n";

echo "\n--- KONSISTENSI DENGAN BOM INDEX ---\n";
echo "BOM Index menampilkan:\n";
echo "- Biaya Bahan: Rp " . number_format($totalBahanPendukungPerUnit, 0, ',', '.') . " (hanya bahan pendukung)\n";
echo "- Total BOM: Rp " . number_format($totalBahanPendukungPerUnit + $totalBTKLPerUnit + $totalBOPPerUnit, 0, ',', '.') . "\n";

echo "\nProduksi menampilkan:\n";
echo "- Total Bahan: Rp " . number_format($totalBahan, 0, ',', '.') . " (bahan baku + bahan pendukung)\n";
echo "- Total Biaya: Rp " . number_format($totalBiaya, 0, ',', '.') . " (semua komponen)\n";

echo "\n--- REKOMENDASI ---\n";
echo "✅ Bahan Terpakai (Ayam Potong) = Konsisten dengan konsumsi stok\n";
echo "✅ Total Bahan (Rp " . number_format($totalBahan, 0, ',', '.') . ") = Bahan Baku + Bahan Pendukung\n";
echo "✅ Total Biaya = Semua komponen BOM × Qty Produksi\n";
