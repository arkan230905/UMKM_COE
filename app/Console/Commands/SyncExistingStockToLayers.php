<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;
use App\Models\StockLayer;
use App\Models\Bom;

class SyncExistingStockToLayers extends Command
{
    protected $signature = 'stock:sync-to-layers';
    protected $description = 'Sync existing product stock to StockLayer table';

    public function handle()
    {
        $this->info('Starting sync of existing product stock to StockLayer...');
        
        // Get products with stock but no StockLayer entries
        $products = Produk::where('stok', '>', 0)
            ->whereNotExists(function ($query) {
                $query->select('id')
                    ->from('stock_layers')
                    ->where('item_type', 'product')
                    ->whereColumn('item_id', 'produks.id');
            })
            ->get();
            
        if ($products->isEmpty()) {
            $this->info('No products need syncing.');
            return;
        }
        
        $this->info("Found {$products->count()} products to sync.");
        
        $synced = 0;
        foreach ($products as $product) {
            // Calculate unit cost
            $unitCost = 0;
            if ($product->harga_jual > 0) {
                $unitCost = $product->harga_jual / 1.3; // Estimate cost as 77% of selling price
            } else {
                // Fallback to BOM cost if available
                $sumBom = (float) Bom::where('produk_id', $product->id)->sum('total_biaya');
                $btkl = (float) ($product->btkl_default ?? 0);
                $bop = (float) ($product->bop_default ?? 0);
                $unitCost = $sumBom + $btkl + $bop;
            }
            
            StockLayer::create([
                'item_type' => 'product',
                'item_id' => $product->id,
                'tanggal' => now()->subDays(30), // Set to past date for opening balance
                'remaining_qty' => $product->stok,
                'satuan' => 'pcs',
                'unit_cost' => $unitCost,
                'ref_type' => 'opening_balance',
                'ref_id' => 0,
            ]);
            
            $synced++;
            $this->line("Synced: {$product->nama_produk} (Stock: {$product->stok}, Cost: {$unitCost})");
        }
        
        $this->info("Successfully synced {$synced} products to StockLayer.");
    }
}