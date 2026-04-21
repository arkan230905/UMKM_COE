<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Pembelian Detail Columns:\n";
$columns = \DB::select('SHOW COLUMNS FROM pembelian_details');
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n\nPembelian Detail for Ayam Potong (ID 1):\n";
$detail = \DB::table('pembelian_details')
    ->where('pembelian_id', 1)
    ->where('bahan_baku_id', 1)
    ->first();

if ($detail) {
    echo json_encode($detail, JSON_PRETTY_PRINT) . "\n";
}

echo "\n\nStock Movement for this Purchase:\n";
$movement = \App\Models\StockMovement::where('ref_type', 'purchase')
    ->where('ref_id', 1)
    ->where('item_id', 1)
    ->first();

if ($movement) {
    echo json_encode($movement->toArray(), JSON_PRETTY_PRINT) . "\n";
}
