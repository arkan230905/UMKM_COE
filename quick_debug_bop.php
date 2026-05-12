<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== QUICK DEBUG BOP ===\n";

// Direct database check
$bop = \App\Models\BopProses::find(2);
if ($bop) {
    echo "BOP FOUND:\n";
    echo "ID: " . $bop->id . "\n";
    echo "komponen_bop: " . json_encode($bop->komponen_bop) . "\n";
    echo "total_bop_per_produk: " . $bop->total_bop_per_produk . "\n";
    
    // Test if we can access components
    if (is_array($bop->komponen_bop)) {
        echo "Komponen count: " . count($bop->komponen_bop) . "\n";
        foreach ($bop->komponen_bop as $i => $k) {
            echo "  K" . ($i+1) . ": " . $k['component'] . " -> " . $k['rate_per_produk'] . "\n";
        }
    } else {
        echo "komponen_bop is NOT array!\n";
    }
} else {
    echo "BOP NOT FOUND!\n";
}

echo "\n=== DONE ===\n";
