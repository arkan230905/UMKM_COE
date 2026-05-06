<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING BOP CONTROLLER DIRECTLY ===\n\n";

// Test the controller method directly
$controller = new \App\Http\Controllers\MasterData\BopController();

try {
    // Simulate the showProses method call
    $id = 2;
    
    echo "Testing showProses method with ID: " . $id . "\n";
    
    // Get the BOP data like the controller does
    $bopProses = \App\Models\BopProses::with('prosesProduksi')->findOrFail($id);
    
    // Get matching BTKL data based on process name
    $btkl = \App\Models\Btkl::where('nama_btkl', $bopProses->prosesProduksi->nama_proses)->first();
    
    // Prepare data for view (same logic as in the controller)
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
    
    echo "Controller prepared data:\n";
    echo "bopProses->total_bop_per_produk: " . $bopProses->total_bop_per_produk . "\n";
    echo "totalBopPerProduk variable: " . $totalBopPerProduk . "\n";
    echo "totalBiayaPerProduk variable: " . $totalBiayaPerProduk . "\n";
    echo "komponenBop count: " . count($komponenBop) . "\n";
    
    echo "\nComponent details:\n";
    foreach ($komponenBop as $index => $komponen) {
        echo "  " . ($index + 1) . ". " . $komponen['component'] . " - Rp " . $komponen['rate_per_produk'] . "\n";
    }
    
    // Test what the view would render
    echo "\nExpected view rendering:\n";
    echo "Total BOP / produk: Rp " . $totalBopPerProduk . "\n";
    echo "Biaya / produk: Rp " . number_format($totalBiayaPerProduk, 2) . "\n";
    
    // Check if there's a formatNumberClean function
    if (function_exists('formatNumberClean')) {
        echo "formatNumberClean(totalBopPerProduk): " . formatNumberClean($totalBopPerProduk) . "\n";
    } else {
        echo "formatNumberClean function not found, using number_format\n";
        echo "number_format(totalBopPerProduk): " . number_format($totalBopPerProduk) . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== CONTROLLER TEST COMPLETE ===\n";
