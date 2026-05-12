<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Final fix for stock layer synchronization...\n\n";

// Delete the broken stock layer
echo "=== DELETING BROKEN STOCK LAYER ===\n";
$stockLayer = \App\Models\StockLayer::where('item_type', 'product')
    ->where('item_id', 2)
    ->first();

if ($stockLayer) {
    echo "Deleting stock layer ID: {$stockLayer->id}\n";
    $stockLayer->delete();
} else {
    echo "No stock layer found for product 2\n";
}

// Get stock movements to recreate layer
echo "\n=== RECREATING STOCK LAYER ===\n";
$stockIn = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->where('direction', 'in')
    ->orderBy('created_at', 'asc')
    ->first();

if ($stockIn) {
    echo "Found stock IN movement: {$stockIn->qty} at {$stockIn->created_at}\n";
    echo "Unit cost: {$stockIn->unit_cost}\n";
    echo "Total cost: {$stockIn->total_cost}\n";
    
    // Create new stock layer
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
        echo "\n=== PROCESSING OUT MOVEMENTS ===\n";
        $stockOut = \App\Models\StockMovement::where('item_type', 'product')
            ->where('item_id', 2)
            ->where('direction', 'out')
            ->orderBy('created_at', 'asc')
            ->get();
        
        $remainingQty = $stockIn->qty;
        foreach ($stockOut as $outMovement) {
            echo "Processing OUT movement: {$outMovement->qty} at {$outMovement->created_at}\n";
            
            if ($outMovement->qty <= $remainingQty) {
                $newLayer->remaining_qty = $remainingQty - $outMovement->qty;
                $newLayer->save();
                $remainingQty -= $outMovement->qty;
                
                echo "Updated remaining_qty to: {$newLayer->remaining_qty}\n";
            } else {
                echo "ERROR: OUT movement ({$outMovement->qty}) exceeds remaining stock ({$remainingQty})\n";
            }
        }
        
        echo "Final remaining_qty: {$newLayer->remaining_qty}\n";
        
    } catch (\Exception $e) {
        echo "Error creating stock layer: " . $e->getMessage() . "\n";
        echo "Trace: " . $e->getTraceAsString() . "\n";
    }
} else {
    echo "No stock IN movement found\n";
}

// Verification
echo "\n=== VERIFICATION ===\n";

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

if ($requestedQty <= $available) {
    echo "\n=== TESTING CONSUME ===\n";
    
    try {
        $stockService = new \App\Services\StockService();
        
        // Test consume operation
        $cogs = $stockService->consume('product', 2, $requestedQty, 'pcs', 'test_sale', 999, '2026-04-08');
        
        echo "Consume successful! COGS: {$cogs}\n";
        
        // Check remaining stock
        $newAvailable = \App\Models\StockLayer::where('item_type', 'product')
            ->where('item_id', 2)
            ->sum('remaining_qty');
        
        echo "Remaining stock after consume: {$newAvailable}\n";
        
        // Clean up test movement
        \App\Models\StockMovement::where('ref_type', 'test_sale')
            ->where('ref_id', 999)
            ->delete();
        
        // Restore stock layer
        $newLayer->refresh();
        $newLayer->remaining_qty = $available;
        $newLayer->save();
        
        echo "Test cleaned up and stock layer restored\n";
        
    } catch (\Exception $e) {
        echo "Error during consume: " . $e->getMessage() . "\n";
    }
} else {
    echo "Cannot consume - insufficient stock\n";
}

echo "\nDone.\n";
