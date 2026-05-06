<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING BOP DETAIL VIEW DATA ===\n\n";

// Simulate controller data preparation
$bopProses = \App\Models\BopProses::with('prosesProduksi')->find(2);

if ($bopProses) {
    echo "BOP Data Found:\n";
    echo "ID: " . $bopProses->id . "\n";
    echo "Proses: " . ($bopProses->prosesProduksi ? $bopProses->prosesProduksi->nama_proses : 'N/A') . "\n";
    echo "Kapasitas: " . $bopProses->kapasitas_per_jam . " pcs/jam\n";
    echo "Total BOP / produk: " . $bopProses->total_bop_per_produk . "\n";
    echo "Total Biaya / produk: " . $bopProses->total_biaya_per_produk . "\n";
    
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
    
    echo "\nPrepared Data for View:\n";
    echo "Kapasitas: " . $kapasitas . "\n";
    echo "Total BOP Per Produk: " . $totalBopPerProduk . "\n";
    echo "Total Biaya Per Produk: " . $totalBiayaPerProduk . "\n";
    echo "BTKL Per Jam: " . $btklPerJam . "\n";
    echo "BTKL Per Produk: " . number_format($btklPerProduk, 2) . "\n";
    
    echo "\nKomponen BOP Data:\n";
    if (!empty($komponenBop)) {
        foreach ($komponenBop as $index => $komponen) {
            echo "  " . ($index + 1) . ". " . $komponen['component'] . "\n";
            echo "     Rate per produk: " . $komponen['rate_per_produk'] . "\n";
            echo "     Description: " . ($komponen['description'] ?? '-') . "\n";
        }
    } else {
        echo "  No components found!\n";
    }
    
    echo "\nExpected View Output:\n";
    echo "Komponen BOP Table:\n";
    echo "No | Komponen         | Rp / produk | Keterangan\n";
    echo "---|------------------|-------------|----------\n";
    
    if (!empty($komponenBop)) {
        foreach ($komponenBop as $index => $komponen) {
            echo ($index + 1) . " | " . $komponen['component'] . " | " . $komponen['rate_per_produk'] . " | " . ($komponen['description'] ?? '-') . "\n";
        }
        echo "   | **Total**        | **" . $totalBopPerProduk . "** |          \n";
    } else {
        echo "   | No data         |             |          \n";
    }
    
    echo "\nRingkasan Biaya:\n";
    echo "Total BOP / produk: Rp " . $totalBopPerProduk . "\n";
    echo "Biaya / produk: Rp " . number_format($totalBiayaPerProduk, 2) . "\n";
    
} else {
    echo "BOP record not found!\n";
}

echo "\n=== DETAIL VIEW DATA TEST COMPLETED! 🎉 ===\n";
