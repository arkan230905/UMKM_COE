<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Debugging stock issue for product ID 2...\n\n";

// Check product ID 2
$product = \App\Models\Produk::find(2);
if (!$product) {
    echo "Product ID 2 not found!\n";
    exit;
}

echo "=== PRODUCT ID 2 ===\n";
echo "Nama: {$product->nama_produk}\n";
echo "Current Stock: {$product->stok}\n";

// Check stock layers
echo "\n=== STOCK LAYERS ===\n";
$stockLayers = \App\Models\StockLayer::where('item_type', 'product')
    ->where('item_id', 2)
    ->orderBy('created_at', 'desc')
    ->get();

echo "Stock layers found: " . $stockLayers->count() . "\n";
$totalRemaining = 0;

foreach ($stockLayers as $layer) {
    echo "Layer ID: {$layer->id}\n";
    echo "  Qty: {$layer->qty}\n";
    echo "  Remaining: {$layer->remaining_qty}\n";
    echo "  Unit Cost: {$layer->unit_cost}\n";
    echo "  Total Cost: {$layer->total_cost}\n";
    echo "  Ref: {$layer->ref_type}:{$layer->ref_id}\n";
    echo "  Created: {$layer->created_at}\n\n";
    $totalRemaining += $layer->remaining_qty;
}

echo "Total remaining from layers: {$totalRemaining}\n";

// Check stock movements
echo "\n=== STOCK MOVEMENTS ===\n";
$stockMovements = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

echo "Recent movements (last 10):\n";
foreach ($stockMovements as $movement) {
    echo "  " . $movement->created_at . " - {$movement->direction} {$movement->qty} (ref: {$movement->ref_type}:{$movement->ref_id})\n";
}

// Check if there are any pending transactions that might be affecting stock
echo "\n=== RECENT PENJUALAN ===\n";
$recentPenjualan = \App\Models\Penjualan::whereDate('tanggal', '2026-04-08')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();

foreach ($recentPenjualan as $penjualan) {
    echo "Penjualan ID: {$penjualan->id} - {$penjualan->nomor_penjualan}\n";
    
    $details = \App\Models\PenjualanDetail::where('penjualan_id', $penjualan->id)
        ->where('produk_id', 2)
        ->get();
    
    foreach ($details as $detail) {
        echo "  Detail: Produk {$detail->produk_id}, Qty: {$detail->jumlah}\n";
    }
}

// Check the specific failing transaction
echo "\n=== CHECKING FAILING TRANSACTION ===\n";
$failingPenjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260408-002')->first();

if ($failingPenjualan) {
    echo "Found failing penjualan: ID {$failingPenjualan->id}\n";
    echo "Status: " . ($failingPenjualan->wasRecentlyCreated ? 'Recently created' : 'Existing') . "\n";
    
    $details = \App\Models\PenjualanDetail::where('penjualan_id', $failingPenjualan->id)
        ->where('produk_id', 2)
        ->get();
    
    foreach ($details as $detail) {
        echo "  Detail: Produk {$detail->produk_id}, Qty: {$detail->jumlah}\n";
    }
    
    // Check if stock movements already exist for this penjualan
    $existingMovements = \App\Models\StockMovement::where('ref_type', 'sale')
        ->where('ref_id', $failingPenjualan->id)
        ->count();
    
    echo "  Existing stock movements: {$existingMovements}\n";
} else {
    echo "Failing penjualan not found in database\n";
}

echo "\nDone.\n";
