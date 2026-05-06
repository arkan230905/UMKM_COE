<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Produk;

echo "🔍 Checking Produk Data\n";
echo "=======================\n\n";

$produk = Produk::find(2);

if ($produk) {
    echo "✅ Produk Found (ID: 2)\n\n";
    
    echo "Field Values:\n";
    echo "-------------\n";
    echo "ID: " . $produk->id . "\n";
    echo "Kode Produk: " . ($produk->kode_produk ?? 'NULL') . "\n";
    echo "Nama Produk: " . ($produk->nama_produk ?? 'NULL') . "\n";
    echo "Barcode: " . ($produk->barcode ?? 'NULL') . "\n";
    echo "Satuan ID: " . ($produk->satuan_id ?? 'NULL') . "\n";
    echo "Harga Jual: " . ($produk->harga_jual ?? 'NULL') . "\n";
    echo "Stok: " . ($produk->stok ?? 'NULL') . "\n";
    echo "Harga Pokok: " . ($produk->harga_pokok ?? 'NULL') . "\n";
    
    echo "\nRelationship Check:\n";
    echo "-------------------\n";
    
    if ($produk->satuan) {
        echo "Satuan: " . $produk->satuan->nama . "\n";
    } else {
        echo "Satuan: NULL (relationship not loaded or satuan_id is NULL)\n";
    }
    
    echo "\nFormatted Display:\n";
    echo "------------------\n";
    echo "Kode: " . ($produk->kode_produk ?? '-') . "\n";
    echo "Harga Jual: Rp " . number_format($produk->harga_jual ?? 0, 0, ',', '.') . "\n";
    echo "Stok: " . number_format($produk->stok ?? 0, 0, ',', '.') . "\n";
    
    echo "\n✅ Data produk tersedia di database\n";
    echo "✅ Field kode_produk: " . ($produk->kode_produk ? 'Ada' : 'Kosong') . "\n";
    echo "✅ Field harga_jual: " . ($produk->harga_jual > 0 ? 'Ada' : 'Kosong/0') . "\n";
    
} else {
    echo "❌ Produk ID 2 tidak ditemukan\n";
}

?>