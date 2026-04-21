<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Gram Conversion Fix ===" . PHP_EOL;

// Transaction data
$transaction = [
    'produksi_qty' => 40.0, // 40 kg stored
    'qty_as_input' => 160.0, // 160 Potong original
    'satuan_as_input' => 'Potong',
    'ref_type' => 'production'
];

// All available units with correct conversion logic
$availableSatuans = [
    ['nama' => 'Kilogram', 'is_primary' => true, 'conversion' => 1],
    ['nama' => 'Gram', 'is_primary' => false, 'conversion' => 1000], // 1000 Gram = 1 kg
    ['nama' => 'Potong', 'is_primary' => false, 'conversion' => 4],
    ['nama' => 'Ons', 'is_primary' => false, 'conversion' => 10] // 10 Ons = 1 kg
];

echo "Testing FIXED conversion logic:" . PHP_EOL;
echo "produksi_qty: " . $transaction['produksi_qty'] . " kg" . PHP_EOL;
echo "qty_as_input: " . $transaction['qty_as_input'] . " " . $transaction['satuan_as_input'] . PHP_EOL;

echo PHP_EOL . "=== Results with Fixed Logic ===" . PHP_EOL;

foreach ($availableSatuans as $unit) {
    echo PHP_EOL . $unit['nama'] . ":" . PHP_EOL;
    
    // FIXED LOGIC
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
            // FIXED: Use conversion directly, not 1/conversion
            $conversionMultiplier = $unit['conversion'];
            $convertedProduksiQty = $transaction['produksi_qty'] * $conversionMultiplier;
            echo "  Logic: Convert from kg - " . $transaction['produksi_qty'] . " × " . $conversionMultiplier . PHP_EOL;
            echo "  Result: " . number_format($convertedProduksiQty, 0) . PHP_EOL;
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
    echo "  Expected: " . number_format($expectedValue, 0) . PHP_EOL;
    echo "  Status: " . ($convertedProduksiQty == $expectedValue ? "CORRECT" : "WRONG") . PHP_EOL;
}

echo PHP_EOL . "=== Fix Summary ===" . PHP_EOL;
echo "Changed: \$conversionMultiplier = 1 / \$unit['conversion']" . PHP_EOL;
echo "To: \$conversionMultiplier = \$unit['conversion']" . PHP_EOL;
echo PHP_EOL;
echo "Why this works:" . PHP_EOL;
echo "- For Gram: conversion = 1000 means 1000 Gram = 1 kg" . PHP_EOL;
echo "- To convert 40 kg to Gram: 40 × 1000 = 40,000 Gram" . PHP_EOL;
echo "- Previously: 40 × (1/1000) = 0.04 Gram (WRONG)" . PHP_EOL;
echo "- Now: 40 × 1000 = 40,000 Gram (CORRECT)" . PHP_EOL;
