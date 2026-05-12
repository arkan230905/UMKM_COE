<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixProductStock extends Command
{
    protected $signature = 'fix:product-stock {product_id=2}';
    protected $description = 'Fix stock issue for a product by adding initial stock';

    public function handle()
    {
        $productId = $this->argument('product_id');
        
        $this->info("Fixing stock for product #{$productId}...");
        
        try {
            // Check current state
            $currentStock = DB::table('stock_layers')
                ->where('item_type', 'product')
                ->where('item_id', $productId)
                ->sum('remaining_qty');
            
            $this->line("Current stock in layers: {$currentStock}");
            
            if ($currentStock > 0) {
                $this->info("✅ Stock already exists! No fix needed.");
                return 0;
            }
            
            // Get product info
            $product = DB::table('produks')->where('id', $productId)->first();
            if (!$product) {
                $this->error("Product #{$productId} not found!");
                return 1;
            }
            
            $this->line("Product: {$product->nama_produk}");
            
            // Add stock
            DB::transaction(function () use ($productId) {
                // Add stock layer
                DB::table('stock_layers')->insert([
                    'item_type' => 'product',
                    'item_id' => $productId,
                    'qty' => 20,
                    'remaining_qty' => 20,
                    'unit_cost' => 35000,
                    'ref_type' => 'initial_stock',
                    'ref_id' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Add stock movement
                DB::table('stock_movements')->insert([
                    'item_type' => 'product',
                    'item_id' => $productId,
                    'tanggal' => now()->format('Y-m-d'),
                    'direction' => 'in',
                    'qty' => 20,
                    'satuan' => 'Pcs',
                    'unit_cost' => 35000,
                    'total_cost' => 700000,
                    'ref_type' => 'initial_stock',
                    'ref_id' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Update product stock
                DB::table('produks')->where('id', $productId)->update([
                    'stok' => 20,
                    'updated_at' => now()
                ]);
            });
            
            // Verify
            $newStock = DB::table('stock_layers')
                ->where('item_type', 'product')
                ->where('item_id', $productId)
                ->sum('remaining_qty');
            
            $updatedProduct = DB::table('produks')->where('id', $productId)->first();
            
            $this->info("✅ FIXED!");
            $this->line("Stock layers now show: {$newStock} units");
            $this->line("Product table now shows: {$updatedProduct->stok} units");
            $this->info("You can now proceed with sales!");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }
}