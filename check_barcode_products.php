<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING BARCODE PRODUCTS ===\n\n";

$totalProducts = \App\Models\Produk::count();
echo "Total produk: {$totalProducts}\n";

$productsWithBarcode = \App\Models\Produk::whereNotNull('barcode')
    ->where('barcode', '!=', '')
    ->count();
echo "Produk dengan barcode: {$productsWithBarcode}\n\n";

if ($productsWithBarcode > 0) {
    echo "Sample produk dengan barcode:\n";
    echo str_repeat("-", 80) . "\n";
    
    $products = \App\Models\Produk::whereNotNull('barcode')
        ->where('barcode', '!=', '')
        ->take(10)
        ->get(['id', 'nama_produk', 'barcode', 'stok', 'harga_jual']);
    
    foreach ($products as $p) {
        echo sprintf(
            "ID: %-5s | Nama: %-30s | Barcode: %-15s | Stok: %-5s | Harga: %s\n",
            $p->id,
            substr($p->nama_produk ?? 'N/A', 0, 30),
            $p->barcode,
            $p->stok ?? 0,
            number_format($p->harga_jual ?? 0, 0, ',', '.')
        );
    }
} else {
    echo "⚠️ TIDAK ADA PRODUK DENGAN BARCODE!\n";
    echo "Silakan tambahkan barcode ke produk di Master Data > Produk\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
