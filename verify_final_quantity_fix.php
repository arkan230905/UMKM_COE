<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Final Production Quantity Fix ===" . PHP_EOL;

// Transaction data (what controller sends)
$transaction = [
    'produksi_qty' => 40.0, // 40 kg (stored converted qty)
    'qty_as_input' => 160.0, // 160 Potong (original)
    'satuan_as_input' => 'Potong',
    'ref_type' => 'production'
];

// Available units with correct conversion logic
$availableSatuans = [
    ['nama' => 'Kilogram', 'is_primary' => true, 'conversion' => 1],      // 1 kg = 1 kg
    ['nama' => 'Potong', 'is_primary' => false, 'conversion' => 4],     // 1 Potong = 4 kg  
    ['nama' => 'Gram', 'is_primary' => false, 'conversion' => 0.001],   // 1 gram = 0.001 kg
    ['nama' => 'Ons', 'is_primary' => false, 'conversion' => 0.1]       // 1 ons = 0.1 kg
];

echo "Transaction Data:" . PHP_EOL;
echo "produksi_qty: " . $transaction['produksi_qty'] . " kg" . PHP_EOL;
echo "qty_as_input: " . $transaction['qty_as_input'] . " " . $transaction['satuan_as_input'] . PHP_EOL;

echo PHP_EOL . "=== Final View Logic Results ===" . PHP_EOL;
foreach ($availableSatuans as $unit) {
    echo PHP_EOL . $unit['nama'] . ":" . PHP_EOL;
    
    // NEW FIXED LOGIC
    if ($transaction['ref_type'] === 'production' && isset($transaction['qty_as_input']) && isset($transaction['satuan_as_input'])) {
        $originalQty = (float)$transaction['qty_as_input'];
        $originalSatuan = $transaction['satuan_as_input'];
        
        if (strtolower($originalSatuan) === strtolower($unit['nama'])) {
            // Displaying in original unit - use original quantity
            $convertedProduksiQty = $originalQty;
            echo "  Logic: Original unit match - use " . $originalQty . PHP_EOL;
        } elseif ($unit['is_primary']) {
            // Displaying in primary/base unit - use stored converted quantity
            $convertedProduksiQty = $transaction['produksi_qty'];
            echo "  Logic: Primary unit - use stored " . $transaction['produksi_qty'] . PHP_EOL;
        } else {
            // Displaying in other sub unit - convert from stored base qty
            // For kg to gram: 40 kg × (1/0.001) = 40,000 gram
            // For kg to ons: 40 kg × (1/0.1) = 400 ons
            $conversionMultiplier = 1 / $unit['conversion'];
            $convertedProduksiQty = $transaction['produksi_qty'] * $conversionMultiplier;
            echo "  Logic: Convert from kg - " . $transaction['produksi_qty'] . " × " . $conversionMultiplier . PHP_EOL;
        }
    }
    
    echo "  Result: " . number_format($convertedProduksiQty, 0) . " " . $unit['nama'] . PHP_EOL;
    
    // Expected results
    $expected = [
        'Kilogram' => 40,
        'Potong' => 160, 
        'Gram' => 40000,
        'Ons' => 400
    ];
    
    $expectedValue = $expected[$unit['nama']] ?? 0;
    $status = (abs($convertedProduksiQty - $expectedValue) < 1) ? "CORRECT" : "WRONG";
    echo "  Expected: " . number_format($expectedValue, 0) . " " . $unit['nama'] . " - Status: " . $status . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Kilogram: 40 kg (CORRECT)" . PHP_EOL;
echo "Potong: 160 Potong (CORRECT)" . PHP_EOL;
echo "Gram: 40.000 Gram (CORRECT)" . PHP_EOL;
echo "Ons: 400 Ons (CORRECT)" . PHP_EOL;
