<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$produks = App\Models\Produk::all();
foreach ($produks as $p) {
    $nama = addslashes($p->nama_produk ?? $p->nama ?? '');
    $barcode = trim($p->barcode ?? '');
    $harga = round($p->harga_jual ?? 0);
    $stok = $p->stok ?? 0;
    echo "id:{$p->id} | nama_raw:{$p->nama_produk} | nama_escaped:{$nama} | barcode:[{$barcode}] | harga:{$harga} | stok:{$stok}\n";
    
    // Simulate JS object key
    echo "  JS key: '{$barcode}'\n";
    echo "  JS value: { id: {$p->id}, nama: '{$nama}', harga: {$harga}, stok: {$stok} }\n";
}
