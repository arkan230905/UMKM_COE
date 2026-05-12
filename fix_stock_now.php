<?php

// Direct database fix for stock issue
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIXING STOCK ISSUE NOW ===\n";

try {
    // Check current state
    $currentStock = DB::table('stock_layers')
        ->where('item_type', 'product')
        ->where('item_id', 2)
        ->sum('remaining_qty');
    
    echo "Current stock in layers: {$currentStock}\n";
    
    if ($currentStock > 0) {
        echo "✅ Stock already exists! No fix needed.\n";
        exit;
    }
    
    // Add stock using raw SQL
    DB::statement("
        INSERT INTO stock_layers (item_type, item_id, qty, remaining_qty, unit_cost, ref_type, ref_id, created_at, updated_at)
        VALUES ('product', 2, 20, 20, 35000, 'initial_stock', 0, NOW(), NOW())
    ");
    
    DB::statement("
        INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at)
        VALUES ('product', 2, CURDATE(), 'in', 20, 'Pcs', 35000, 700000, 'initial_stock', 0, NOW(), NOW())
    ");
    
    DB::statement("
        UPDATE produks SET stok = 20, updated_at = NOW() WHERE id = 2
    ");
    
    // Verify
    $newStock = DB::table('stock_layers')
        ->where('item_type', 'product')
        ->where('item_id', 2)
        ->sum('remaining_qty');
    
    $product = DB::table('produks')->where('id', 2)->first();
    
    echo "✅ FIXED!\n";
    echo "Stock layers now show: {$newStock} units\n";
    echo "Product table now shows: {$product->stok} units\n";
    echo "You can now proceed with sales!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== COMPLETE ===\n";