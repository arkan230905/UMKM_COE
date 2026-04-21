<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Quantity Issue ===" . PHP_EOL;

// Current stock movement data
$movement = DB::table('stock_movements')
    ->where('ref_type', 'production')
    ->where('ref_id', 2)
    ->where('item_id', 1)
    ->first();

echo "Stock Movement Data:" . PHP_EOL;
echo "Stored Qty: " . $movement->qty . " (should be 40 kg)" . PHP_EOL;
echo "Qty as Input: " . $movement->qty_as_input . " (should be 160 Potong)" . PHP_EOL;
echo "Satuan as Input: " . $movement->satuan_as_input . PHP_EOL;

// Available units
$availableSatuans = [
    ['nama' => 'Kilogram', 'is_primary' => true, 'conversion' => 1],
    ['nama' => 'Potong', 'is_primary' => false, 'conversion' => 4],
    ['nama' => 'Gram', 'is_primary' => false, 'conversion' => 0.001],
    ['nama' => 'Ons', 'is_primary' => false, 'conversion' => 0.1]
];

echo PHP_EOL . "=== What Should Be Displayed ===" . PHP_EOL;
foreach ($availableSatuans as $unit) {
    echo $unit['nama'] . ": ";
    
    // CORRECT LOGIC
    if ($unit['nama'] === 'Potong') {
        echo "160 Potong (from qty_as_input)";
    } elseif ($unit['is_primary']) {
        echo "40 kg (from stored qty)";
    } else {
        $convertedQty = 40 * $unit['conversion']; // Convert from stored 40 kg
        echo $convertedQty . " " . $unit['nama'] . " (from stored 40 kg)";
    }
    echo PHP_EOL;
}

echo PHP_EOL . "=== What's Currently Being Shown ===" . PHP_EOL;
echo "Kilogram: 160 kg (WRONG - should be 40 kg)" . PHP_EOL;
echo "Gram: 160.000 gram (WRONG - should be 40.000 gram)" . PHP_EOL;
echo "Ons: 1.600 ons (WRONG - should be 400 ons)" . PHP_EOL;
echo "Potong: 160 Potong (CORRECT)" . PHP_EOL;

echo PHP_EOL . "The Issue:" . PHP_EOL;
echo "The view is using qty_as_input (160) for ALL units instead of:" . PHP_EOL;
echo "- Using qty_as_input (160) only for Potong unit" . PHP_EOL;
echo "- Using stored qty (40) for other units" . PHP_EOL;
