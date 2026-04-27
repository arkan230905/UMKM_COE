<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PenjualanDetail;

echo "Memeriksa harga_satuan di penjualan_details...\n\n";

$details = PenjualanDetail::with('produk')->get();

foreach ($details as $d) {
    $produkNama = $d->produk ? $d->produk->nama_produk : 'N/A';
    $hargaJual = $d->produk ? $d->produk->harga_jual : 0;
    
    echo "Detail ID: {$d->id}\n";
    echo "  Produk: {$produkNama}\n";
    echo "  Harga Satuan (di detail): Rp " . number_format($d->harga_satuan, 0, ',', '.') . "\n";
    echo "  Harga Jual (di produk): Rp " . number_format($hargaJual, 0, ',', '.') . "\n";
    echo "  Jumlah: {$d->jumlah}\n";
    echo "  Subtotal: Rp " . number_format($d->subtotal, 0, ',', '.') . "\n";
    echo "\n";
}
