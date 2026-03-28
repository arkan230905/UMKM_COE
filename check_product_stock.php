<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Produk;
use App\Models\StockMovement;

echo "Checking stock calculation for Ayam Ketumbar...\n";

$produk = Produk::where('nama_produk', 'like', '%ayam%ketumbar%')->first();

if ($produk) {
    echo "Product: " . $produk->nama_produk . " (ID: " . $produk->id . ")\n";
    echo "Stok field: " . $produk->stok . "\n";
    
    // Calculate stock like the controller does - EXACTLY the same query
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
    
    echo "Stock movements calculation:\n";
    echo "  Stok masuk: " . $stokMasuk . "\n";
    echo "  Stok keluar: " . $stokKeluar . "\n";
    echo "  Stok tersedia: " . $stokTersedia . "\n";
    
    // Check all stock movements
    echo "\nAll stock movements:\n";
    $movements = StockMovement::where('item_id', $produk->id)->get();
    foreach ($movements as $movement) {
        echo "  " . $movement->tanggal . " - " . $movement->item_type . " - " . $movement->direction . ": " . $movement->qty . " (" . ($movement->keterangan ?? 'no memo') . ")\n";
    }
    
} else {
    echo "Product not found\n";
}