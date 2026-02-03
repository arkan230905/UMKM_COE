<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check BOM data for produk id 1
echo "=== BOM DATA FOR PRODUK ID 1 ===\n";
$bom = \App\Models\Bom::where('produk_id', 1)->first();

if ($bom) {
    echo "BOM ID: " . $bom->id . "\n";
    echo "Total Biaya: " . $bom->total_biaya . "\n";
    echo "Total BBB: " . $bom->total_bbb . "\n";
    echo "Total HPP: " . $bom->total_hpp . "\n";
    echo "Total BTKL: " . ($bom->total_btkl ?? 'N/A') . "\n";
    echo "Total BOP: " . ($bom->total_bop ?? 'N/A') . "\n";
} else {
    echo "BOM tidak ditemukan\n";
}

// Check BomJobCosting
echo "\n=== BOM JOB COSTING DATA ===\n";
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 1)->first();

if ($bomJobCosting) {
    echo "BomJobCosting ID: " . $bomJobCosting->id . "\n";
    echo "Total BOP: " . $bomJobCosting->total_bop . "\n";
    
    // Check BTKL details
    echo "\n--- BTKL Details ---\n";
    $btklDetails = \App\Models\BomJobBtkl::where('bom_job_costing_id', $bomJobCosting->id)->get();
    foreach ($btklDetails as $detail) {
        echo "Proses: " . ($detail->prosesProduksi->nama_proses ?? 'N/A') . "\n";
        echo "Total Biaya: " . $detail->total_biaya . "\n";
    }
    
    // Check BOP details
    echo "\n--- BOP Details ---\n";
    $bopDetails = \App\Models\BomJobBop::where('bom_job_costing_id', $bomJobCosting->id)->get();
    foreach ($bopDetails as $detail) {
        echo "Komponen: " . ($detail->komponenBop->nama_komponen ?? 'N/A') . "\n";
        echo "Biaya: " . $detail->biaya . "\n";
    }
} else {
    echo "BomJobCosting tidak ditemukan\n";
}

// Check Produk defaults
echo "\n=== PRODUK DEFAULTS ===\n";
$produk = \App\Models\Produk::find(1);
echo "BTKL Default: " . ($produk->btkl_default ?? 'N/A') . "\n";
echo "BOP Default: " . ($produk->bop_default ?? 'N/A') . "\n";
