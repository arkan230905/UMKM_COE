<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Produk;

echo "Memeriksa harga_jual di tabel produks...\n\n";

$produks = Produk::all();

foreach ($produks as $p) {
    echo "Produk: {$p->nama_produk}\n";
    echo "  Harga Jual: Rp " . number_format($p->harga_jual ?? 0, 0, ',', '.') . "\n";
    echo "  Harga BOM: Rp " . number_format($p->harga_bom ?? 0, 0, ',', '.') . "\n";
    echo "  Harga Beli: Rp " . number_format($p->harga_beli ?? 0, 0, ',', '.') . "\n";
    echo "  HPP: Rp " . number_format($p->hpp ?? 0, 0, ',', '.') . "\n";
    echo "\n";
}
