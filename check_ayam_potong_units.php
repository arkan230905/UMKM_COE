<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Ayam Potong Unit Configuration ===" . PHP_EOL;

// Get Ayam Potong data
$item = DB::table('bahan_bakus')->where('id', 1)->first();

echo "Ayam Potong Configuration:" . PHP_EOL;
echo "Main Satuan ID: " . $item->satuan_id . PHP_EOL;
echo "Sub Satuan 1 ID: " . ($item->sub_satuan_1_id ?? 'NULL') . PHP_EOL;
echo "Sub Satuan 2 ID: " . ($item->sub_satuan_2_id ?? 'NULL') . PHP_EOL;
echo "Sub Satuan 3 ID: " . ($item->sub_satuan_3_id ?? 'NULL') . PHP_EOL;

echo PHP_EOL . "Satuan Values:" . PHP_EOL;
echo "Sub Satuan 1 Nilai: " . ($item->sub_satuan_1_nilai ?? 'NULL') . PHP_EOL;
echo "Sub Satuan 2 Nilai: " . ($item->sub_satuan_2_nilai ?? 'NULL') . PHP_EOL;
echo "Sub Satuan 3 Nilai: " . ($item->sub_satuan_3_nilai ?? 'NULL') . PHP_EOL;

// Get all satuan details
echo PHP_EOL . "=== Satuan Details ===" . PHP_EOL;

$satuans = [
    'main' => $item->satuan_id,
    'sub1' => $item->sub_satuan_1_id,
    'sub2' => $item->sub_satuan_2_id,
    'sub3' => $item->sub_satuan_3_id
];

foreach ($satuans as $key => $satuanId) {
    if ($satuanId) {
        $satuan = DB::table('satuans')->where('id', $satuanId)->first();
        echo $key . ": " . $satuan->nama . " (ID: " . $satuan->id . ")" . PHP_EOL;
    } else {
        echo $key . ": NULL" . PHP_EOL;
    }
}

// Check if Gram exists in system
echo PHP_EOL . "=== Check if Gram Exists in System ===" . PHP_EOL;
$gramSatuan = DB::table('satuans')->where('nama', 'LIKE', '%gram%')->get();
echo "Found " . $gramSatuan->count() . " satuan with 'gram' in name:" . PHP_EOL;
foreach ($gramSatuan as $gram) {
    echo "- " . $gram->nama . " (ID: " . $gram->id . ")" . PHP_EOL;
}

// Check if Ons exists in system
echo PHP_EOL . "=== Check if Ons Exists in System ===" . PHP_EOL;
$onsSatuan = DB::table('satuans')->where('nama', 'LIKE', '%ons%')->get();
echo "Found " . $onsSatuan->count() . " satuan with 'ons' in name:" . PHP_EOL;
foreach ($onsSatuan as $ons) {
    echo "- " . $ons->nama . " (ID: " . $ons->id . ")" . PHP_EOL;
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;
echo "Current Ayam Potong units:" . PHP_EOL;
echo "- Main: " . DB::table('satuans')->where('id', $item->satuan_id)->first()->nama . PHP_EOL;
if ($item->sub_satuan_2_id) {
    echo "- Sub 2: " . DB::table('satuans')->where('id', $item->sub_satuan_2_id)->first()->nama . PHP_EOL;
}

echo PHP_EOL . "Issue: User is viewing 'Satuan Gram' but Ayam Potong doesn't have Gram configured!" . PHP_EOL;
echo "The system is likely showing a default/fallback unit instead of the actual configured units." . PHP_EOL;
