<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Production Quantity Fix ===" . PHP_EOL;

// Simulate the data flow from controller to view
echo "Controller to View Data Flow:" . PHP_EOL;

// Stock movement data
$movement = DB::table('stock_movements')
    ->where('ref_type', 'production')
    ->where('ref_id', 2)
    ->where('item_id', 1)
    ->first();

echo "Stock Movement:" . PHP_EOL;
echo "qty (stored): " . $movement->qty . PHP_EOL;
echo "qty_as_input: " . $movement->qty_as_input . PHP_EOL;
echo "satuan_as_input: " . $movement->satuan_as_input . PHP_EOL;

// Controller now sends:
$transaction = [
    'produksi_qty' => (float)$movement->qty, // 40 kg (FIXED)
    'produksi_nilai' => (float)$movement->total_cost, // 1280000
    'qty_as_input' => $movement->qty_as_input, // 160
    'satuan_as_input' => $movement->satuan_as_input, // Potong
    'ref_type' => 'production'
];

echo PHP_EOL . "Controller Sends:" . PHP_EOL;
echo "produksi_qty: " . $transaction['produksi_qty'] . PHP_EOL;
echo "qty_as_input: " . $transaction['qty_as_input'] . PHP_EOL;

// Available units
$availableSatuans = [
    ['nama' => 'Kilogram', 'is_primary' => true, 'conversion' => 1],
    ['nama' => 'Potong', 'is_primary' => false, 'conversion' => 4],
    ['nama' => 'Gram', 'is_primary' => false, 'conversion' => 0.001],
    ['nama' => 'Ons', 'is_primary' => false, 'conversion' => 0.1]
];

echo PHP_EOL . "=== View Logic Results ===" . PHP_EOL;
foreach ($availableSatuans as $unit) {
    echo PHP_EOL . $unit['nama'] . ":" . PHP_EOL;
    
    // View logic
    if ($transaction['ref_type'] === 'production' && isset($transaction['qty_as_input']) && isset($transaction['satuan_as_input'])) {
        $originalQty = (float)$transaction['qty_as_input'];
        $originalSatuan = $transaction['satuan_as_input'];
        
        if (strtolower($originalSatuan) === strtolower($unit['nama'])) {
            // Displaying in original unit - use original quantity
            $convertedProduksiQty = $originalQty;
            echo "  Logic: Original unit match" . PHP_EOL;
        } elseif ($unit['is_primary']) {
            // Displaying in primary/base unit - use stored converted quantity
            $convertedProduksiQty = $transaction['produksi_qty'];
            echo "  Logic: Primary unit - use stored qty" . PHP_EOL;
        } else {
            // Displaying in other sub unit - convert from stored base qty
            $convertedProduksiQty = $transaction['produksi_qty'] * $unit['conversion'];
            echo "  Logic: Other sub unit - convert from stored" . PHP_EOL;
        }
    } else {
        $convertedProduksiQty = $transaction['produksi_qty'] * $unit['conversion'];
        echo "  Logic: Standard conversion" . PHP_EOL;
    }
    
    echo "  Result: " . $convertedProduksiQty . " " . $unit['nama'] . PHP_EOL;
    
    // Verify correctness
    $expected = [
        'Kilogram' => 40,
        'Potong' => 160,
        'Gram' => 0.04,
        'Ons' => 4
    ];
    
    $expectedValue = $expected[$unit['nama']] ?? 0;
    $status = (abs($convertedProduksiQty - $expectedValue) < 0.01) ? "CORRECT" : "WRONG";
    echo "  Expected: " . $expectedValue . " " . $unit['nama'] . " - Status: " . $status . PHP_EOL;
}
