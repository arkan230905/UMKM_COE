<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Produk;

echo "Testing stock validation logic...\n";

$produk = Produk::find(1); // Ayam Ketumbar
$qty = 5; // Quantity to test

echo "Product: " . $produk->nama_produk . "\n";
echo "Requested qty: " . $qty . "\n";

// Test validation 1: Stock movements
$stokMasuk = \DB::table('stock_movements')
    ->where('item_type', 'product')
    ->where('item_id', $produk->id)
    ->where('direction', 'in')
    ->sum('qty');

$stokKeluar = \DB::table('stock_movements')
    ->where('item_type', 'product')
    ->where('item_id', $produk->id)
    ->where('direction', 'out')
    ->sum('qty');

$stokTersedia = $stokMasuk - $stokKeluar;

echo "\nValidation 1 (Stock Movements):\n";
echo "  Stok masuk: " . $stokMasuk . "\n";
echo "  Stok keluar: " . $stokKeluar . "\n";
echo "  Stok tersedia: " . $stokTersedia . "\n";
echo "  Validation: " . ($qty > $stokTersedia ? "FAIL" : "PASS") . "\n";

// Test validation 2: Product stok field
$prodStok = (int)($produk->stok ?? 0);

echo "\nValidation 2 (Product Field):\n";
echo "  Product stok: " . $prodStok . "\n";
echo "  Validation: " . ($prodStok < $qty ? "FAIL" : "PASS") . "\n";

// Check if there are any stock movements with wrong item_type
echo "\nChecking for wrong item_type in stock_movements:\n";
$wrongMovements = \DB::table('stock_movements')
    ->where('item_id', $produk->id)
    ->where('item_type', '!=', 'product')
    ->get();

foreach ($wrongMovements as $movement) {
    echo "  Wrong item_type: " . $movement->item_type . " (should be 'product')\n";
}