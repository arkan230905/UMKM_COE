<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG BARCODE PRODUCTS ===\n";

// Cek produk yang punya barcode
$produks = \App\Models\Produk::whereNotNull('barcode')
    ->where('barcode', '!=', '')
    ->take(10)
    ->get(['id', 'nama_produk', 'barcode', 'harga_jual', 'stok']);

echo "Jumlah produk dengan barcode: " . $produks->count() . "\n\n";

if ($produks->count() > 0) {
    echo "Daftar produk dengan barcode:\n";
    echo "ID\tNama\t\tBarcode\t\tHarga\tStok\n";
    echo "------------------------------------------------\n";
    
    foreach ($produks as $p) {
        echo $p->id . "\t" . 
             substr($p->nama_produk ?? $p->nama, 0, 15) . "\t" . 
             ($p->barcode ?? '-') . "\t\t" . 
             "Rp" . number_format($p->harga_jual ?? 0) . "\t" . 
             ($p->stok ?? 0) . "\n";
    }
} else {
    echo "TIDAK ADA produk dengan barcode di database!\n";
    echo "\nMenambahkan contoh barcode untuk testing...\n";
    
    // Update beberapa produk dengan barcode contoh
    $produkSample = \App\Models\Produk::take(3)->get();
    $barcodes = ['1234567890123', '2345678901234', '3456789012345'];
    
    foreach ($produkSample as $index => $p) {
        $p->barcode = $barcodes[$index] ?? '9999999999999';
        $p->save();
        echo "Updated: " . $p->nama_produk . " -> " . $p->barcode . "\n";
    }
}

echo "\n=== TEST API ENDPOINT ===\n";

// Test API endpoint
if ($produks->count() > 0) {
    $testProduct = $produks->first();
    $barcode = $testProduct->barcode;
    
    echo "Testing barcode: $barcode\n";
    
    // Simulate API call
    $foundProduct = \App\Models\Produk::where('barcode', $barcode)->first();
    
    if ($foundProduct) {
        echo "PRODUK DITEMUKAN:\n";
        echo "- ID: " . $foundProduct->id . "\n";
        echo "- Nama: " . ($foundProduct->nama_produk ?? $foundProduct->nama) . "\n";
        echo "- Barcode: " . $foundProduct->barcode . "\n";
        echo "- Harga: " . $foundProduct->harga_jual . "\n";
        echo "- Stok: " . ($foundProduct->stok ?? 0) . "\n";
        
        // Calculate stok tersedia
        $stokMasuk = \DB::table('stock_movements')
            ->where('item_type', 'product')
            ->where('item_id', $foundProduct->id)
            ->where('direction', 'in')
            ->sum('qty');
        
        $stokKeluar = \DB::table('stock_movements')
            ->where('item_type', 'product')
            ->where('item_id', $foundProduct->id)
            ->where('direction', 'out')
            ->sum('qty');
        
        $stokTersedia = $stokMasuk - $stokKeluar;
        echo "- Stok tersedia: " . $stokTersedia . "\n";
    } else {
        echo "PRODUK TIDAK DITEMUKAN\n";
    }
}
