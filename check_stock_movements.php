<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check stock movements for Ayam Potong on 2026-04-18
echo "=== Stock Movements for Ayam Potong on 2026-04-18 ===" . PHP_EOL;
$movements = DB::table('stock_movements')
    ->where('item_type', 'material')
    ->where('item_id', 1)
    ->whereDate('tanggal', '2026-04-18')
    ->get();

foreach ($movements as $movement) {
    echo "ID: " . $movement->id . PHP_EOL;
    echo "Tanggal: " . $movement->tanggal . PHP_EOL;
    echo "Ref Type: " . $movement->ref_type . PHP_EOL;
    echo "Ref ID: " . $movement->ref_id . PHP_EOL;
    echo "Direction: " . $movement->direction . PHP_EOL;
    echo "Qty: " . $movement->qty . PHP_EOL;
    echo "Unit Cost: " . $movement->unit_cost . PHP_EOL;
    echo "Total Cost: " . $movement->total_cost . PHP_EOL;
    echo "Manual Conversion Data: " . ($movement->manual_conversion_data ?? 'none') . PHP_EOL;
    echo "---" . PHP_EOL;
}

// Check conversion ratios for Ayam Potong
echo PHP_EOL . "=== Ayam Potong Conversion Data ===" . PHP_EOL;
$item = DB::table('bahan_bakus')->where('id', 1)->first();
echo "Main Satuan ID: " . $item->satuan_id . PHP_EOL;
echo "Sub Satuan 2 ID: " . $item->sub_satuan_2_id . PHP_EOL;
echo "Sub Satuan 2 Nilai: " . $item->sub_satuan_2_nilai . PHP_EOL;

// Check satuan names
echo PHP_EOL . "=== Satuan Names ===" . PHP_EOL;
$mainSatuan = DB::table('satuans')->where('id', $item->satuan_id)->first();
echo "Main Satuan: " . $mainSatuan->nama . PHP_EOL;

$subSatuan = DB::table('satuans')->where('id', $item->sub_satuan_2_id)->first();
echo "Sub Satuan 2: " . $subSatuan->nama . PHP_EOL;
