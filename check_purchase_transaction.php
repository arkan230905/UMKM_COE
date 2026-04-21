<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Purchase Transaction Data ===" . PHP_EOL;

// Check purchase transaction ID 1
echo "Checking pembelian/1 for Ayam Potong..." . PHP_EOL;

// Get purchase details
$pembelian = DB::table('pembelians')->where('id', 1)->first();
if (!$pembelian) {
    echo "Purchase transaction ID 1 not found!" . PHP_EOL;
    exit;
}

echo "Purchase Transaction:" . PHP_EOL;
echo "ID: " . $pembelian->id . PHP_EOL;
echo "Tanggal: " . $pembelian->tanggal . PHP_EOL;
echo "Total: " . $pembelian->total . PHP_EOL;

// Get purchase details
$pembelianDetails = DB::table('pembelian_details')
    ->where('pembelian_id', 1)
    ->where('bahan_baku_id', 1) // Ayam Potong
    ->get();

echo PHP_EOL . "Purchase Details for Ayam Potong:" . PHP_EOL;
foreach ($pembelianDetails as $detail) {
    echo "Bahan Baku ID: " . $detail->bahan_baku_id . PHP_EOL;
    echo "Jumlah: " . $detail->jumlah . PHP_EOL;
    echo "Satuan: " . $detail->satuan . PHP_EOL;
    echo "Harga Satuan: " . $detail->harga_satuan . PHP_EOL;
    echo "Subtotal: " . $detail->subtotal . PHP_EOL;
    echo "---" . PHP_EOL;
}

// Check stock movements for purchase
echo PHP_EOL . "Stock Movements for Purchase:" . PHP_EOL;
$purchaseMovements = DB::table('stock_movements')
    ->where('ref_type', 'purchase')
    ->where('ref_id', 1)
    ->where('item_id', 1)
    ->get();

foreach ($purchaseMovements as $movement) {
    echo "Tanggal: " . $movement->tanggal . PHP_EOL;
    echo "Direction: " . $movement->direction . PHP_EOL;
    echo "Qty: " . $movement->qty . PHP_EOL;
    echo "Unit Cost: " . $movement->unit_cost . PHP_EOL;
    echo "Total Cost: " . $movement->total_cost . PHP_EOL;
    echo "Manual Conversion Data: " . ($movement->manual_conversion_data ?? 'none') . PHP_EOL;
    echo "---" . PHP_EOL;
}

// Check what conversion was used
echo PHP_EOL . "=== Conversion Analysis ===" . PHP_EOL;
if (!empty($purchaseMovements) && isset($purchaseMovements[0]->manual_conversion_data)) {
    $conversionData = json_decode($purchaseMovements[0]->manual_conversion_data, true);
    if ($conversionData) {
        echo "Manual Conversion Data:" . PHP_EOL;
        foreach ($conversionData as $key => $value) {
            echo "  " . $key . ": " . $value . PHP_EOL;
        }
    }
} else {
    echo "No manual conversion data found" . PHP_EOL;
}

echo PHP_EOL . "Expected Conversion:" . PHP_EOL;
echo "Purchase: 1 kg = 3 Potong" . PHP_EOL;
echo "Production: 1 kg = 4 Potong" . PHP_EOL;

echo PHP_EOL . "Current Stock Report Shows:" . PHP_EOL;
echo "Kilogram: 50 kg (should be 40 kg)" . PHP_EOL;
echo "Potong: 150 Potong (should be 120 Potong)" . PHP_EOL;
