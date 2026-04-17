<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING STOCK FIX ===\n\n";

// Test the Produk model's actual_stok accessor
$products = \App\Models\Produk::all();

echo "TESTING ACTUAL_STOK ACCESSOR:\n";
echo str_pad('Product', 30) . " | " . str_pad('DB Stok', 10) . " | " . str_pad('Actual Stok', 12) . " | " . str_pad('StockLayer', 12) . "\n";
echo str_repeat('-', 70) . "\n";

foreach ($products as $product) {
    $dbStock = (float)($product->stok ?? 0);
    $actualStock = (float)$product->actual_stok; // Using accessor
    
    // Direct query to stock_layers for verification
    $stockLayerStock = (float)DB::table('stock_layers')
        ->where('item_type', 'product')
        ->where('item_id', $product->id)
        ->sum('remaining_qty');
    
    $productName = substr($product->nama_produk, 0, 28);
    echo str_pad($productName, 30) . " | " . 
         str_pad(number_format($dbStock, 0), 10) . " | " . 
         str_pad(number_format($actualStock, 0), 12) . " | " . 
         str_pad(number_format($stockLayerStock, 0), 12) . "\n";
}

echo "\n=== TESTING CONTROLLER LOGIC ===\n";

// Simulate what the controller does now
$produks = \App\Models\Produk::all()->map(function($p) {
    $p->stok_tersedia = (float)$p->actual_stok;
    return $p;
});

echo "Controller Stock Mapping:\n";
foreach ($produks as $p) {
    echo "- {$p->nama_produk}: stok_tersedia = {$p->stok_tersedia}\n";
}

echo "\n=== TESTING STOCK VALIDATION ===\n";

// Test stock validation logic
foreach ($products as $product) {
    $actualStock = (float)$product->actual_stok;
    $testQty = 50; // Test quantity
    
    if ($testQty > $actualStock) {
        echo "❌ {$product->nama_produk}: Qty {$testQty} > Stock {$actualStock} - VALIDATION WOULD FAIL\n";
    } else {
        echo "✅ {$product->nama_produk}: Qty {$testQty} <= Stock {$actualStock} - VALIDATION WOULD PASS\n";
    }
}

echo "\n=== RECOMMENDATION ===\n";

$hasStockIssues = false;
foreach ($products as $product) {
    $dbStock = (float)($product->stok ?? 0);
    $actualStock = (float)$product->actual_stok;
    
    if ($dbStock > 0 && $actualStock == 0) {
        $hasStockIssues = true;
        break;
    }
}

if ($hasStockIssues) {
    echo "⚠️  STOCK SYNC NEEDED: Some products have DB stock but no StockLayer stock\n";
    echo "SOLUTION: Run stock synchronization or create initial stock layers\n";
} else {
    echo "✅ STOCK DATA CONSISTENT: actual_stok accessor working correctly\n";
}

echo "\n=== TEST COMPLETE ===\n";