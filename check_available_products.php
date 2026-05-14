<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK AVAILABLE PRODUCTS ===" . PHP_EOL;

$produkList = \App\Models\Produk::limit(5)->get();
foreach ($produkList as $produk) {
    echo "- " . $produk->nama_produk . " (HPP: " . $produk->hpp . ", coa_persediaan_id: " . ($produk->coa_persediaan_id ?? 'NULL') . ")" . PHP_EOL;
}
