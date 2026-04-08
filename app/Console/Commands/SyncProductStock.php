<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;
use App\Models\StockLayer;

class SyncProductStock extends Command
{
    protected $signature = 'stock:sync-product {id}';
    protected $description = 'Sync product stock from system to StockLayer';

    public function handle()
    {
        $productId = $this->argument('id');
        
        $produk = Produk::find($productId);
        if (!$produk) {
            $this->error("Product #{$productId} not found!");
            return 1;
        }
        
        $this->info("Product: {$produk->nama_produk}");
        $this->info("System stock: {$produk->stok}");
        
        $stockLayers = StockLayer::where('item_type', 'product')
            ->where('item_id', $productId)
            ->get();
        
        $totalRealtime = $stockLayers->sum('remaining_qty');
        $this->info("Realtime stock: {$totalRealtime}");
        
        if ($produk->stok > 0 && $totalRealtime == 0) {
            $this->info("Creating stock layer from system stock...");
            
            $newLayer = StockLayer::create([
                'item_type' => 'product',
                'item_id' => $productId,
                'qty' => $produk->stok,
                'remaining_qty' => $produk->stok,
                'unit_cost' => $produk->harga_bom ?? 0,
                'ref_type' => 'stock_sync',
                'ref_id' => 0,
                'tanggal' => now(),
            ]);
            
            $this->info("Created stock layer ID: {$newLayer->id}");
            $this->info("New realtime stock: {$produk->stok}");
        } else {
            $this->info("No sync needed.");
        }
        
        return 0;
    }
}