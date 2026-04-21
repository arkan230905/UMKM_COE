<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check how production stock movements are created
echo "=== Production Stock Movement Creation Check ===" . PHP_EOL;

// Check the actual production details
$produksiDetail = DB::table('produksi_details')
    ->where('produksi_id', 2)
    ->where('bahan_baku_id', 1)
    ->first();

echo "Production Detail:" . PHP_EOL;
echo "Qty Resep: " . $produksiDetail->qty_resep . PHP_EOL;
echo "Satuan Resep: " . $produksiDetail->satuan_resep . PHP_EOL;
echo "Qty Konversi: " . $produksiDetail->qty_konversi . PHP_EOL;
echo "Satuan: " . $produksiDetail->satuan . PHP_EOL;

// Check if stock movements have the required fields
$movement = DB::table('stock_movements')
    ->where('ref_type', 'production')
    ->where('ref_id', 2)
    ->where('item_id', 1)
    ->first();

echo PHP_EOL . "Stock Movement Fields:" . PHP_EOL;
echo "Has qty_as_input: " . (isset($movement->qty_as_input) ? 'Yes' : 'No') . PHP_EOL;
echo "Has satuan_as_input: " . (isset($movement->satuan_as_input) ? 'Yes' : 'No') . PHP_EOL;

if (isset($movement->qty_as_input)) {
    echo "qty_as_input value: " . $movement->qty_as_input . PHP_EOL;
}
if (isset($movement->satuan_as_input)) {
    echo "satuan_as_input value: " . $movement->satuan_as_input . PHP_EOL;
}

// Check stock_movements table structure
echo PHP_EOL . "=== Stock Movements Table Structure ===" . PHP_EOL;
$columns = DB::select('DESCRIBE stock_movements');
foreach ($columns as $column) {
    if (strpos($column->Field, 'qty') !== false || strpos($column->Field, 'satuan') !== false) {
        echo $column->Field . " - " . $column->Type . PHP_EOL;
    }
}
