<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Conversion Logic Correctly ===" . PHP_EOL;

// Ayam Potong conversion data
echo "Ayam Potong Conversion:" . PHP_EOL;
echo "1 Potong = 4 Kilogram" . PHP_EOL;
echo "160 Potong = 40 Kilogram (correct)" . PHP_EOL;

// Current logic in view
echo PHP_EOL . "Current View Logic Analysis:" . PHP_EOL;
$originalQty = 160; // 160 Potong
$originalSatuan = 'Potong';
$targetUnit = 'Kilogram';

// Available satuan data
$availableSatuans = [
    ['nama' => 'Kilogram', 'conversion_to_primary' => 1], // Primary unit (kg)
    ['nama' => 'Potong', 'conversion_to_primary' => 4],  // 1 Potong = 4 kg
];

echo "Original Qty: " . $originalQty . " " . $originalSatuan . PHP_EOL;
echo "Target Unit: " . $targetUnit . PHP_EOL;

// Find conversion factor
$conversionFactor = 1;
foreach ($availableSatuans as $availableUnit) {
    if (strtolower($availableUnit['nama']) === strtolower($originalSatuan)) {
        $originalToBase = $availableUnit['conversion_to_primary'] ?? 1;
        $targetToBase = 1; // Kilogram is primary unit
        
        echo "Original to Base: " . $originalToBase . " (1 " . $originalSatuan . " = " . $originalToBase . " kg)" . PHP_EOL;
        echo "Target to Base: " . $targetToBase . " (1 " . $targetUnit . " = " . $targetToBase . " kg)" . PHP_EOL;
        
        if ($targetToBase > 0) {
            $conversionFactor = $originalToBase / $targetToBase;
        }
        echo "Conversion Factor: " . $conversionFactor . PHP_EOL;
        break;
    }
}

$convertedQty = $originalQty * $conversionFactor;
echo "Current Logic Result: " . $convertedQty . " " . $targetUnit . PHP_EOL;

echo PHP_EOL . "What's WRONG with current logic:" . PHP_EOL;
echo "- Current: 160 Potong × 4 = 640 kg" . PHP_EOL;
echo "- Correct: 160 Potong ÷ 4 = 40 kg" . PHP_EOL;

echo PHP_EOL . "CORRECT Logic Should Be:" . PHP_EOL;
echo "If 1 Potong = 4 kg, then:" . PHP_EOL;
echo "160 Potong = 160 × 4 kg = 640 kg" . PHP_EOL;
echo "BUT the stock movement stores 40 kg (already converted)" . PHP_EOL;
echo "So the view should use the STORED qty (40 kg) for kg display" . PHP_EOL;
echo "And use ORIGINAL qty (160) for potong display" . PHP_EOL;

echo PHP_EOL . "The REAL Issue:" . PHP_EOL;
echo "- Stock movement stores: qty = 40 kg (already converted)" . PHP_EOL;
echo "- Stock movement also stores: qty_as_input = 160 Potong (original)" . PHP_EOL;
echo "- For kg display: should use qty (40 kg)" . PHP_EOL;
echo "- For potong display: should use qty_as_input (160 Potong)" . PHP_EOL;
echo "- Current view tries to convert qty_as_input again, causing double conversion!" . PHP_EOL;
