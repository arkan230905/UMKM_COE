<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check produksi_details table structure
echo "=== Produksi Details Table Structure ===" . PHP_EOL;
$columns = DB::select('DESCRIBE produksi_details');
foreach ($columns as $column) {
    echo $column->Field . " - " . $column->Type . PHP_EOL;
}

// Check production transactions for Ayam Potong on 2026-04-18
echo PHP_EOL . "=== Production Transactions for Ayam Potong on 2026-04-18 ===" . PHP_EOL;
$productions = DB::table('produksi_details')
    ->join('produksis', 'produksi_details.produksi_id', '=', 'produksis.id')
    ->where('produksi_details.bahan_baku_id', 1)
    ->whereDate('produksis.tanggal', '2026-04-18')
    ->get();

foreach ($productions as $prod) {
    echo "Produksi ID: " . $prod->produksi_id . PHP_EOL;
    echo "Tanggal: " . $prod->tanggal . PHP_EOL;
    echo "Qty Resep: " . $prod->qty_resep . PHP_EOL;
    echo "Satuan Resep: " . $prod->satuan_resep . PHP_EOL;
    echo "Qty Konversi: " . $prod->qty_konversi . PHP_EOL;
    echo "Harga Satuan: " . $prod->harga_satuan . PHP_EOL;
    echo "Subtotal: " . $prod->subtotal . PHP_EOL;
    echo "---" . PHP_EOL;
}
