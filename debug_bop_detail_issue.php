<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUGGING BOP DETAIL VIEW ISSUE ===\n\n";

// 1. Check database data
echo "1. Database Data Check:\n";
$bopProses = \App\Models\BopProses::with('prosesProduksi')->find(2);

if ($bopProses) {
    echo "BOP ID: " . $bopProses->id . "\n";
    echo "komponen_bop (raw): " . json_encode($bopProses->komponen_bop) . "\n";
    echo "total_bop_per_produk: " . $bopProses->total_bop_per_produk . "\n";
    echo "total_biaya_per_produk: " . $bopProses->total_biaya_per_produk . "\n";
    echo "bop_per_unit: " . $bopProses->bop_per_unit . "\n";
    
    // Check if komponen_bop is array
    echo "komponen_bop is_array: " . (is_array($bopProses->komponen_bop) ? 'YES' : 'NO') . "\n";
    
    if (is_array($bopProses->komponen_bop)) {
        echo "Component count: " . count($bopProses->komponen_bop) . "\n";
        foreach ($bopProses->komponen_bop as $i => $komponen) {
            echo "  Component " . ($i+1) . ": " . $komponen['component'] . " -> " . $komponen['rate_per_produk'] . "\n";
        }
    }
} else {
    echo "BOP record not found!\n";
}

echo "\n2. Simulating Controller Logic:\n";

// Simulate the exact controller logic
if ($bopProses) {
    // Get matching BTKL data based on process name
    $btkl = \App\Models\Btkl::where('nama_btkl', $bopProses->prosesProduksi->nama_proses)->first();
    
    // Prepare data for view (same logic as in the view)
    $kapasitas = $bopProses->kapasitas_per_jam ?? 0;
    $totalBopPerProduk = $bopProses->total_bop_per_produk ?? 0;
    $totalBiayaPerProduk = $bopProses->total_biaya_per_produk ?? 0;
    $btklPerJam = $bopProses->prosesProduksi->tarif_btkl ?? 0;
    $btklPerProduk = $kapasitas > 0 ? $btklPerJam / $kapasitas : 0;
    
    // Get components from komponen_bop JSON
    $komponenBop = [];
    if ($bopProses->komponen_bop && is_array($bopProses->komponen_bop)) {
        $komponenBop = $bopProses->komponen_bop;
    }
    
    echo "Prepared variables:\n";
    echo "kapasitas: " . $kapasitas . "\n";
    echo "totalBopPerProduk: " . $totalBopPerProduk . "\n";
    echo "totalBiayaPerProduk: " . $totalBiayaPerProduk . "\n";
    echo "btklPerProduk: " . $btklPerProduk . "\n";
    echo "komponenBop count: " . count($komponenBop) . "\n";
    
    echo "\n3. Expected View Output:\n";
    echo "Komponen BOP:\n";
    if (!empty($komponenBop)) {
        foreach ($komponenBop as $index => $komponen) {
            echo "  " . ($index + 1) . ". " . $komponen['component'] . " - Rp " . $komponen['rate_per_produk'] . "\n";
        }
        echo "Total BOP / produk: Rp " . $totalBopPerProduk . "\n";
        echo "Biaya / produk: Rp " . number_format($totalBiayaPerProduk, 2) . "\n";
    } else {
        echo "  No components!\n";
    }
}

echo "\n4. Check if there are multiple BOP records:\n";
$allBop = \App\Models\BopProses::all();
echo "Total BOP records: " . $allBop->count() . "\n";
foreach ($allBop as $bp) {
    echo "ID: " . $bp->id . ", Proses: " . ($bp->prosesProduksi ? $bp->prosesProduksi->nama_proses : 'N/A') . ", Total BOP: " . $bp->total_bop_per_produk . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
