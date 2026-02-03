<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK SELURUH NOMINAL BOM ===\n";

$produk = \App\Models\Produk::find(1);

// 1. Cek Total Bahan Baku dari BOM Details
echo "--- BAHAN BAKU (BBB) ---\n";
$bom = \App\Models\Bom::where('produk_id', $produk->id)->with('details.bahanBaku')->first();
$totalBBB = 0;
if ($bom) {
    foreach ($bom->details as $detail) {
        $subtotal = $detail->total_harga;
        $totalBBB += $subtotal;
        echo $detail->bahanBaku->nama_bahan . ": Rp " . number_format($subtotal, 0, ',', '.') . "\n";
    }
}
echo "Total BBB: Rp " . number_format($totalBBB, 0, ',', '.') . "\n";

// 2. Cek Total BTKL dari BomJobBtkl
echo "\n--- BTKL (Biaya Tenaga Kerja Langsung) ---\n";
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
$totalBTKL = 0;
if ($bomJobCosting) {
    $btklDetails = \App\Models\BomJobBtkl::where('bom_job_costing_id', $bomJobCosting->id)->get();
    foreach ($btklDetails as $detail) {
        $totalBiaya = $detail->total_biaya;
        $totalBTKL += $totalBiaya;
        echo ($detail->prosesProduksi->nama_proses ?? 'Proses') . ": Rp " . number_format($totalBiaya, 0, ',', '.') . "\n";
        echo "  - Jumlah Tenaga: " . $detail->jumlah_tenaga . "\n";
        echo "  - Tarif/Jam: Rp " . number_format($detail->tarif_per_jam, 0, ',', '.') . "\n";
        echo "  - Waktu (Jam): " . $detail->waktu_jam . "\n";
    }
}
echo "Total BTKL: Rp " . number_format($totalBTKL, 0, ',', '.') . "\n";

// 3. Cek Total BOP dari BomJobCosting
echo "\n--- BOP (Biaya Overhead Pabrik) ---\n";
$totalBOP = 0;
if ($bomJobCosting) {
    $totalBOP = $bomJobCosting->total_bop;
    echo "Total BOP: Rp " . number_format($totalBOP, 0, ',', '.') . "\n";
    
    // Cek detail BOP
    $bopDetails = \App\Models\BomJobBop::where('bom_job_costing_id', $bomJobCosting->id)->get();
    foreach ($bopDetails as $detail) {
        echo "  - " . ($detail->komponenBop->nama_komponen ?? 'Komponen') . ": Rp " . number_format($detail->biaya, 0, ',', '.') . "\n";
    }
}

// 4. Total Keseluruhan BOM
echo "\n--- TOTAL KESULURUHAN BOM (PER UNIT) ---\n";
$totalBOM = $totalBBB + $totalBTKL + $totalBOP;
echo "Total Bahan Baku: Rp " . number_format($totalBBB, 0, ',', '.') . "\n";
echo "Total BTKL: Rp " . number_format($totalBTKL, 0, ',', '.') . "\n";
echo "Total BOP: Rp " . number_format($totalBOP, 0, ',', '.') . "\n";
echo "Total BOM: Rp " . number_format($totalBOM, 0, ',', '.') . "\n";

// 5. Simulasi untuk berbagai qty produksi
echo "\n--- SIMULASI PRODUKSI ---\n";
for ($qty = 1; $qty <= 5; $qty++) {
    $totalProduksi = $totalBOM * $qty;
    echo "Qty " . $qty . ": Rp " . number_format($totalProduksi, 0, ',', '.') . " (Unit: Rp " . number_format($totalBOM, 0, ',', '.') . ")\n";
}
