<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Conversion Fix ===" . PHP_EOL;

// Simulate the NEW logic
echo "Testing NEW Conversion Logic:" . PHP_EOL;

// Transaction data (from stock movement)
$transaction = [
    'ref_type' => 'production',
    'produksi_qty' => 40.0, // Stored in kg
    'qty_as_input' => 160.0, // Original in Potong
    'satuan_as_input' => 'Potong'
];

// Available units
$availableSatuans = [
    ['nama' => 'Kilogram', 'is_primary' => true, 'conversion' => 1],
    ['nama' => 'Potong', 'is_primary' => false, 'conversion' => 4]
];

echo PHP_EOL . "Transaction Data:" . PHP_EOL;
echo "Stored Qty: " . $transaction['produksi_qty'] . " kg" . PHP_EOL;
echo "Original Qty: " . $transaction['qty_as_input'] . " " . $transaction['satuan_as_input'] . PHP_EOL;

foreach ($availableSatuans as $unit) {
    echo PHP_EOL . "=== Displaying in " . $unit['nama'] . " ===" . PHP_EOL;
    
    // NEW LOGIC
    if ($transaction['ref_type'] === 'production' && isset($transaction['qty_as_input']) && isset($transaction['satuan_as_input'])) {
        $originalQty = (float)$transaction['qty_as_input'];
        $originalSatuan = $transaction['satuan_as_input'];
        
        if (strtolower($originalSatuan) === strtolower($unit['nama'])) {
            // Displaying in original unit - use original quantity
            $convertedProduksiQty = $originalQty;
            echo "Logic: Original unit match - use original qty" . PHP_EOL;
        } elseif ($unit['is_primary']) {
            // Displaying in primary/base unit - use stored converted quantity
            $convertedProduksiQty = $transaction['produksi_qty'];
            echo "Logic: Primary unit - use stored qty" . PHP_EOL;
        } else {
            // Displaying in other sub unit - convert from stored base qty
            $convertedProduksiQty = $transaction['produksi_qty'] * $unit['conversion'];
            echo "Logic: Other sub unit - convert from stored base qty" . PHP_EOL;
        }
    }
    
    echo "Result: " . $convertedProduksiQty . " " . $unit['nama'] . PHP_EOL;
    
    // Verify correctness
    if ($unit['nama'] === 'Kilogram') {
        $expected = 40;
        $status = ($convertedProduksiQty == $expected) ? "CORRECT" : "WRONG";
        echo "Expected: " . $expected . " kg - Status: " . $status . PHP_EOL;
    } elseif ($unit['nama'] === 'Potong') {
        $expected = 160;
        $status = ($convertedProduksiQty == $expected) ? "CORRECT" : "WRONG";
        echo "Expected: " . $expected . " Potong - Status: " . $status . PHP_EOL;
    }
}
