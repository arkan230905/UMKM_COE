<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Testing stock report integration with penjualan...\n\n";

// Test creating a new penjualan to see if stock report updates
echo "=== CREATING TEST PENJUALAN ===\n";

$product = \App\Models\Produk::find(2);
if (!$product) {
    echo "Product ID 2 not found!\n";
    exit;
}

echo "Product: {$product->nama_produk}\n";
echo "Current stock: {$product->stok}\n";

// Check stock movements before
$stockInBefore = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->where('direction', 'in')
    ->sum('qty');

$stockOutBefore = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->where('direction', 'out')
    ->sum('qty');

$calculatedStockBefore = $stockInBefore - $stockOutBefore;

echo "Stock IN before: {$stockInBefore}\n";
echo "Stock OUT before: {$stockOutBefore}\n";
echo "Calculated stock before: {$calculatedStockBefore}\n";

// Create test penjualan
try {
    $penjualan = \App\Models\Penjualan::create([
        'produk_id' => 2,
        'tanggal' => now()->format('Y-m-d'),
        'payment_method' => 'cash',
        'jumlah' => 10,
        'harga_satuan' => 45000,
        'diskon_nominal' => 0,
        'total' => 450000,
    ]);
    
    echo "Created test penjualan ID: {$penjualan->id}\n";
    
    // Create stock movement
    $hpp = $product->getHPPForSaleDate(now()->format('Y-m-d'));
    if ($hpp <= 0) {
        $hpp = 35000; // Fallback
    }
    
    $stockMovement = \App\Models\StockMovement::create([
        'tanggal' => now()->format('Y-m-d'),
        'item_type' => 'product',
        'item_id' => 2,
        'ref_type' => 'sale',
        'ref_id' => $penjualan->id,
        'qty' => 10,
        'direction' => 'out',
        'unit_cost' => $hpp,
        'total_cost' => $hpp * 10,
    ]);
    
    echo "Created stock movement ID: {$stockMovement->id}\n";
    
    // Update product stock
    $product->stok = $product->stok - 10;
    $product->save();
    
    echo "Updated product stock to: {$product->stok}\n";
    
} catch (\Exception $e) {
    echo "Error creating test penjualan: " . $e->getMessage() . "\n";
}

// Check stock after
$stockInAfter = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->where('direction', 'in')
    ->sum('qty');

$stockOutAfter = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->where('direction', 'out')
    ->sum('qty');

$calculatedStockAfter = $stockInAfter - $stockOutAfter;

echo "\n=== AFTER PENJUALAN ===\n";
echo "Stock IN after: {$stockInAfter}\n";
echo "Stock OUT after: {$stockOutAfter}\n";
echo "Calculated stock after: {$calculatedStockAfter}\n";
echo "Product stock in database: {$product->stok}\n";

// Test stock report logic
echo "\n=== TESTING STOCK REPORT LOGIC ===\n";

// Simulate the new laporan stok logic
$stockIn = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->where('direction', 'in')
    ->sum('qty');

$stockOut = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->where('direction', 'out')
    ->sum('qty');

$reportStock = $stockIn - $stockOut;

echo "Stock report calculated stock: {$reportStock}\n";
echo "Matches product stock: " . ($reportStock == $product->stok ? 'YES' : 'NO') . "\n";

// Clean up test data
echo "\n=== CLEANUP ===\n";
try {
    \App\Models\StockMovement::where('ref_type', 'sale')
        ->where('ref_id', $penjualan->id)
        ->delete();
    
    $penjualan->delete();
    
    // Restore product stock
    $product->stok = $product->stok + 10;
    $product->save();
    
    echo "Test data cleaned up\n";
} catch (\Exception $e) {
    echo "Error during cleanup: " . $e->getMessage() . "\n";
}

echo "\nDone.\n";
