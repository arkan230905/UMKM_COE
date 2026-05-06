<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CURRENT BOP DATA VERIFICATION ===\n";
$bop = \App\Models\BopProses::find(2);
if ($bop) {
    echo "BOP ID: " . $bop->id . "\n";
    echo "Total BOP / produk: " . $bop->total_bop_per_produk . "\n";
    echo "Total Biaya / produk: " . $bop->total_biaya_per_produk . "\n";
    echo "Komponen: " . json_encode($bop->komponen_bop) . "\n";
}
echo "\n=== DATABASE IS READY! 🎉 ===\n";
