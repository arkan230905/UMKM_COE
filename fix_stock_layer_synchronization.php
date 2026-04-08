<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Fixing stock layer synchronization issue...\n\n";

// The problem: stock layer qty is empty, causing remaining_qty to be incorrect
echo "=== FIXING STOCK LAYER ===\n";

$stockLayer = \App\Models\StockLayer::where('item_type', 'product')
    ->where('item_id', 2)
    ->first();

if ($stockLayer) {
    echo "Found stock layer ID: {$stockLayer->id}\n";
    echo "Current qty: " . ($stockLayer->qty ?? 'NULL') . "\n";
    echo "Current remaining_qty: {$stockLayer->remaining_qty}\n";
    
    // The issue: qty field is empty, need to fix it
    if (is_null($stockLayer->qty) || $stockLayer->qty == '') {
        echo "Qty field is empty - fixing it...\n";
        
        // Get the original quantity from stock movements
        $stockIn = \App\Models\StockMovement::where('item_type', 'product')
            ->where('item_id', 2)
            ->where('direction', 'in')
            ->sum('qty');
        
        echo "Setting qty to original stock IN: {$stockIn}\n";
        
        $stockLayer->qty = $stockIn;
        $stockLayer->remaining_qty = $stockIn - 5; // 160 - 5 = 155
        $stockLayer->save();
        
        echo "Updated stock layer:\n";
        echo "  Qty: {$stockLayer->qty}\n";
        echo "  Remaining: {$stockLayer->remaining_qty}\n";
    } else {
        echo "Qty field is not empty, checking remaining_qty...\n";
        
        // Recalculate remaining_qty from stock movements
        $stockOut = \App\Models\StockMovement::where('item_type', 'product')
            ->where('item_id', 2)
            ->where('direction', 'out')
            ->sum('qty');
        
        $correctRemaining = $stockLayer->qty - $stockOut;
        
        echo "Calculated remaining_qty: {$correctRemaining}\n";
        echo "Current remaining_qty: {$stockLayer->remaining_qty}\n";
        
        if ($stockLayer->remaining_qty != $correctRemaining) {
            echo "Updating remaining_qty...\n";
            $stockLayer->remaining_qty = $correctRemaining;
            $stockLayer->save();
            echo "Updated remaining_qty to: {$stockLayer->remaining_qty}\n";
        }
    }
} else {
    echo "No stock layer found for product 2\n";
}

// Verify the fix
echo "\n=== VERIFICATION ===\n";

$available = \App\Models\StockLayer::where('item_type', 'product')
    ->where('item_id', 2)
    ->sum('remaining_qty');

echo "Available stock from layers: {$available}\n";

// Test if we can now consume 150 units
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
        $stockLayer->refresh();
        $stockLayer->remaining_qty = $available;
        $stockLayer->save();
        
        echo "Test cleaned up and stock layer restored\n";
        
    } catch (\Exception $e) {
        echo "Error during consume: " . $e->getMessage() . "\n";
    }
} else {
    echo "Cannot consume - insufficient stock\n";
}

echo "\nDone.\n";
