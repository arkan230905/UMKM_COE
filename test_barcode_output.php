<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING BARCODE OUTPUT ===\n\n";

$produks = \App\Models\Produk::all();
$paketMenus = \App\Models\PaketMenu::with('details.produk')->where('status', 'aktif')->get();

echo "Total produk: " . $produks->count() . "\n";
echo "Total paket menu: " . $paketMenus->count() . "\n\n";

echo "=== PRODUCT DATA (JavaScript Object) ===\n";
echo "const productData = {\n";
foreach($produks as $p) {
    if($p->barcode) {
        $barcode = trim($p->barcode);
        $nama = addslashes($p->nama_produk ?? $p->nama);
        $harga = round($p->harga_jual ?? 0);
        $stok = $p->stok ?? 0;
        
        echo "    '{$barcode}': {\n";
        echo "        id: {$p->id},\n";
        echo "        nama: '{$nama}',\n";
        echo "        harga: {$harga},\n";
        echo "        stok: {$stok},\n";
        echo "        barcode: '{$barcode}'\n";
        echo "    },\n";
    }
}
echo "};\n\n";

echo "=== SEARCHABLE PRODUCTS (JavaScript Array) ===\n";
echo "const searchableProducts = [\n";
foreach($produks as $p) {
    $barcode = trim($p->barcode ?? '');
    $nama = addslashes($p->nama_produk ?? $p->nama);
    $harga = round($p->harga_jual ?? 0);
    $stok = $p->stok ?? 0;
    $searchText = strtolower(addslashes($p->nama_produk ?? $p->nama)) . ' ' . $barcode;
    
    echo "    {\n";
    echo "        id: {$p->id},\n";
    echo "        nama: '{$nama}',\n";
    echo "        harga: {$harga},\n";
    echo "        stok: {$stok},\n";
    echo "        barcode: '{$barcode}',\n";
    echo "        type: 'produk',\n";
    echo "        searchText: '{$searchText}'.toLowerCase()\n";
    echo "    },\n";
}
echo "];\n\n";

echo "=== SUMMARY ===\n";
$productsWithBarcode = $produks->filter(function($p) {
    return !empty($p->barcode);
});
echo "Products with barcode: " . $productsWithBarcode->count() . "\n";
echo "Products without barcode: " . ($produks->count() - $productsWithBarcode->count()) . "\n";

if ($productsWithBarcode->count() > 0) {
    echo "\nBarcodes in productData:\n";
    foreach($productsWithBarcode as $p) {
        echo "  - '{$p->barcode}' => {$p->nama_produk}\n";
    }
}
