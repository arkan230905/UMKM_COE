<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Cek Pembelian ID 2 ===\n";

$pembelian = \DB::table('pembelians')->where('id', 2)->first();

if ($pembelian) {
    echo "ID: {$pembelian->id}\n";
    echo "Nomor Pembelian: {$pembelian->nomor_pembelian}\n";
    echo "Tanggal: {$pembelian->tanggal}\n";
    echo "Payment Method: {$pembelian->payment_method}\n";
    echo "Bank ID: {$pembelian->bank_id}\n";
    echo "Status: {$pembelian->status}\n";
    echo "Total: {$pembelian->total}\n";
    echo "Total Harga: {$pembelian->total_harga}\n";
    echo "Terbayar: {$pembelian->terbayar}\n";
    echo "Sisa Pembayaran: {$pembelian->sisa_pembayaran}\n";
} else {
    echo "Pembelian ID 2 tidak ditemukan\n";
}

echo "\n=== Cek Pembelian dengan Total > 0 ===\n";
$pembelianWithTotal = \DB::table('pembelians')->where('total', '>', 0)->get();

echo "Pembelian dengan total > 0: " . $pembelianWithTotal->count() . "\n";
foreach ($pembelianWithTotal as $p) {
    echo "- ID: {$p->id}, Tanggal: {$p->tanggal}, Total: {$p->total}, Payment: {$p->payment_method}, Bank: {$p->bank_id}\n";
}
