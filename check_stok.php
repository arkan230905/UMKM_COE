<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Stock Movements untuk Ayam Kampung ===\n";
$movements = DB::table('stock_movements')
    ->where('item_id', 2) // ID Ayam Kampung
    ->where('item_type', 'material')
    ->whereDate('tanggal', '2026-02-02')
    ->get();

foreach ($movements as $movement) {
    echo "Tanggal: {$movement->tanggal}, Direction: {$movement->direction}, Qty: {$movement->qty}\n";
}

echo "\n=== Total Pembelian Tanggal 2026-02-02 ===\n";
$totalPembelian = DB::table('stock_movements')
    ->where('item_id', 2)
    ->where('item_type', 'material')
    ->where('direction', 'in')
    ->whereDate('tanggal', '2026-02-02')
    ->sum('qty');

echo "Total pembelian: $totalPembelian\n";
?>
