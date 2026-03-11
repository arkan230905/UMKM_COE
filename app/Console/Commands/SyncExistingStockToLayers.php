<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Produk;
use App\Models\StockLayer;
use Carbon\Carbon;

class SyncExistingStockToLayers extends Command
{
    protected $signature = 'stock:sync-to-layers';
    protected $description = 'Sync existing stock from bahan_bakus, bahan_pendukungs, and produks to stock_layers';

    public function handle()
    {
        $this->info('Syncing existing stock to stock_layers...');
        
        // Sync Bahan Baku
        $this->info('Syncing Bahan Baku...');
        $bahanBakus = BahanBaku::where('stok', '>', 0)->get();
        foreach ($bahanBakus as $bahan) {
            // Check if layer already exists
            $existingLayer = StockLayer::where('item_type', 'material')
                ->where('item_id', $bahan->id)
                ->where('ref_type', 'opening_balance')
                ->first();
            
            if (!$existingLayer) {
                $unitCost = $bahan->harga_satuan ?? 0;
                StockLayer::create([
                    'item_type' => 'material',
                    'item_id' => $bahan->id,
                    'tanggal' => Carbon::now()->subDays(30), // 30 days ago as opening balance
                    'remaining_qty' => $bahan->stok,
                    'satuan' => $bahan->satuan->nama ?? 'Unit',
                    'unit_cost' => $unitCost,
                    'ref_type' => 'opening_balance',
                    'ref_id' => 0,
                ]);
                $this->info("  ✓ {$bahan->nama_bahan}: {$bahan->stok} {$bahan->satuan->nama}");
            }
        }
        
        // Sync Bahan Pendukung
        $this->info('Syncing Bahan Pendukung...');
        $bahanPendukungs = BahanPendukung::where('stok', '>', 0)->get();
        foreach ($bahanPendukungs as $bahan) {
            $existingLayer = StockLayer::where('item_type', 'support')
                ->where('item_id', $bahan->id)
                ->where('ref_type', 'opening_balance')
                ->first();
            
            if (!$existingLayer) {
                $unitCost = $bahan->harga_satuan ?? 0;
                StockLayer::create([
                    'item_type' => 'support',
                    'item_id' => $bahan->id,
                    'tanggal' => Carbon::now()->subDays(30),
                    'remaining_qty' => $bahan->stok,
                    'satuan' => $bahan->satuanRelation->nama ?? 'Unit',
                    'unit_cost' => $unitCost,
                    'ref_type' => 'opening_balance',
                    'ref_id' => 0,
                ]);
                $this->info("  ✓ {$bahan->nama_bahan}: {$bahan->stok} {$bahan->satuanRelation->nama}");
            }
        }
        
        // Sync Produk
        $this->info('Syncing Produk...');
        $produks = Produk::where('stok', '>', 0)->get();
        foreach ($produks as $produk) {
            $existingLayer = StockLayer::where('item_type', 'product')
                ->where('item_id', $produk->id)
                ->where('ref_type', 'opening_balance')
                ->first();
            
            if (!$existingLayer) {
                // Calculate unit cost from harga_jual with 30% margin
                $unitCost = $produk->harga_jual ? $produk->harga_jual / 1.3 : 0;
                StockLayer::create([
                    'item_type' => 'product',
                    'item_id' => $produk->id,
                    'tanggal' => Carbon::now()->subDays(30),
                    'remaining_qty' => $produk->stok,
                    'satuan' => 'pcs',
                    'unit_cost' => $unitCost,
                    'ref_type' => 'opening_balance',
                    'ref_id' => 0,
                ]);
                $this->info("  ✓ {$produk->nama_produk}: {$produk->stok} pcs");
            }
        }
        
        $this->info('✓ Stock sync completed!');
        return 0;
    }
}
