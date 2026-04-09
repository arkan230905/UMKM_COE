<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Produk;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;

class AddInitialProductStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menambahkan stok awal untuk produk yang belum memiliki stock_layers
     */
    public function run(): void
    {
        $stockService = new StockService();
        
        // Get all products that have stok > 0 but no stock_layers
        $products = Produk::where('stok', '>', 0)->get();
        
        foreach ($products as $produk) {
            // Check if product already has stock layers
            $existingStock = DB::table('stock_layers')
                ->where('item_type', 'product')
                ->where('item_id', $produk->id)
                ->sum('remaining_qty');
            
            if ($existingStock <= 0 && $produk->stok > 0) {
                // Add initial stock layer
                $unitCost = $produk->harga_bom ?? $produk->harga_jual ?? 0;
                
                $stockService->addLayer(
                    'product',
                    $produk->id,
                    $produk->stok,
                    'pcs', // Default unit
                    $unitCost,
                    'initial_stock',
                    0, // No reference ID for initial stock
                    now()->format('Y-m-d')
                );
                
                $this->command->info("✓ Added initial stock for: {$produk->nama_produk} (Qty: {$produk->stok}, Cost: {$unitCost})");
            } else {
                $this->command->info("- Product already has stock layers: {$produk->nama_produk} (Existing: {$existingStock})");
            }
        }
        
        $this->command->info("✅ Initial product stock synchronization completed");
    }
}