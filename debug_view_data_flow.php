<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUGGING VIEW DATA FLOW ===\n\n";

// Simulate exactly what the controller does
$bopProses = \App\Models\BopProses::with('prosesProduksi')->find(2);

if ($bopProses) {
    echo "1. Raw Database Data:\n";
    echo "   komponen_bop: " . json_encode($bopProses->komponen_bop) . "\n";
    echo "   total_bop_per_produk: " . $bopProses->total_bop_per_produk . "\n";
    echo "   total_biaya_per_produk: " . $bopProses->total_biaya_per_produk . "\n";
    
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
    
    echo "\n2. Controller Prepared Data:\n";
    echo "   \$totalBopPerProduk: " . $totalBopPerProduk . " (type: " . gettype($totalBopPerProduk) . ")\n";
    echo "   \$totalBiayaPerProduk: " . $totalBiayaPerProduk . " (type: " . gettype($totalBiayaPerProduk) . ")\n";
    echo "   \$komponenBop count: " . count($komponenBop) . "\n";
    
    echo "\n3. Component Details:\n";
    foreach ($komponenBop as $index => $komponen) {
        echo "   Component " . ($index + 1) . ":\n";
        echo "     - component: '" . $komponen['component'] . "'\n";
        echo "     - rate_per_produk: " . $komponen['rate_per_produk'] . " (type: " . gettype($komponen['rate_per_produk']) . ")\n";
        echo "     - description: '" . ($komponen['description'] ?? '') . "'\n";
    }
    
    // Test formatNumberClean function
    function formatNumberClean($number, $decimals = 2) {
        if ($number == 0) return '0';
        
        if ($number == floor($number)) {
            return number_format($number, 0, ',', '.');
        }
        
        $formatted = number_format($number, $decimals, ',', '.');
        $formatted = rtrim($formatted, '0');
        $formatted = rtrim($formatted, ',');
        
        return $formatted;
    }
    
    echo "\n4. Format Number Tests:\n";
    echo "   formatNumberClean(\$totalBopPerProduk): " . formatNumberClean($totalBopPerProduk) . "\n";
    echo "   formatNumberClean(\$totalBiayaPerProduk): " . formatNumberClean($totalBiayaPerProduk) . "\n";
    
    foreach ($komponenBop as $index => $komponen) {
        echo "   formatNumberClean(" . $komponen['rate_per_produk'] . "): " . formatNumberClean($komponen['rate_per_produk']) . "\n";
    }
    
    echo "\n5. Expected View Output:\n";
    echo "   Komponen BOP Table:\n";
    echo "   No | Komponen         | Rp / produk | Keterangan\n";
    echo "   ---|------------------|-------------|----------\n";
    
    foreach ($komponenBop as $index => $komponen) {
        echo "   " . ($index + 1) . " | " . $komponen['component'] . " | Rp " . formatNumberClean($komponen['rate_per_produk']) . " | " . ($komponen['description'] ?? '-') . "\n";
    }
    
    echo "   | **Total**        | **Rp " . formatNumberClean($totalBopPerProduk) . "** |          \n";
    
    echo "\n   Ringkasan Biaya:\n";
    echo "   Total BOP / produk: Rp " . formatNumberClean($totalBopPerProduk) . "\n";
    echo "   Biaya / produk: Rp " . formatNumberClean($totalBiayaPerProduk) . "\n";
    
} else {
    echo "BOP record not found!\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
