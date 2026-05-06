<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING VIEW WITHOUT FORMATTING ===\n\n";

// Simulate exactly what the controller does
$bopProses = \App\Models\BopProses::with('prosesProduksi')->find(2);

if ($bopProses) {
    // Simulate controller data preparation
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
    
    echo "View Variables (without formatting):\n";
    echo "totalBopPerProduk: " . $totalBopPerProduk . "\n";
    echo "totalBiayaPerProduk: " . $totalBiayaPerProduk . "\n";
    echo "komponenBop count: " . count($komponenBop) . "\n";
    
    echo "\nExpected View Output (without formatting):\n";
    echo "Komponen BOP:\n";
    foreach ($komponenBop as $index => $komponen) {
        echo "  " . ($index + 1) . ". " . $komponen['component'] . " - Rp " . $komponen['rate_per_produk'] . "\n";
    }
    
    echo "Total BOP / produk: Rp " . $totalBopPerProduk . "\n";
    echo "Biaya / produk: Rp " . $totalBiayaPerProduk . "\n";
    
    // Test the actual view rendering logic
    echo "\nTesting actual view rendering logic:\n";
    echo "Component rendering:\n";
    foreach ($komponenBop as $index => $komponen) {
        echo "  Rp {{ \$komponen['rate_per_produk'] ?? 0 }} -> Rp " . ($komponen['rate_per_produk'] ?? 0) . "\n";
    }
    
    echo "Total rendering:\n";
    echo "  Rp {{ \$totalBopPerProduk }} -> Rp " . $totalBopPerProduk . "\n";
    echo "  Rp {{ \$totalBiayaPerProduk }} -> Rp " . $totalBiayaPerProduk . "\n";
    
} else {
    echo "BOP record not found!\n";
}

echo "\n=== TEST COMPLETE ===\n";
