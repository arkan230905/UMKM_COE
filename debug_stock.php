<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check Ayam Potong stock
$bahan = \App\Models\BahanBaku::where('nama_bahan', 'Ayam Potong')->first();

if ($bahan) {
    echo "=== DATA BAHAN AYAM POTONG ===\n";
    echo "ID: " . $bahan->id . "\n";
    echo "Nama: " . $bahan->nama_bahan . "\n";
    echo "Stok: " . $bahan->stok . "\n";
    echo "Satuan: " . ($bahan->satuan->nama ?? 'N/A') . "\n";
    echo "Harga Satuan: " . $bahan->harga_satuan . "\n";
    echo "Kode: " . ($bahan->kode_bahan ?? 'N/A') . "\n";
} else {
    echo "Bahan Ayam Potong tidak ditemukan\n";
}

// Check BOM details for produk id 1
echo "\n=== BOM DETAILS FOR PRODUK ID 1 ===\n";
$bom = \App\Models\Bom::where('produk_id', 1)->with('details.bahanBaku')->first();

if ($bom) {
    echo "BOM ID: " . $bom->id . "\n";
    echo "Jumlah Details: " . $bom->details->count() . "\n";
    
    foreach ($bom->details as $detail) {
        echo "\nDetail ID: " . $detail->id . "\n";
        echo "Bahan: " . ($detail->bahanBaku->nama_bahan ?? 'N/A') . "\n";
        echo "Jumlah: " . $detail->jumlah . "\n";
        echo "Satuan: " . $detail->satuan . "\n";
        echo "Harga per Satuan: " . $detail->harga_per_satuan . "\n";
        echo "Total Harga: " . $detail->total_harga . "\n";
    }
} else {
    echo "BOM untuk produk id 1 tidak ditemukan\n";
}

// Check stock layers
echo "\n=== STOCK LAYERS FOR AYAM POTONG ===\n";
if ($bahan) {
    $stockLayers = \App\Models\StockLayer::where('item_type', 'material')
        ->where('item_id', $bahan->id)
        ->where('qty_available', '>', 0)
        ->orderBy('created_at')
        ->get();
    
    echo "Jumlah Stock Layers: " . $stockLayers->count() . "\n";
    $totalAvailable = 0;
    foreach ($stockLayers as $layer) {
        echo "Layer " . $layer->id . ": " . $layer->qty_available . " @ " . $layer->unit_price . "\n";
        $totalAvailable += $layer->qty_available;
    }
    echo "Total Available: " . $totalAvailable . "\n";
}
