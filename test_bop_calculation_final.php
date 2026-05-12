<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING FINAL BOP CALCULATION ===\n\n";

// Test the BOP calculation with current data
$bopProses = \App\Models\BopProses::with('prosesProduksi')->find(2);

if ($bopProses) {
    echo "BOP Data Found:\n";
    echo "ID: " . $bopProses->id . "\n";
    echo "Proses: " . ($bopProses->prosesProduksi ? $bopProses->prosesProduksi->nama_proses : 'N/A') . "\n";
    echo "Kapasitas: " . $bopProses->kapasitas_per_jam . " pcs/jam\n";
    echo "BTKL / jam: Rp " . number_format($bopProses->prosesProduksi->tarif_btkl ?? 0) . "\n";
    
    echo "\nKomponen BOP:\n";
    if ($bopProses->komponen_bop && is_array($bopProses->komponen_bop)) {
        foreach ($bopProses->komponen_bop as $index => $komponen) {
            echo "  " . ($index + 1) . ". " . $komponen['component'] . " - Rp " . $komponen['rate_per_produk'] . " / produk\n";
        }
    }
    
    echo "\nPerhitungan:\n";
    echo "Total BOP / produk: Rp " . $bopProses->total_bop_per_produk . "\n";
    echo "Total Biaya / produk: Rp " . number_format($bopProses->total_biaya_per_produk, 2) . "\n";
    echo "BOP / unit: Rp " . $bopProses->bop_per_unit . "\n";
    
    // Manual calculation verification
    $manualTotalBop = 0;
    if ($bopProses->komponen_bop && is_array($bopProses->komponen_bop)) {
        foreach ($bopProses->komponen_bop as $komponen) {
            $manualTotalBop += floatval($komponen['rate_per_produk']);
        }
    }
    
    $btklPerProduk = $bopProses->kapasitas_per_jam > 0 ? 
        floatval($bopProses->prosesProduksi->tarif_btkl) / $bopProses->kapasitas_per_jam : 0;
    $manualTotalBiaya = $btklPerProduk + $manualTotalBop;
    
    echo "\nManual Verification:\n";
    echo "Manual Total BOP: Rp " . $manualTotalBop . "\n";
    echo "Manual Total Biaya: Rp " . number_format($manualTotalBiaya, 2) . "\n";
    
    echo "\nComparison:\n";
    echo "Total BOP Match: " . ($bopProses->total_bop_per_produk == $manualTotalBop ? "✅ YES" : "❌ NO") . "\n";
    echo "Total Biaya Match: " . (abs($bopProses->total_biaya_per_produk - $manualTotalBiaya) < 0.01 ? "✅ YES" : "❌ NO") . "\n";
    
    // Test model calculation
    echo "\nTesting Model Auto-Calculation:\n";
    $testBop = new \App\Models\BopProses();
    $testBop->komponen_bop = [
        ['component' => 'Gas / BBM', 'rate_per_produk' => 67],
        ['component' => 'Air & Kebersihan', 'rate_per_produk' => 28]
    ];
    $testBop->prosesProduksi = $bopProses->prosesProduksi;
    
    // Trigger the saving event
    $testBop->save();
    
    echo "Model Calculated Total BOP: Rp " . $testBop->total_bop_per_produk . "\n";
    echo "Model Calculated Total Biaya: Rp " . number_format($testBop->total_biaya_per_produk, 2) . "\n";
    
    // Clean up
    $testBop->delete();
    
} else {
    echo "BOP record not found!\n";
}

echo "\n=== FINAL TEST COMPLETED! 🎉 ===\n";
