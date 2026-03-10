<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK DATA BTKL & BOP SEMUA PRODUK ===" . PHP_EOL;

// 1. Cek semua BomJobCosting
$bomJobCostings = \App\Models\BomJobCosting::with('produk')->get();
echo "Total BomJobCosting: " . $bomJobCostings->count() . PHP_EOL . PHP_EOL;

foreach ($bomJobCostings as $bom) {
    echo "Produk: {$bom->produk->nama_produk}" . PHP_EOL;
    echo "- Total BTKL: Rp " . number_format($bom->total_btkl, 2, ',', '.') . PHP_EOL;
    echo "- Total BOP: Rp " . number_format($bom->total_bop, 2, ',', '.') . PHP_EOL;
    echo "- Target BTKL: Rp 2.040" . PHP_EOL;
    echo "- Target BOP: Rp 3.190" . PHP_EOL;
    
    // Cek detail BTKL
    $btklDetails = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bom->id)->get();
    echo "  Detail BTKL (" . $btklDetails->count() . "):" . PHP_EOL;
    foreach ($btklDetails as $btkl) {
        echo "    - " . ($btkl->btkl->nama ?? 'Unknown') . ": Rp " . 
             number_format($btkl->subtotal, 2, ',', '.') . PHP_EOL;
    }
    
    // Cek detail BOP
    $bopDetails = \App\Models\BomJobBOP::where('bom_job_costing_id', $bom->id)->get();
    echo "  Detail BOP (" . $bopDetails->count() . "):" . PHP_EOL;
    foreach ($bopDetails as $bop) {
        echo "    - " . ($bop->bop->nama ?? 'Unknown') . ": Rp " . 
             number_format($bop->subtotal, 2, ',', '.') . PHP_EOL;
    }
    
    echo PHP_EOL . str_repeat("-", 50) . PHP_EOL;
}

// 2. Cek master data BTKL dan BOP yang tersedia
echo PHP_EOL . "MASTER DATA YANG TERSEDIA:" . PHP_EOL;

$btkls = \App\Models\Btkl::all();
echo "BTKL tersedia (" . $btkls->count() . "):" . PHP_EOL;
foreach ($btkls as $btkl) {
    echo "- {$btkl->nama}: Rp " . number_format($btkl->tarif_per_jam ?? 0, 2, ',', '.') . "/jam" . PHP_EOL;
}

$bops = \App\Models\Bop::all();
echo PHP_EOL . "BOP tersedia (" . $bops->count() . "):" . PHP_EOL;
foreach ($bops as $bop) {
    echo "- {$bop->nama}: Rp " . number_format($bop->tarif_per_unit ?? 0, 2, ',', '.') . "/unit" . PHP_EOL;
}
