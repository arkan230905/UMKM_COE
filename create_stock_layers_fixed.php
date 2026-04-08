<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Creating missing stock layers for product ID 2...\n\n";

$product = \App\Models\Produk::find(2);
if (!$product) {
    echo "Product ID 2 not found!\n";
    exit;
}

echo "Product: {$product->nama_produk}\n";
echo "Current stock: {$product->stok}\n";

// Check if stock layers exist
$existingLayers = \App\Models\StockLayer::where('item_type', 'product')
    ->where('item_id', 2)
    ->count();

echo "Existing stock layers: {$existingLayers}\n";

if ($existingLayers > 0) {
    echo "Stock layers already exist. No action needed.\n";
    exit;
}

// Get stock movements to reconstruct layers
$movementIn = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->where('direction', 'in')
    ->orderBy('created_at', 'asc')
    ->first();

$movementOut = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->where('direction', 'out')
    ->orderBy('created_at', 'asc')
    ->get();

echo "\n=== RECONSTRUCTING STOCK LAYERS ===\n";

if ($movementIn) {
    echo "Found IN movement: {$movementIn->qty} at {$movementIn->created_at}\n";
    echo "Unit cost: {$movementIn->unit_cost}\n";
    echo "Total cost: {$movementIn->total_cost}\n";
    
    // Create stock layer from IN movement
    $layer = \App\Models\StockLayer::create([
        'item_type' => 'product',
        'item_id' => 2,
        'qty' => $movementIn->qty,
        'remaining_qty' => $movementIn->qty,
        'unit_cost' => $movementIn->unit_cost,
        'total_cost' => $movementIn->total_cost,
        'ref_type' => $movementIn->ref_type,
        'ref_id' => $movementIn->ref_id,
        'tanggal' => $movementIn->created_at,
        'created_at' => $movementIn->created_at,
        'updated_at' => $movementIn->created_at
    ]);
    
    echo "Created stock layer ID: {$layer->id}\n";
    
    // Process OUT movements
    $remainingQty = $movementIn->qty;
    foreach ($movementOut as $outMovement) {
        echo "\nProcessing OUT movement: {$outMovement->qty} at {$outMovement->created_at}\n";
        
        if ($outMovement->qty <= $remainingQty) {
            $layer->remaining_qty = $remainingQty - $outMovement->qty;
            $layer->save();
            $remainingQty -= $outMovement->qty;
            
            echo "Updated layer remaining_qty: {$layer->remaining_qty}\n";
        } else {
            echo "ERROR: OUT movement ({$outMovement->qty}) exceeds remaining stock ({$remainingQty})\n";
        }
    }
    
    echo "\nFinal remaining_qty: {$layer->remaining_qty}\n";
    
} else {
    echo "No IN movement found to reconstruct stock layers.\n";
}

echo "\n=== VERIFICATION ===\n";

// Check final state
$finalLayers = \App\Models\StockLayer::where('item_type', 'product')
    ->where('item_id', 2)
    ->get();

echo "Stock layers after reconstruction: " . $finalLayers->count() . "\n";
$totalRemaining = 0;
foreach ($finalLayers as $layer) {
    echo "Layer ID: {$layer->id}, Remaining: {$layer->remaining_qty}\n";
    $totalRemaining += $layer->remaining_qty;
}

echo "Total remaining from layers: {$totalRemaining}\n";
echo "Product stock in database: {$product->stok}\n";
echo "Match: " . ($totalRemaining == $product->stok ? 'YES' : 'NO') . "\n";

echo "\nDone.\n";
