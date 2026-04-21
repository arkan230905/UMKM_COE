<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Ekor Conversion ===" . PHP_EOL;

// Get the actual purchase data
$pembelianDetail = DB::table('pembelian_details')
    ->where('pembelian_id', 1)
    ->where('bahan_baku_id', 1)
    ->first();

echo "Purchase Detail:" . PHP_EOL;
echo "Jumlah: " . $pembelianDetail->jumlah . " Ekor" . PHP_EOL;
echo "Satuan ID: " . $pembelianDetail->satuan . PHP_EOL;

// Get Ekor satuan details
$ekorSatuan = DB::table('satuans')->where('id', $pembelianDetail->satuan)->first();
echo "Ekor Satuan: " . $ekorSatuan->nama . PHP_EOL;

// Check Ayam Potong sub satuan configurations
$item = DB::table('bahan_bakus')->where('id', 1)->first();
echo PHP_EOL . "Ayam Potong Unit Configuration:" . PHP_EOL;
echo "Main Satuan ID: " . $item->satuan_id . " (Kilogram)" . PHP_EOL;
echo "Sub Satuan 1 ID: " . $item->sub_satuan_1_id . " (Gram)" . PHP_EOL;
echo "Sub Satuan 2 ID: " . $item->sub_satuan_2_id . " (Potong)" . PHP_EOL;
echo "Sub Satuan 3 ID: " . $item->sub_satuan_3_id . " (Ons)" . PHP_EOL;

echo PHP_EOL . "Conversion Values:" . PHP_EOL;
echo "Sub Satuan 1 Nilai: " . $item->sub_satuan_1_nilai . " (1000 Gram = 1 kg)" . PHP_EOL;
echo "Sub Satuan 2 Nilai: " . $item->sub_satuan_2_nilai . " (4 Potong = 1 kg)" . PHP_EOL;
echo "Sub Satuan 3 Nilai: " . $item->sub_satuan_3_nilai . " (10 Ons = 1 kg)" . PHP_EOL;

// Check stock movement to see how it was converted
$movement = DB::table('stock_movements')
    ->where('ref_type', 'purchase')
    ->where('ref_id', 1)
    ->where('item_id', 1)
    ->first();

echo PHP_EOL . "Stock Movement:" . PHP_EOL;
echo "Stored Qty: " . $movement->qty . " kg" . PHP_EOL;
echo "Manual Conversion Data: " . $movement->manual_conversion_data . PHP_EOL;

// Parse the conversion
$manualData = json_decode($movement->manual_conversion_data, true);
echo PHP_EOL . "Manual Conversion Details:" . PHP_EOL;
foreach ($manualData as $key => $value) {
    echo "  " . $key . ": " . $value . PHP_EOL;
}

echo PHP_EOL . "=== Conversion Analysis ===" . PHP_EOL;
echo "Purchase: 50 Ekor" . PHP_EOL;
echo "Stock Movement: 50 kg" . PHP_EOL;
echo "This means: 50 Ekor = 50 kg" . PHP_EOL;
echo "So: 1 Ekor = 1 kg" . PHP_EOL;
echo PHP_EOL;

echo "With manual conversion 1 kg = 3 Potong:" . PHP_EOL;
echo "50 Ekor = 50 kg = 150 Potong" . PHP_EOL;
echo PHP_EOL;

echo "User expects:" . PHP_EOL;
echo "- 40 kg" . PHP_EOL;
echo "- 120 Potong" . PHP_EOL;
echo PHP_EOL;

echo "This suggests user expects:" . PHP_EOL;
echo "- 40 Ekor (not 50 Ekor)" . PHP_EOL;
echo "- 40 Ekor = 40 kg = 120 Potong" . PHP_EOL;
echo PHP_EOL;

echo "=== Possible Solutions ===" . PHP_EOL;
echo "1. Update purchase quantity from 50 Ekor to 40 Ekor" . PHP_EOL;
echo "2. Update stock movement from 50 kg to 40 kg" . PHP_EOL;
echo "3. Update manual conversion from 150 Potong to 120 Potong" . PHP_EOL;
