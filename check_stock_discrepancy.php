<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING STOCK DISCREPANCY ===\n\n";

// Get all products and compare stock sources
$products = DB::table('produks')
    ->select('id', 'nama_produk', 'stok')
    ->orderBy('nama_produk')
    ->get();

echo "STOCK COMPARISON:\n";
echo str_pad('Product', 30) . " | " . str_pad('DB Stok', 10) . " | " . str_pad('StockLayer', 12) . " | " . str_pad('Difference', 12) . "\n";
echo str_repeat('-', 70) . "\n";

$totalDiscrepancies = 0;
$productsWithDiscrepancy = [];

foreach ($products as $product) {
    // Get stock from produks table
    $dbStock = (float)($product->stok ?? 0);
    
    // Get stock from stock_layers table
    $stockLayerStock = (float)DB::table('stock_layers')
        ->where('item_type', 'product')
        ->where('item_id', $product->id)
        ->sum('remaining_qty');
    
    // Calculate difference
    $difference = $stockLayerStock - $dbStock;
    
    // Display comparison
    $productName = substr($product->nama_produk, 0, 28);
    echo str_pad($productName, 30) . " | " . 
         str_pad(number_format($dbStock, 0), 10) . " | " . 
         str_pad(number_format($stockLayerStock, 0), 12) . " | " . 
         str_pad(number_format($difference, 0), 12) . "\n";
    
    // Track discrepancies
    if (abs($difference) > 0.01) {
        $totalDiscrepancies++;
        $productsWithDiscrepancy[] = [
            'id' => $product->id,
            'nama' => $product->nama_produk,
            'db_stock' => $dbStock,
            'stock_layer' => $stockLayerStock,
            'difference' => $difference
        ];
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total Products: " . $products->count() . "\n";
echo "Products with Stock Discrepancy: $totalDiscrepancies\n";

if ($totalDiscrepancies > 0) {
    echo "\n=== PRODUCTS WITH MAJOR DISCREPANCIES ===\n";
    foreach ($productsWithDiscrepancy as $product) {
        if (abs($product['difference']) > 10) {
            echo "- {$product['nama']}: DB={$product['db_stock']}, StockLayer={$product['stock_layer']}, Diff={$product['difference']}\n";
        }
    }
}

echo "\n=== STOCK MOVEMENT ANALYSIS ===\n";
$stockMovements = DB::table('stock_movements')
    ->where('item_type', 'product')
    ->selectRaw('
        item_id,
        SUM(CASE WHEN direction = "in" THEN qty ELSE 0 END) as total_in,
        SUM(CASE WHEN direction = "out" THEN qty ELSE 0 END) as total_out,
        SUM(CASE WHEN direction = "in" THEN qty ELSE -qty END) as net_movement
    ')
    ->groupBy('item_id')
    ->get();

echo "Stock Movement Summary:\n";
echo "Total Products with Movements: " . $stockMovements->count() . "\n";

$movementDiscrepancies = 0;
foreach ($stockMovements as $movement) {
    $product = $products->firstWhere('id', $movement->item_id);
    if ($product) {
        $dbStock = (float)($product->stok ?? 0);
        $calculatedStock = (float)$movement->net_movement;
        
        if (abs($calculatedStock - $dbStock) > 0.01) {
            $movementDiscrepancies++;
        }
    }
}

echo "Products with Movement vs DB Stock Discrepancy: $movementDiscrepancies\n";

echo "\n=== RECOMMENDATION ===\n";
if ($totalDiscrepancies > 0) {
    echo "❌ PROBLEM FOUND: Stock data is inconsistent!\n";
    echo "SOLUTION: Update PenjualanController to use actual_stok instead of stok\n";
    echo "- Change: \$p->stok to \$p->actual_stok\n";
    echo "- Or sync stok field with StockLayer data\n";
} else {
    echo "✅ Stock data is consistent between sources\n";
}

echo "\n=== CHECK COMPLETE ===\n";