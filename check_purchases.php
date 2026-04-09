<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKING PURCHASE DATA ===\n\n";

$totalPembelians = \DB::table('pembelians')->count();
echo "Total pembelians: {$totalPembelians}\n";

$totalDetails = \DB::table('pembelian_details')->count();
echo "Total pembelian_details: {$totalDetails}\n";

// Check current stock values
echo "\nCurrent stock values:\n";
$bahanBakus = \DB::table('bahan_bakus')->select('id', 'nama_bahan', 'stok')->get();
foreach ($bahanBakus as $bahan) {
    echo "  {$bahan->nama_bahan}: stok={$bahan->stok}\n";
}

// Check if there are any stock movements
$stockMovements = \DB::table('stock_movements')->count();
echo "\nTotal stock_movements: {$stockMovements}\n";

$stockLayers = \DB::table('stock_layers')->count();
echo "Total stock_layers: {$stockLayers}\n";