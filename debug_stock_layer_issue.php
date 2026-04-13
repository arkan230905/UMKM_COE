<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Debugging stock layer synchronization issue...\n\n";

// Check current state
echo "=== CURRENT STATE ===\n";

$product = \App\Models\Produk::find(2);
echo "Product stock in database: {$product->stok}\n";

// Check stock layers
$stockLayers = \App\Models\StockLayer::where('item_type', 'product')
    ->where('item_id', 2)
    ->get();

echo "Stock layers found: " . $stockLayers->count() . "\n";
foreach ($stockLayers as $layer) {
    echo "Layer ID: {$layer->id}\n";
    echo "  Qty: {$layer->qty}\n";
    echo "  Remaining: {$layer->remaining_qty}\n";
    echo "  Unit Cost: {$layer->unit_cost}\n";
    echo "  Ref: {$layer->ref_type}:{$layer->ref_id}\n";
    echo "  Created: {$layer->created_at}\n\n";
}

// Check stock movements
echo "=== STOCK MOVEMENTS ===\n";
$stockMovements = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->orderBy('created_at', 'asc')
    ->get();

echo "Stock movements found: " . $stockMovements->count() . "\n";
foreach ($stockMovements as $movement) {
    echo "Movement ID: {$movement->id}\n";
    echo "  Direction: {$movement->direction}\n";
    echo "  Qty: {$movement->qty}\n";
    echo "  Ref: {$movement->ref_type}:{$movement->ref_id}\n";
    echo "  Created: {$movement->created_at}\n\n";
}

// Check available stock from layers
$available = \App\Models\StockLayer::where('item_type', 'product')
    ->where('item_id', 2)
    ->sum('remaining_qty');

echo "Available from stock layers: {$available}\n";

// Check if there are any penjualan that were created but failed stock movement
echo "=== PENJUALAN ANALYSIS ===\n";

$recentPenjualan = \App\Models\Penjualan::whereDate('tanggal', '2026-04-08')
    ->orderBy('id', 'desc')
    ->get();

foreach ($recentPenjualan as $penjualan) {
    echo "Penjualan ID: {$penjualan->id} - {$penjualan->nomor_penjualan}\n";
    
    $details = \App\Models\PenjualanDetail::where('penjualan_id', $penjualan->id)
        ->where('produk_id', 2)
        ->get();
    
    foreach ($details as $detail) {
        echo "  Detail: Qty {$detail->jumlah}\n";
    }
    
    // Check if stock movements exist
    $movementCount = \App\Models\StockMovement::where('ref_type', 'sale')
        ->where('ref_id', $penjualan->id)
        ->count();
    
    echo "  Stock movements: {$movementCount}\n";
    
    // Check if penjualan exists in database
    $penjualanExists = \App\Models\Penjualan::find($penjualan->id);
    echo "  Penjualan exists: " . ($penjualanExists ? 'YES' : 'NO') . "\n";
    
    echo "\n";
}

// The issue: stock layer not being updated when stock movement is created
echo "=== ISSUE ANALYSIS ===\n";
echo "Problem: Stock layer remaining_qty is not being updated when stock movements are created\n";
echo "Expected: Stock layer should have remaining_qty = 155\n";
echo "Actual: Stock layer has remaining_qty = 5 (only 5 left after 5 unit sale)\n";
echo "Missing: 150 units that should be available\n";

// Check if there are multiple layers that should be collapsed
echo "\n=== CHECKING FOR MULTIPLE LAYERS ===\n";
$allLayers = \App\Models\StockLayer::where('item_type', 'product')
    ->where('item_id', 2)
    ->orderBy('created_at', 'asc')
    ->get();

echo "All layers for product 2:\n";
foreach ($allLayers as $layer) {
    echo "  Layer {$layer->id}: {$layer->remaining_qty} remaining (created: {$layer->created_at})\n";
}

// Check if there are any failed penjualan transactions that created stock movements but didn't update layers
echo "\n=== CHECKING FAILED TRANSACTIONS ===\n";
$failedMovements = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->where('direction', 'out')
    ->where('created_at', '>', '2026-04-08 11:30:00')
    ->get();

echo "Stock movements after 11:30: " . $failedMovements->count() . "\n";
foreach ($failedMovements as $movement) {
    echo "  Movement {$movement->id}: {$movement->qty} units at {$movement->created_at}\n";
}

echo "\nDone.\n";
