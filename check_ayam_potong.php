<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check Ayam Potong data (ID 1, not 5)
$item = \App\Models\BahanBaku::find(1);
if (!$item) {
    echo "Ayam Potong with ID 1 not found!" . PHP_EOL;
    exit;
}

echo "=== Ayam Potong Data ===" . PHP_EOL;
echo "ID: " . $item->id . PHP_EOL;
echo "Nama: " . $item->nama_bahan . PHP_EOL;
echo "Stok: " . $item->stok . PHP_EOL;
echo "Saldo Awal: " . $item->saldo_awal . PHP_EOL;
echo "Harga Satuan: " . $item->harga_satuan . PHP_EOL;
echo "Satuan ID: " . $item->satuan_id . PHP_EOL;
echo "Sub Satuan 2 ID: " . $item->sub_satuan_2_id . PHP_EOL;
echo "Sub Satuan 2 Nilai: " . $item->sub_satuan_2_nilai . PHP_EOL;

// Check production transactions
echo PHP_EOL . "=== Production Transactions for Ayam Potong ===" . PHP_EOL;
$productions = DB::table('produksis')
    ->join('produksi_details', 'produksis.id', '=', 'produksi_details.produksi_id')
    ->where('produksi_details.bahan_baku_id', 1)
    ->whereDate('produksis.tanggal', '2026-04-18')
    ->select(
        'produksis.id',
        'produksis.tanggal',
        'produksi_details.qty_konversi',
        'produksi_details.satuan_id',
        'produksi_details.harga_satuan',
        's.nama as satuan_nama'
    )
    ->join('satuans as s', 'produksi_details.satuan_id', '=', 's.id')
    ->get();

foreach ($productions as $prod) {
    echo "Tanggal: " . $prod->tanggal . PHP_EOL;
    echo "Jumlah: " . $prod->qty_konversi . PHP_EOL;
    echo "Satuan: " . $prod->satuan_nama . PHP_EOL;
    echo "Harga: " . $prod->harga_satuan . PHP_EOL;
    echo "---" . PHP_EOL;
}

// Check stock movements
echo PHP_EOL . "=== Stock Movements for Ayam Potong ===" . PHP_EOL;
$movements = DB::table('stock_movements')
    ->where('item_type', 'material')
    ->where('item_id', 1)
    ->whereDate('tanggal', '2026-04-18')
    ->get();

foreach ($movements as $movement) {
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
