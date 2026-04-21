<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Conversion Issue ===" . PHP_EOL;

// Check Ayam Potong data and conversion ratios
$item = DB::table('bahan_bakus')->where('id', 1)->first();
echo "Ayam Potong Data:" . PHP_EOL;
echo "Main Satuan ID: " . $item->satuan_id . PHP_EOL;
echo "Sub Satuan 2 ID: " . $item->sub_satuan_2_id . PHP_EOL;
echo "Sub Satuan 2 Nilai: " . $item->sub_satuan_2_nilai . PHP_EOL;

// Get satuan names
$mainSatuan = DB::table('satuans')->where('id', $item->satuan_id)->first();
$subSatuan = DB::table('satuans')->where('id', $item->sub_satuan_2_id)->first();

echo "Main Satuan: " . $mainSatuan->nama . PHP_EOL;
echo "Sub Satuan: " . $subSatuan->nama . PHP_EOL;

echo PHP_EOL . "Conversion Logic:" . PHP_EOL;
echo "1 Potong = " . $item->sub_satuan_2_nilai . " " . $mainSatuan->nama . PHP_EOL;
echo "160 Potong = " . (160 * $item->sub_satuan_2_nilai) . " " . $mainSatuan->nama . PHP_EOL;

// Check stock movement data
$movement = DB::table('stock_movements')
    ->where('ref_type', 'production')
    ->where('ref_id', 2)
    ->where('item_id', 1)
    ->first();

echo PHP_EOL . "Stock Movement Data:" . PHP_EOL;
echo "Qty (stored): " . $movement->qty . PHP_EOL;
echo "Qty as Input: " . $movement->qty_as_input . PHP_EOL;
echo "Satuan as Input: " . $movement->satuan_as_input . PHP_EOL;

// Check available satuans from controller logic
echo PHP_EOL . "Available Satuans (from controller):" . PHP_EOL;
$availableSatuans = [];

// Main satuan
$availableSatuans[] = [
    'id' => $mainSatuan->id,
    'nama' => $mainSatuan->nama,
    'is_primary' => true,
    'conversion_to_primary' => 1,
    'price_conversion' => 1
];

// Sub satuan 2
if ($item->sub_satuan_2_id) {
    $quantityRatio = (float)($item->sub_satuan_2_nilai ?? 1);
    $priceRatio = (float)($item->sub_satuan_2_nilai ?? 1);
    $availableSatuans[] = [
        'id' => $subSatuan->id,
        'nama' => $subSatuan->nama,
        'is_primary' => false,
        'conversion_to_primary' => $quantityRatio,
        'price_conversion' => $priceRatio
    ];
}

foreach ($availableSatuans as $satuan) {
    echo "Satuan: " . $satuan['nama'] . ", Conversion: " . $satuan['conversion_to_primary'] . PHP_EOL;
}

// Simulate the conversion logic from the view
echo PHP_EOL . "Simulating View Logic:" . PHP_EOL;
$originalQty = 160; // qty_as_input
$originalSatuan = 'Potong'; // satuan_as_input

foreach ($availableSatuans as $unit) {
    echo PHP_EOL . "=== Converting to " . $unit['nama'] . " ===" . PHP_EOL;
    
    $conversionRate = $unit['conversion_to_primary'];
    echo "Conversion Rate: " . $conversionRate . PHP_EOL;
    
    // Check if the original satuan matches current unit
    if (strtolower($originalSatuan) === strtolower($unit['nama'])) {
        // Direct match - use original qty without conversion
        $convertedProduksiQty = $originalQty;
        echo "Direct match - Result: " . $convertedProduksiQty . " " . $unit['nama'] . PHP_EOL;
    } else {
        // Need conversion from original satuan to current unit
        $conversionFactor = 1;
        
        // Find conversion between original and target unit
        foreach ($availableSatuans as $availableUnit) {
            if (strtolower($availableUnit['nama']) === strtolower($originalSatuan)) {
                $originalToBase = $availableUnit['conversion_to_primary'] ?? 1;
                $targetToBase = $unit['conversion_to_primary'] ?? 1;
                
                echo "Original to Base: " . $originalToBase . PHP_EOL;
                echo "Target to Base: " . $targetToBase . PHP_EOL;
                
                if ($targetToBase > 0) {
                    $conversionFactor = $originalToBase / $targetToBase;
                }
                echo "Conversion Factor: " . $conversionFactor . PHP_EOL;
                break;
            }
        }
        
        $convertedProduksiQty = $originalQty * $conversionFactor;
        echo "Converted Result: " . $convertedProduksiQty . " " . $unit['nama'] . PHP_EOL;
    }
}
