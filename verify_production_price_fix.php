<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Production Price Fix ===" . PHP_EOL;

// Transaction data
$transaction = [
    'produksi_qty' => 40.0, // Stored in kg
    'produksi_nilai' => 1280000.00, // Total cost
    'qty_as_input' => 160.0, // Original in Potong
    'satuan_as_input' => 'Potong'
];

// Available units
$availableSatuans = [
    ['nama' => 'Kilogram', 'is_primary' => true, 'conversion_to_primary' => 1],
    ['nama' => 'Potong', 'is_primary' => false, 'conversion_to_primary' => 4],
    ['nama' => 'Gram', 'is_primary' => false, 'conversion_to_primary' => 0.001],
    ['nama' => 'Ons', 'is_primary' => false, 'conversion_to_primary' => 0.1]
];

echo "Transaction Data:" . PHP_EOL;
echo "Total Cost: Rp " . number_format($transaction['produksi_nilai'], 0) . PHP_EOL;
echo "Original Qty: " . $transaction['qty_as_input'] . " " . $transaction['satuan_as_input'] . PHP_EOL;
echo "Stored Qty: " . $transaction['produksi_qty'] . " kg" . PHP_EOL;

echo PHP_EOL . "=== Testing NEW Price Calculation Logic ===" . PHP_EOL;

foreach ($availableSatuans as $unit) {
    echo PHP_EOL . "=== " . $unit['nama'] . " ===" . PHP_EOL;
    
    // Simulate NEW logic
    if ($transaction['produksi_qty'] > 0 && $transaction['produksi_nilai'] > 0) {
        if (isset($transaction['qty_as_input']) && isset($transaction['satuan_as_input'])) {
            $originalQty = (float)$transaction['qty_as_input'];
            $originalSatuan = $transaction['satuan_as_input'];
            
            // Use original price per unit from stock movement total
            $originalPricePerUnit = $transaction['produksi_nilai'] / $originalQty;
            
            // Find the conversion factor for original unit
            $originalConversionFactor = 1;
            foreach ($availableSatuans as $availableUnit) {
                if (strtolower($availableUnit['nama']) === strtolower($originalSatuan)) {
                    $originalConversionFactor = $availableUnit['conversion_to_primary'] ?? 1;
                    break;
                }
            }
            
            if (strtolower($originalSatuan) === strtolower($unit['nama'])) {
                // Displaying in original unit - use original price
                $convertedProduksiHarga = $originalPricePerUnit;
                echo "Logic: Original unit match - use original price" . PHP_EOL;
            } elseif ($unit['is_primary']) {
                // Displaying in primary unit - convert price from original unit
                $convertedProduksiHarga = $originalPricePerUnit / $originalConversionFactor;
                echo "Logic: Primary unit - convert price from original" . PHP_EOL;
            } else {
                // Displaying in other sub unit - convert price appropriately
                $convertedProduksiHarga = $originalPricePerUnit * $unit['conversion_to_primary'] / $originalConversionFactor;
                echo "Logic: Other sub unit - convert price appropriately" . PHP_EOL;
            }
        }
    }
    
    echo "Price per " . $unit['nama'] . ": Rp " . number_format($convertedProduksiHarga, 0) . PHP_EOL;
    
    // Verify correctness
    $expectedPrices = [
        'Kilogram' => 2000, // Rp 8.000 / 4 = Rp 2.000
        'Potong' => 8000,   // Rp 8.000 per Potong
        'Gram' => 2,        // Rp 2.000 / 1000 = Rp 2
        'Ons' => 200        // Rp 2.000 / 10 = Rp 200
    ];
    
    $expected = $expectedPrices[$unit['nama']] ?? 0;
    $actual = $convertedProduksiHarga;
    $status = (abs($actual - $expected) < 0.01) ? "CORRECT" : "WRONG";
    
    echo "Expected: Rp " . number_format($expected, 0) . " - Status: " . $status . PHP_EOL;
}
