<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Produk;
use App\Models\StockLayer;

echo "=== Checking Product #2 Stock ===\n";

$produk = Produk::find(2);
if ($produk) {
    echo "Product: {$produk->nama_produk}\n";
    echo "System stock: {$produk->stok}\n";
    
    $stockLayers = StockLayer::where('item_type', 'product')
        ->where('item_id', 2)
        ->get();
    
    echo "Stock layers count: " . $stockLayers->count() . "\n";
    
    $totalRealtime = $stockLayers->sum('remaining_qty');
    echo "Realtime stock: {$totalRealtime}\n";
    
    echo "\nStock layers:\n";
    foreach ($stockLayers as $layer) {
        echo "- ID: {$layer->id}, qty: {$layer->qty}, remaining: {$layer->remaining_qty}, cost: {$layer->unit_cost}, ref: {$layer->ref_type}\n";
    }
    
    if ($produk->stok > 0 && $totalRealtime == 0) {
        echo "\n=== SOLUTION: Sync system stock to StockLayer ===\n";
        
        // Create stock layer from system stock
        $newLayer = StockLayer::create([
            'item_type' => 'product',
            'item_id' => 2,
            'qty' => $produk->stok,
            'remaining_qty' => $produk->stok,
            'unit_cost' => $produk->harga_bom ?? 0,
            'ref_type' => 'stock_sync',
            'ref_id' => 0,
            'tanggal' => now(),
        ]);
        
        echo "Created new stock layer ID: {$newLayer->id}\n";
        echo "New realtime stock: {$produk->stok}\n";
    }
    
} else {
    echo "Product #2 not found!\n";
}