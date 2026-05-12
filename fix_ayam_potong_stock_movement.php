<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fixing Ayam Potong Stock Movement ===" . PHP_EOL;

// Get the production detail
$produksiDetail = DB::table('produksi_details')
    ->where('produksi_id', 2)
    ->where('bahan_baku_id', 1)
    ->first();

if (!$produksiDetail) {
    echo "Production detail not found!" . PHP_EOL;
    exit;
}

echo "Production Detail Found:" . PHP_EOL;
echo "Qty Resep: " . $produksiDetail->qty_resep . PHP_EOL;
echo "Satuan Resep: " . $produksiDetail->satuan_resep . PHP_EOL;
echo "Qty Konversi: " . $produksiDetail->qty_konversi . PHP_EOL;

// Update the stock movement with the correct original data
$updated = DB::table('stock_movements')
    ->where('ref_type', 'production')
    ->where('ref_id', 2)
    ->where('item_id', 1)
    ->update([
        'qty_as_input' => $produksiDetail->qty_resep,
        'satuan_as_input' => $produksiDetail->satuan_resep,
    ]);

if ($updated) {
    echo "Stock movement updated successfully!" . PHP_EOL;
    echo "Updated qty_as_input to: " . $produksiDetail->qty_resep . PHP_EOL;
    echo "Updated satuan_as_input to: " . $produksiDetail->satuan_resep . PHP_EOL;
} else {
    echo "No stock movement found to update or no changes needed." . PHP_EOL;
}

// Verify the update
$movement = DB::table('stock_movements')
    ->where('ref_type', 'production')
    ->where('ref_id', 2)
    ->where('item_id', 1)
    ->first();

echo PHP_EOL . "Updated Stock Movement:" . PHP_EOL;
echo "Qty: " . $movement->qty . PHP_EOL;
echo "Qty as Input: " . $movement->qty_as_input . PHP_EOL;
echo "Satuan as Input: " . $movement->satuan_as_input . PHP_EOL;
