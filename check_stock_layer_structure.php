<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Checking stock_layer table structure...\n\n";

// Get table structure
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('stock_layers');

echo "Stock layer table columns:\n";
foreach ($columns as $column) {
    echo "  - {$column}\n";
}

echo "\n=== CURRENT STOCK LAYER ===\n";

$stockLayer = \App\Models\StockLayer::where('item_type', 'product')
    ->where('item_id', 2)
    ->first();

if ($stockLayer) {
    echo "Found stock layer ID: {$stockLayer->id}\n";
    
    // Get all attributes
    $reflection = new ReflectionClass($stockLayer);
    $properties = $reflection->getProperties();
    
    echo "Stock layer attributes:\n";
    foreach ($properties as $property) {
        $property->setAccessible(true);
        $value = $property->getValue($stockLayer);
        echo "  {$property->getName()}: " . (is_null($value) ? 'NULL' : $value) . "\n";
    }
} else {
    echo "No stock layer found for product 2\n";
}

echo "\n=== RECREATING STOCK LAYER ===\n";

// Delete the broken stock layer
if ($stockLayer) {
    echo "Deleting broken stock layer...\n";
    $stockLayer->delete();
}

// Get stock movements to recreate layer
$stockIn = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->where('direction', 'in')
    ->orderBy('created_at', 'asc')
    ->first();

if ($stockIn) {
    echo "Found stock IN movement: {$stockIn->qty} at {$stockIn->created_at}\n";
    
    // Create new stock layer with correct structure
    try {
        $newLayer = \App\Models\StockLayer::create([
            'item_type' => 'product',
            'item_id' => 2,
            'remaining_qty' => $stockIn->qty,
            'unit_cost' => $stockIn->unit_cost,
            'total_cost' => $stockIn->total_cost,
            'ref_type' => $stockIn->ref_type,
            'ref_id' => $stockIn->ref_id,
            'tanggal' => $stockIn->created_at,
            'created_at' => $stockIn->created_at,
            'updated_at' => $stockIn->created_at
        ]);
        
        echo "Created new stock layer ID: {$newLayer->id}\n";
        
        // Process existing OUT movements
        $stockOut = \App\Models\StockMovement::where('item_type', 'product')
            ->where('item_id', 2)
            ->where('direction', 'out')
            ->orderBy('created_at', 'asc')
            ->get();
        
        $remainingQty = $stockIn->qty;
        foreach ($stockOut as $outMovement) {
            echo "Processing OUT movement: {$outMovement->qty}\n";
            
            if ($outMovement->qty <= $remainingQty) {
                $newLayer->remaining_qty = $remainingQty - $outMovement->qty;
                $newLayer->save();
                $remainingQty -= $outMovement->qty;
                
                echo "Updated remaining_qty to: {$newLayer->remaining_qty}\n";
            }
        }
        
        echo "Final remaining_qty: {$newLayer->remaining_qty}\n";
        
    } catch (\Exception $e) {
        echo "Error creating stock layer: " . $e->getMessage() . "\n";
    }
} else {
    echo "No stock IN movement found\n";
}

echo "\n=== VERIFICATION ===\n";

// Check final state
$finalLayers = \App\Models\StockLayer::where('item_type', 'product')
    ->where('item_id', 2)
    ->get();

echo "Final stock layers: " . $finalLayers->count() . "\n";
foreach ($finalLayers as $layer) {
    echo "Layer ID: {$layer->id}, Remaining: {$layer->remaining_qty}\n";
}

$available = \App\Models\StockLayer::where('item_type', 'product')
    ->where('item_id', 2)
    ->sum('remaining_qty');

echo "Available stock: {$available}\n";

// Test if we can consume 150 units
$requestedQty = 150;
echo "Requested quantity: {$requestedQty}\n";
echo "Can consume: " . ($requestedQty <= $available ? 'YES' : 'NO') . "\n";

echo "\nDone.\n";
