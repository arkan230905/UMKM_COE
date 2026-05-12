<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SYNCING STOCK LAYERS FROM PRODUKS TABLE ===\n\n";

try {
    DB::beginTransaction();
    
    // Get all products with stock > 0 in produks table
    $products = DB::table('produks')
        ->where('stok', '>', 0)
        ->get();
    
    echo "Found " . $products->count() . " products with stock > 0\n\n";
    
    $syncedCount = 0;
    $skippedCount = 0;
    
    foreach ($products as $product) {
        // Check if stock layer already exists
        $existingLayer = DB::table('stock_layers')
            ->where('item_type', 'product')
            ->where('item_id', $product->id)
            ->first();
        
        if ($existingLayer) {
            echo "SKIP: {$product->nama_produk} - Stock layer already exists\n";
            $skippedCount++;
            continue;
        }
        
        // Create initial stock layer
        $stockLayerId = DB::table('stock_layers')->insertGetId([
            'item_type' => 'product',
            'item_id' => $product->id,
            'tanggal' => now()->format('Y-m-d'),
            'remaining_qty' => $product->stok,
            'unit_cost' => $product->harga_beli ?? $product->hpp ?? 0,
            'satuan' => 'pcs',
            'ref_type' => 'initial_stock',
            'ref_id' => $product->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Create corresponding stock movement (initial stock)
        DB::table('stock_movements')->insert([
            'item_type' => 'product',
            'item_id' => $product->id,
            'tanggal' => now()->format('Y-m-d'),
            'direction' => 'in',
            'qty' => $product->stok,
            'satuan' => 'pcs',
            'unit_cost' => $product->harga_beli ?? $product->hpp ?? 0,
            'total_cost' => ($product->harga_beli ?? $product->hpp ?? 0) * $product->stok,
            'ref_type' => 'initial_stock',
            'ref_id' => $product->id,
            'keterangan' => 'Initial stock sync from produks table',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "SYNC: {$product->nama_produk} - Created stock layer with qty {$product->stok}\n";
        $syncedCount++;
    }
    
    DB::commit();
    
    echo "\n=== SYNC COMPLETE ===\n";
    echo "Synced: $syncedCount products\n";
    echo "Skipped: $skippedCount products\n";
    
    // Verify the sync
    echo "\n=== VERIFICATION ===\n";
    $products = \App\Models\Produk::all();
    
    foreach ($products as $product) {
        $dbStock = (float)($product->stok ?? 0);
        $actualStock = (float)$product->actual_stok;
        
        if ($dbStock > 0) {
            if ($actualStock == $dbStock) {
                echo "✅ {$product->nama_produk}: DB={$dbStock}, Actual={$actualStock} - SYNCED\n";
            } else {
                echo "❌ {$product->nama_produk}: DB={$dbStock}, Actual={$actualStock} - MISMATCH\n";
            }
        }
    }
    
    echo "\n✅ STOCK SYNC COMPLETED SUCCESSFULLY!\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== SYNC COMPLETE ===\n";