<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKING DEBUG VALUES ===\n\n";

// Test the exact same logic as in the view
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
    
    echo "DEBUG VALUES (what should appear in view):\n";
    echo "komponenBop count: " . count($komponenBop) . "\n";
    
    if (!empty($komponenBop)) {
        foreach ($komponenBop as $i => $k) {
            echo "Component " . ($i+1) . " = " . $k['component'] . " -> " . $k['rate_per_produk'] . "\n";
        }
    }
    
    echo "totalBopPerProduk: " . $totalBopPerProduk . "\n";
    
    echo "\nExpected DEBUG output in view:\n";
    echo "- Yellow row: 'DEBUG: komponenBop count = 2'\n";
    echo "- Orange rows: 'DEBUG: Component 1 = Gas / BBM -> 67', 'DEBUG: Component 2 = Air & Kebersihan -> 28'\n";
    echo "- Red row: 'DEBUG: totalBopPerProduk = 95.00'\n";
    
} else {
    echo "BOP record not found!\n";
}

echo "\nPlease refresh the detail BOP page and tell me what debug values you see.\n";
