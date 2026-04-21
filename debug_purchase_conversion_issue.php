<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Purchase Conversion Issue ===" . PHP_EOL;

// Get actual stock movement data for purchase
$movement = DB::table('stock_movements')
    ->where('ref_type', 'purchase')
    ->where('ref_id', 1)
    ->where('item_id', 1)
    ->first();

echo "Purchase Stock Movement:" . PHP_EOL;
echo "Qty: " . $movement->qty . PHP_EOL;
echo "Manual Conversion Data: " . ($movement->manual_conversion_data ?? 'none') . PHP_EOL;

// Parse manual conversion data
$manualData = json_decode($movement->manual_conversion_data, true);
echo PHP_EOL . "Manual Conversion Data Parsed:" . PHP_EOL;
if ($manualData) {
    foreach ($manualData as $key => $value) {
        echo "  " . $key . ": " . $value . PHP_EOL;
    }
}

// Check what controller sends
echo PHP_EOL . "=== Controller Data for Purchase ===" . PHP_EOL;

// Simulate controller processing for purchase
if ($movement->direction === 'in') {
    if ($movement->ref_type === 'purchase') {
        $dailyInQty = (float)$movement->qty; // Should be 50
        $dailyInNilai = (float)($movement->total_cost ?? 0); // Should be 1600000
    } else {
        $dailyInQty = 0;
        $dailyInNilai = 0;
    }
    
    echo "Controller sends:" . PHP_EOL;
    echo "pembelian_qty: " . $dailyInQty . PHP_EOL;
    echo "pembelian_nilai: " . $dailyInNilai . PHP_EOL;
    echo "manual_conversion_data: " . ($movement->manual_conversion_data ?? 'none') . PHP_EOL;
}

// Test view logic for both units
echo PHP_EOL . "=== Testing View Logic ===" . PHP_EOL;

// Available units
$units = [
    ['nama' => 'Kilogram', 'id' => 2, 'conversion' => 1],
    ['nama' => 'Potong', 'id' => 6, 'conversion' => 4],
    ['nama' => 'Gram', 'id' => 4, 'conversion' => 1000],
    ['nama' => 'Ons', 'id' => 1, 'conversion' => 10]
];

$transaction = [
    'ref_type' => 'purchase',
    'pembelian_qty' => 50,
    'pembelian_nilai' => 1600000,
    'manual_conversion_data' => $manualData
];

foreach ($units as $unit) {
    echo PHP_EOL . $unit['nama'] . " (ID: " . $unit['id'] . "):" . PHP_EOL;
    
    // Simulate view logic
    $purchaseConversionRate = $unit['conversion']; // Default to master rate
    
    // Check manual conversion condition
    $condition = ($transaction['ref_type'] === 'purchase' &&
        isset($transaction['manual_conversion_data']) && $transaction['manual_conversion_data'] && 
        isset($transaction['manual_conversion_data']['sub_satuan_id']) &&
        $transaction['manual_conversion_data']['sub_satuan_id'] == $unit['id']);
    
    echo "  Manual conversion condition: " . ($condition ? "TRUE" : "FALSE") . PHP_EOL;
    
    if ($condition) {
        $purchaseConversionRate = (float)($transaction['manual_conversion_data']['manual_conversion_factor'] ?? $unit['conversion']);
        echo "  Using manual conversion factor: " . $purchaseConversionRate . PHP_EOL;
    } else {
        echo "  Using master conversion rate: " . $purchaseConversionRate . PHP_EOL;
    }
    
    $convertedPembelianQty = $transaction['pembelian_qty'] * $purchaseConversionRate;
    echo "  Result: " . $convertedPembelianQty . " " . $unit['nama'] . PHP_EOL;
    
    // Expected results
    $expected = [
        'Kilogram' => 50, // 50 kg × 1 = 50 kg
        'Potong' => 150, // 50 kg × 3 = 150 Potong (using manual conversion)
        'Gram' => 50000, // 50 kg × 1000 = 50,000 Gram
        'Ons' => 500 // 50 kg × 10 = 500 Ons
    ];
    
    echo "  Expected: " . $expected[$unit['nama']] . " " . $unit['nama'] . PHP_EOL;
    echo "  Status: " . ($convertedPembelianQty == $expected[$unit['nama']] ? "CORRECT" : "WRONG") . PHP_EOL;
}

echo PHP_EOL . "=== Issue Analysis ===" . PHP_EOL;
echo "User expects:" . PHP_EOL;
echo "- Kilogram: 40 kg" . PHP_EOL;
echo "- Potong: 120 Potong" . PHP_EOL;
echo PHP_EOL;
echo "But current logic shows:" . PHP_EOL;
echo "- Kilogram: 50 kg" . PHP_EOL;
echo "- Potong: 150 Potong (using 1 kg = 3 Potong)" . PHP_EOL;
echo PHP_EOL;
echo "The issue might be:" . PHP_EOL;
echo "1. User expects different base quantity (40 kg instead of 50 kg)" . PHP_EOL;
echo "2. Or there's a calculation error in the expected results" . PHP_EOL;
