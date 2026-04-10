<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Syncing product stock with stock movements...\n\n";

// Get all products
$products = \App\Models\Produk::all();

foreach ($products as $product) {
    echo "=== Product: {$product->nama_produk} (ID: {$product->id}) ===\n";
    
    // Calculate stock from movements
    $stockIn = \App\Models\StockMovement::where('item_type', 'product')
        ->where('item_id', $product->id)
        ->where('direction', 'in')
        ->sum('qty');
    
    $stockOut = \App\Models\StockMovement::where('item_type', 'product')
        ->where('item_id', $product->id)
        ->where('direction', 'out')
        ->sum('qty');
    
    $calculatedStock = $stockIn - $stockOut;
    
    echo "Current stock in database: " . ($product->stok ?? 0) . "\n";
    echo "Stock IN from movements: {$stockIn}\n";
    echo "Stock OUT from movements: {$stockOut}\n";
    echo "Calculated stock: {$calculatedStock}\n";
    
    // Calculate difference
    $difference = ($product->stok ?? 0) - $calculatedStock;
    
    if (abs($difference) > 0.001) { // Small tolerance for floating point
        echo "Difference: {$difference} - UPDATING\n";
        
        // Update product stock
        $product->stok = $calculatedStock;
        $product->save();
        
        echo "Updated stock to: {$calculatedStock}\n";
    } else {
        echo "Difference: {$difference} - NO UPDATE NEEDED\n";
    }
    
    echo "\n";
}

echo "=== VERIFICATION ===\n";

// Verify the sync
$verificationProducts = \App\Models\Produk::all();
$allSynced = true;

foreach ($verificationProducts as $product) {
    $stockIn = \App\Models\StockMovement::where('item_type', 'product')
        ->where('item_id', $product->id)
        ->where('direction', 'in')
        ->sum('qty');
    
    $stockOut = \App\Models\StockMovement::where('item_type', 'product')
        ->where('item_id', $product->id)
        ->where('direction', 'out')
        ->sum('qty');
    
    $calculatedStock = $stockIn - $stockOut;
    $difference = ($product->stok ?? 0) - $calculatedStock;
    
    if (abs($difference) > 0.001) {
        echo "✗ Product {$product->nama_produk} still has difference: {$difference}\n";
        $allSynced = false;
    }
}

if ($allSynced) {
    echo "✓ All products are now synced with stock movements\n";
} else {
    echo "✗ Some products still need syncing\n";
}

echo "\nDone.\n";
