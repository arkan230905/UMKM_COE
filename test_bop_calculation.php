<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING BOP CALCULATION WITH CORRECT DATA ===\n\n";

// Get the BOP record we just fixed
$bopProses = \App\Models\BopProses::find(2);

if ($bopProses) {
    echo "BOP ID: " . $bopProses->id . "\n";
    echo "Proses: " . ($bopProses->prosesProduksi ? $bopProses->prosesProduksi->nama_proses : 'N/A') . "\n";
    echo "Kapasitas: " . $bopProses->kapasitas_per_jam . " pcs/jam\n";
    echo "BTKL / jam: Rp " . number_format($bopProses->prosesProduksi->tarif_btkl) . "\n";
    
    // Calculate BTKL per produk
    $btklPerProduk = $bopProses->kapasitas_per_jam > 0 ? 
        floatval($bopProses->prosesProduksi->tarif_btkl) / $bopProses->kapasitas_per_jam : 0;
    echo "BTKL / produk: Rp " . number_format($btklPerProduk, 2) . "\n\n";
    
    echo "Komponen BOP:\n";
    if ($bopProses->komponen_bop && is_array($bopProses->komponen_bop)) {
        foreach ($bopProses->komponen_bop as $index => $komponen) {
            echo "  " . ($index + 1) . ". " . $komponen['component'] . ": Rp " . $komponen['rate_per_produk'] . " / produk\n";
        }
    }
    
    echo "\nPerhitungan:\n";
    echo "Total BOP / produk: Rp " . $bopProses->total_bop_per_produk . "\n";
    echo "Total Biaya / produk: Rp " . number_format($bopProses->total_biaya_per_produk, 2) . "\n";
    echo "BOP / unit: Rp " . $bopProses->bop_per_unit . "\n";
    
    // Expected calculation verification
    $expectedTotalBop = 67 + 28; // 95
    $expectedTotalBiaya = $btklPerProduk + $expectedTotalBop; // 166.67 + 95 = 261.67
    
    echo "\nExpected vs Actual:\n";
    echo "Expected Total BOP / produk: Rp " . $expectedTotalBop . "\n";
    echo "Actual Total BOP / produk: Rp " . $bopProses->total_bop_per_produk . "\n";
    echo "Match: " . ($bopProses->total_bop_per_produk == $expectedTotalBop ? "✅ YES" : "❌ NO") . "\n\n";
    
    echo "Expected Total Biaya / produk: Rp " . number_format($expectedTotalBiaya, 2) . "\n";
    echo "Actual Total Biaya / produk: Rp " . number_format($bopProses->total_biaya_per_produk, 2) . "\n";
    echo "Match: " . (abs($bopProses->total_biaya_per_produk - $expectedTotalBiaya) < 0.01 ? "✅ YES" : "❌ NO") . "\n";
    
} else {
    echo "BOP record not found!\n";
}

echo "\n=== TESTING MODEL AUTO-CALCULATION ===\n";

// Test model auto-calculation by creating a new test record
$testBop = new \App\Models\BopProses();
$testBop->user_id = 1;
$testBop->proses_produksi_id = 1;
$testBop->komponen_bop = [
    ['component' => 'Gas / BBM', 'rate_per_produk' => 50],
    ['component' => 'Air & Kebersihan', 'rate_per_produk' => 30],
    ['component' => 'Listrik', 'rate_per_produk' => 20]
];

echo "Test components:\n";
foreach ($testBop->komponen_bop as $komponen) {
    echo "- " . $komponen['component'] . ": Rp " . $komponen['rate_per_produk'] . "\n";
}

// Trigger the saving event to test auto-calculation
$testBop->save();

echo "\nAuto-calculated results:\n";
echo "Total BOP / produk: Rp " . $testBop->total_bop_per_produk . "\n";
echo "Total Biaya / produk: Rp " . number_format($testBop->total_biaya_per_produk, 2) . "\n";
echo "BOP / unit: Rp " . $testBop->bop_per_unit . "\n";

// Clean up test record
$testBop->delete();

echo "\n=== CALCULATION TEST COMPLETED! 🎉 ===\n";
