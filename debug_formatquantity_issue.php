<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug FormatQuantity Issue ===" . PHP_EOL;

// Check if formatQuantity function exists and how it works
if (function_exists('formatQuantity')) {
    echo "formatQuantity function exists" . PHP_EOL;
    
    // Test formatQuantity with different values
    echo PHP_EOL . "Testing formatQuantity function:" . PHP_EOL;
    echo "formatQuantity(40000, 'Gram'): " . formatQuantity(40000, 'Gram') . PHP_EOL;
    echo "formatQuantity(40, 'Kilogram'): " . formatQuantity(40, 'Kilogram') . PHP_EOL;
    echo "formatQuantity(160, 'Potong'): " . formatQuantity(160, 'Potong') . PHP_EOL;
    echo "formatQuantity(0, 'Gram'): " . formatQuantity(0, 'Gram') . PHP_EOL;
} else {
    echo "formatQuantity function does not exist" . PHP_EOL;
}

// Simulate the actual data that goes to the view
echo PHP_EOL . "=== Simulating View Data ===" . PHP_EOL;

// Transaction data as it would be processed by view
$transaction = [
    'produksi_qty' => 40.0,
    'qty_as_input' => 160.0,
    'satuan_as_input' => 'Potong',
    'ref_type' => 'production'
];

$unit = ['nama' => 'Gram', 'is_primary' => false, 'conversion' => 0.001];

// Process the quantity using view logic
$convertedProduksiQty = null;
if ($transaction['ref_type'] === 'production' && isset($transaction['qty_as_input']) && isset($transaction['satuan_as_input'])) {
    $originalQty = (float)$transaction['qty_as_input'];
    $originalSatuan = $transaction['satuan_as_input'];
    
    if (strtolower($originalSatuan) === strtolower($unit['nama'])) {
        $convertedProduksiQty = $originalQty;
    } elseif ($unit['is_primary']) {
        $convertedProduksiQty = $transaction['produksi_qty'];
    } else {
        $conversionMultiplier = 1 / $unit['conversion'];
        $convertedProduksiQty = $transaction['produksi_qty'] * $conversionMultiplier;
    }
}

echo "Converted Produksi Qty: " . $convertedProduksiQty . PHP_EOL;
echo "Is not zero? " . ($convertedProduksiQty != 0 ? "YES" : "NO") . PHP_EOL;

// Test the actual condition from view
$condition = isset($convertedProduksiQty) && $convertedProduksiQty != 0;
echo "View condition passes? " . ($condition ? "YES" : "NO") . PHP_EOL;

if ($condition) {
    echo "Would display: " . formatQuantity($convertedProduksiQty, $unit['nama']) . PHP_EOL;
} else {
    echo "Would display: (empty)" . PHP_EOL;
}
