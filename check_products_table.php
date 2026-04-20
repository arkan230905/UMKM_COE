<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CHECKING PRODUCTS FOR KELOLA FOTO ===\n";
$products = \App\Models\Produk::take(3)->get();
echo "Total products: " . $products->count() . "\n";

foreach ($products as $product) {
    echo "- Product: {$product->nama_produk}\n";
    echo "  Photo: " . ($product->foto ? 'Yes' : 'No') . "\n";
    if ($product->foto) {
        echo "  Photo path: {$product->foto}\n";
    }
}

echo "\n=== CHECKING IF TABLE SHOWS PRODUCTS ===\n";
echo "If you can't see the 'Kelola Foto' column, try:\n";
echo "1. Scroll the table horizontally to the right\n";
echo "2. Clear browser cache (Ctrl + Shift + R)\n";
echo "3. Check if products are being displayed in the table\n";