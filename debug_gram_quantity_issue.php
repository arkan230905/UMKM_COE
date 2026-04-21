<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Gram Quantity Issue ===" . PHP_EOL;

// Check current stock movement data
$movement = DB::table('stock_movements')
    ->where('ref_type', 'production')
    ->where('ref_id', 2)
    ->where('item_id', 1)
    ->first();

echo "Stock Movement Data:" . PHP_EOL;
echo "qty (stored): " . $movement->qty . PHP_EOL;
echo "qty_as_input: " . $movement->qty_as_input . PHP_EOL;
echo "satuan_as_input: " . $movement->satuan_as_input . PHP_EOL;

// Check what controller sends
echo PHP_EOL . "=== Controller Data Flow ===" . PHP_EOL;
$transaction = [
    'produksi_qty' => (float)$movement->qty, // Should be 40
    'qty_as_input' => $movement->qty_as_input, // Should be 160
    'satuan_as_input' => $movement->satuan_as_input,
    'ref_type' => 'production'
];

echo "produksi_qty: " . $transaction['produksi_qty'] . PHP_EOL;
echo "qty_as_input: " . $transaction['qty_as_input'] . PHP_EOL;

// Check available units and their conversion values
echo PHP_EOL . "=== Available Units ===" . PHP_EOL;
$availableSatuans = [
    ['nama' => 'Kilogram', 'is_primary' => true, 'conversion' => 1],
    ['nama' => 'Potong', 'is_primary' => false, 'conversion' => 4],
    ['nama' => 'Gram', 'is_primary' => false, 'conversion' => 0.001],
    ['nama' => 'Ons', 'is_primary' => false, 'conversion' => 0.1]
];

foreach ($availableSatuans as $unit) {
    echo $unit['nama'] . ": conversion=" . $unit['conversion'] . ", is_primary=" . ($unit['is_primary'] ? 'true' : 'false') . PHP_EOL;
}

// Test the view logic for Gram specifically
echo PHP_EOL . "=== Testing View Logic for Gram ===" . PHP_EOL;
$unit = ['nama' => 'Gram', 'is_primary' => false, 'conversion' => 0.001];

if ($transaction['ref_type'] === 'production' && isset($transaction['qty_as_input']) && isset($transaction['satuan_as_input'])) {
    $originalQty = (float)$transaction['qty_as_input'];
    $originalSatuan = $transaction['satuan_as_input'];
    
    echo "Original Qty: " . $originalQty . PHP_EOL;
    echo "Original Satuan: " . $originalSatuan . PHP_EOL;
    
    if (strtolower($originalSatuan) === strtolower($unit['nama'])) {
        // Displaying in original unit - use original quantity
        $convertedProduksiQty = $originalQty;
        echo "Logic: Original unit match" . PHP_EOL;
        echo "Result: " . $convertedProduksiQty . PHP_EOL;
    } elseif ($unit['is_primary']) {
        // Displaying in primary/base unit - use stored converted quantity
        $convertedProduksiQty = $transaction['produksi_qty'];
        echo "Logic: Primary unit - use stored qty" . PHP_EOL;
        echo "Result: " . $convertedProduksiQty . PHP_EOL;
    } else {
        // Displaying in other sub unit - convert from stored base qty
        $conversionMultiplier = 1 / $unit['conversion'];
        $convertedProduksiQty = $transaction['produksi_qty'] * $conversionMultiplier;
        echo "Logic: Convert from kg - " . $transaction['produksi_qty'] . " × " . $conversionMultiplier . PHP_EOL;
        echo "Result: " . $convertedProduksiQty . PHP_EOL;
    }
}

echo PHP_EOL . "Expected for Gram: 40.000" . PHP_EOL;
echo "Actual: " . $convertedProduksiQty . PHP_EOL;
echo "Status: " . ($convertedProduksiQty == 40000 ? "CORRECT" : "WRONG") . PHP_EOL;
