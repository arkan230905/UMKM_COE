<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Gram Fix Complete ===" . PHP_EOL;

// Test the complete flow with the fix
echo "Testing complete view logic with initialization fix:" . PHP_EOL;

// Transaction data
$transaction = [
    'produksi_qty' => 40.0,
    'qty_as_input' => 160.0,
    'satuan_as_input' => 'Potong',
    'ref_type' => 'production'
];

// Test for Gram unit
$unit = ['nama' => 'Gram', 'is_primary' => false, 'conversion' => 0.001];

echo PHP_EOL . "=== Gram Unit Test ===" . PHP_EOL;

// Simulate view logic with initialization
$convertedProduksiQty = 0; // NEW: Proper initialization
$convertedProduksiHarga = 0; // NEW: Proper initialization

if ($transaction['ref_type'] === 'production' && isset($transaction['qty_as_input']) && isset($transaction['satuan_as_input'])) {
    $originalQty = (float)$transaction['qty_as_input'];
    $originalSatuan = $transaction['satuan_as_input'];
    
    if (strtolower($originalSatuan) === strtolower($unit['nama'])) {
        $convertedProduksiQty = $originalQty;
        echo "Logic: Original unit match" . PHP_EOL;
    } elseif ($unit['is_primary']) {
        $convertedProduksiQty = $transaction['produksi_qty'];
        echo "Logic: Primary unit - use stored qty" . PHP_EOL;
    } else {
        $conversionMultiplier = 1 / $unit['conversion'];
        $convertedProduksiQty = $transaction['produksi_qty'] * $conversionMultiplier;
        echo "Logic: Convert from kg - " . $transaction['produksi_qty'] . " × " . $conversionMultiplier . PHP_EOL;
    }
} else {
    $convertedProduksiQty = $transaction['produksi_qty'] * $unit['conversion'];
    echo "Logic: Standard conversion" . PHP_EOL;
}

echo "Result: " . number_format($convertedProduksiQty, 0) . " Gram" . PHP_EOL;
echo "Expected: 40.000 Gram" . PHP_EOL;
echo "Status: " . ($convertedProduksiQty == 40000 ? "CORRECT" : "WRONG") . PHP_EOL;

// Test all units
echo PHP_EOL . "=== All Units Test ===" . PHP_EOL;
$units = [
    ['nama' => 'Kilogram', 'is_primary' => true, 'conversion' => 1],
    ['nama' => 'Potong', 'is_primary' => false, 'conversion' => 4],
    ['nama' => 'Gram', 'is_primary' => false, 'conversion' => 0.001],
    ['nama' => 'Ons', 'is_primary' => false, 'conversion' => 0.1]
];

$expected = [
    'Kilogram' => 40,
    'Potong' => 160,
    'Gram' => 40000,
    'Ons' => 400
];

foreach ($units as $unit) {
    $convertedProduksiQty = 0; // Reset for each unit
    
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
    
    $expectedValue = $expected[$unit['nama']];
    $status = (abs($convertedProduksiQty - $expectedValue) < 1) ? "CORRECT" : "WRONG";
    
    echo $unit['nama'] . ": " . number_format($convertedProduksiQty, 0) . " - " . $status . PHP_EOL;
}

echo PHP_EOL . "=== Fix Summary ===" . PHP_EOL;
echo "Added proper initialization of convertedProduksiQty = 0" . PHP_EOL;
echo "This prevents undefined variable issues that could cause 0 values" . PHP_EOL;
echo "All units should now display correct production quantities" . PHP_EOL;
