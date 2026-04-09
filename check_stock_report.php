<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Checking stock report calculations...\n\n";

// Get all products
$products = \App\Models\Produk::all(['id', 'nama_produk', 'stok']);

foreach ($products as $product) {
    echo "=== Produk: {$product->nama_produk} ===\n";
    echo "Current stock in database: {$product->stok}\n";
    
    // Calculate stock from stock movements
    $stockIn = \App\Models\StockMovement::where('item_type', 'product')
        ->where('item_id', $product->id)
        ->where('direction', 'in')
        ->sum('qty');
    
    $stockOut = \App\Models\StockMovement::where('item_type', 'product')
        ->where('item_id', $product->id)
        ->where('direction', 'out')
        ->sum('qty');
    
    $calculatedStock = $stockIn - $stockOut;
    
    echo "Stock IN (total): {$stockIn}\n";
    echo "Stock OUT (total): {$stockOut}\n";
    echo "Calculated stock: {$calculatedStock}\n";
    echo "Difference: " . ($product->stok - $calculatedStock) . "\n";
    
    // Show detail movements
    echo "\nMovement details:\n";
    $movements = \App\Models\StockMovement::where('item_type', 'product')
        ->where('item_id', $product->id)
        ->orderBy('created_at', 'asc')
        ->get();
    
    foreach ($movements as $movement) {
        echo "  " . $movement->created_at . " - {$movement->direction} {$movement->qty} ({$movement->ref_type}:{$movement->ref_id})\n";
    }
    
    echo "\n";
}

echo "=== Checking Stock Report Logic ===\n";

// Check if there's a specific stock report that might be using different logic
echo "Looking for stock report files...\n";

$reportFiles = [
    'app/Http/Controllers/LaporanController.php',
    'app/Http/Controllers/StokController.php',
    'resources/views/laporan/stok/index.blade.php'
];

foreach ($reportFiles as $file) {
    if (file_exists($file)) {
        echo "Found: {$file}\n";
        
        // Look for stock calculation logic
        $content = file_get_contents($file);
        if (strpos($content, 'stok') !== false) {
            echo "  Contains stock logic\n";
        }
    }
}

echo "\nDone.\n";
