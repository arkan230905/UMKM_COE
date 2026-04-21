<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Gram Conversion Logic ===" . PHP_EOL;

// Transaction data
$transaction = [
    'produksi_qty' => 40.0, // 40 kg stored
    'qty_as_input' => 160.0, // 160 Potong original
    'satuan_as_input' => 'Potong',
    'ref_type' => 'production'
];

// All available units
$availableSatuans = [
    ['nama' => 'Kilogram', 'is_primary' => true, 'conversion_to_primary' => 1],
    ['nama' => 'Gram', 'is_primary' => false, 'conversion_to_primary' => 1000], // 1 Gram = 0.001 kg, so 1000 Gram = 1 kg
    ['nama' => 'Potong', 'is_primary' => false, 'conversion_to_primary' => 4],
    ['nama' => 'Ons', 'is_primary' => false, 'conversion_to_primary' => 10]
];

echo "Transaction Data:" . PHP_EOL;
echo "produksi_qty: " . $transaction['produksi_qty'] . " kg" . PHP_EOL;
echo "qty_as_input: " . $transaction['qty_as_input'] . " " . $transaction['satuan_as_input'] . PHP_EOL;

echo PHP_EOL . "=== Testing Conversion Logic for Each Unit ===" . PHP_EOL;

foreach ($availableSatuans as $unit) {
    echo PHP_EOL . $unit['nama'] . " (conversion_to_primary: " . $unit['conversion_to_primary'] . "):" . PHP_EOL;
    
    // Current view logic
    $convertedProduksiQty = 0;
    
    if ($transaction['ref_type'] === 'production' && isset($transaction['qty_as_input']) && isset($transaction['satuan_as_input'])) {
        $originalQty = (float)$transaction['qty_as_input'];
        $originalSatuan = $transaction['satuan_as_input'];
        
        if (strtolower($originalSatuan) === strtolower($unit['nama'])) {
            $convertedProduksiQty = $originalQty;
            echo "  Logic: Original unit match - Result: " . $convertedProduksiQty . PHP_EOL;
        } elseif ($unit['is_primary']) {
            $convertedProduksiQty = $transaction['produksi_qty'];
            echo "  Logic: Primary unit - Result: " . $convertedProduksiQty . PHP_EOL;
        } else {
            $conversionMultiplier = 1 / $unit['conversion_to_primary'];
            $convertedProduksiQty = $transaction['produksi_qty'] * $conversionMultiplier;
            echo "  Logic: Convert from kg - " . $transaction['produksi_qty'] . " × " . $conversionMultiplier . PHP_EOL;
            echo "  Result: " . $convertedProduksiQty . PHP_EOL;
        }
    }
    
    // Check expected result
    $expected = [
        'Kilogram' => 40,
        'Gram' => 40000,
        'Potong' => 160,
        'Ons' => 400
    ];
    
    $expectedValue = $expected[$unit['nama']] ?? 0;
    echo "  Expected: " . $expectedValue . PHP_EOL;
    echo "  Status: " . ($convertedProduksiQty == $expectedValue ? "CORRECT" : "WRONG") . PHP_EOL;
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;
echo "The issue is with conversion_to_primary interpretation:" . PHP_EOL;
echo "- For Gram: conversion_to_primary = 1000 means 1000 Gram = 1 kg" . PHP_EOL;
echo "- So to convert from kg to Gram: 40 kg × 1000 = 40,000 Gram" . PHP_EOL;
echo "- Current logic: 1/conversion_to_primary = 1/1000 = 0.001" . PHP_EOL;
echo "- This gives: 40 kg × 0.001 = 0.04 Gram (WRONG!)" . PHP_EOL;
echo PHP_EOL;
echo "The fix should be: Use conversion_to_primary directly as multiplier" . PHP_EOL;
echo "NOT: 1/conversion_to_primary" . PHP_EOL;
