<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Data Saat Ini ===\n";
$movements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 2)
    ->orderBy('tanggal', 'asc')
    ->orderBy('id', 'asc')
    ->get();

foreach ($movements as $m) {
    echo "Tanggal: {$m->tanggal}, Type: {$m->ref_type}, Direction: {$m->direction}, Qty: {$m->qty} {$m->satuan}, Total: Rp " . number_format($m->total_cost, 0) . "\n";
}

echo "\n=== Masalahnya ===\n";
echo "1. Adjustment muncul sebagai pembelian\n";
echo "2. Saldo awal bulan Februari seharusnya di 01/02, bukan 31/01\n";

echo "\n=== Solusi ===\n";
echo "1. Adjustment harus di kolom Produksi (bukan Pembelian)\n";
echo "2. Tambah baris khusus untuk saldo awal bulan (tanpa transaksi)\n";
