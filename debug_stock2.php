<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check stock layers structure
echo "=== STOCK LAYERS TABLE STRUCTURE ===\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('stock_layers');
foreach ($columns as $column) {
    echo "- " . $column . "\n";
}

// Check stock layers for Ayam Potong
echo "\n=== STOCK LAYERS FOR AYAM POTONG ===\n";
$bahan = \App\Models\BahanBaku::where('nama_bahan', 'Ayam Potong')->first();

if ($bahan) {
    $stockLayers = \App\Models\StockLayer::where('item_type', 'material')
        ->where('item_id', $bahan->id)
        ->orderBy('created_at')
        ->get();
    
    echo "Jumlah Stock Layers: " . $stockLayers->count() . "\n";
    foreach ($stockLayers as $layer) {
        echo "Layer " . $layer->id . ": qty=" . $layer->qty . ", unit_price=" . $layer->unit_price . "\n";
    }
}

// Test stock service getAvailableQty
echo "\n=== TEST STOCK SERVICE ===\n";
$stock = new \App\Services\StockService();
$available = $stock->getAvailableQty('material', $bahan->id);
echo "Available Qty from StockService: " . $available . "\n";

// Test unit conversion
echo "\n=== TEST UNIT CONVERSION ===\n";
$converter = new \App\Services\UnitConverter();
$qtyBase = $converter->convert(100, 'Gram', 'Kilogram');
echo "100 Gram = " . $qtyBase . " Kilogram\n";

// Test production calculation
echo "\n=== PRODUCTION CALCULATION TEST ===\n";
$qtyProd = 1; // 1 unit produksi
$qtyPerUnit = 100; // 100 gram per unit
$qtyResepTotal = $qtyPerUnit * $qtyProd; // 100 gram
$qtyBase = $converter->convert($qtyResepTotal, 'Gram', 'Kilogram'); // 0.1 kg
echo "Qty Produksi: " . $qtyProd . "\n";
echo "Qty per Unit: " . $qtyPerUnit . " Gram\n";
echo "Qty Resep Total: " . $qtyResepTotal . " Gram\n";
echo "Qty Base (Kilogram): " . $qtyBase . "\n";
echo "Stok Tersedia: " . $bahan->stok . " Kilogram\n";
echo "Cukup? " . ($bahan->stok >= $qtyBase ? "YA" : "TIDAK") . "\n";
