<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Production BOP calculation for production ID: 1\n";

// Get production data
$produksi = \App\Models\Produksi::find(1);

if (!$produksi) {
    echo "Production ID 1 not found\n";
    exit;
}

echo "\n=== Production Info ===\n";
echo "Produk: " . $produksi->produk->nama_produk . "\n";
echo "Qty: " . $produksi->qty_produksi . "\n";
echo "Status: " . $produksi->status . "\n";
echo "Total BTKL: Rp " . number_format($produksi->total_btkl, 0, ',', '.') . "\n";
echo "Total BOP: Rp " . number_format($produksi->total_bop, 0, ',', '.') . "\n";

// Check production processes
echo "\n=== Production Processes ===\n";
$processes = \App\Models\ProduksiProses::where('produksi_id', 1)->get();
echo "Total processes: " . $processes->count() . "\n";

foreach ($processes as $process) {
    echo "\nProcess ID: " . $process->id . "\n";
    echo "  Name: " . $process->nama_proses . "\n";
    echo "  Order: " . $process->urutan . "\n";
    echo "  Status: " . $process->status . "\n";
    echo "  BTKL: Rp " . number_format($process->biaya_btkl, 0, ',', '.') . "\n";
    echo "  BOP: Rp " . number_format($process->biaya_bop, 0, ',', '.') . "\n";
    echo "  Total: Rp " . number_format($process->total_biaya_proses, 0, ',', '.') . "\n";
}

// Check BOP details
echo "\n=== BOP Details ===\n";
$bopDetails = \App\Models\ProduksiBopDetail::where('produksi_id', 1)->get();
echo "Total BOP details: " . $bopDetails->count() . "\n";

foreach ($bopDetails as $bopDetail) {
    echo "\nBOP Detail ID: " . $bopDetail->id . "\n";
    echo "  Process: " . $bopDetail->nama_proses . "\n";
    echo "  Component: " . $bopDetail->nama_komponen . "\n";
    echo "  Rate/Unit: Rp " . number_format($bopDetail->rate_per_unit, 0, ',', '.') . "\n";
    echo "  Total: Rp " . number_format($bopDetail->total, 0, ',', '.') . "\n";
}

// Check BomJobCosting
echo "\n=== BOM Job Costing ===\n";
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produksi->produk_id)->first();
if ($bomJobCosting) {
    echo "BOM Job Costing ID: " . $bomJobCosting->id . "\n";
    
    // Check BomJobBOP
    $bomJobBOPs = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get();
    echo "BomJobBOP count: " . $bomJobBOPs->count() . "\n";
    
    foreach ($bomJobBOPs as $bomJobBOP) {
        echo "  " . $bomJobBOP->nama_bop . ": Rp " . number_format($bomJobBOP->subtotal, 0, ',', '.') . "\n";
    }
} else {
    echo "No BOM Job Costing found for product\n";
}

echo "\nProduction BOP calculation check completed!\n";
