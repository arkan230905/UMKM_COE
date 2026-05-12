<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;
use App\Models\StockLayer;

class ProductStockSeeder extends Seeder
{
    public function run()
    {
        // Add stock for product #2
        $produk = Produk::find(2);
        if ($produk) {
            // Update system stock
            $produk->update(['stok' => 10]);
            
            // Add stock layer
            StockLayer::create([
                'item_type' => 'product',
                'item_id' => 2,
                'qty' => 10,
                'remaining_qty' => 10,
                'unit_cost' => $produk->harga_bom ?? 50000,
                'ref_type' => 'initial_stock',
                'ref_id' => 0,
                'tanggal' => now(),
            ]);
            
            echo "Added stock for product: {$produk->nama_produk}\n";
        }
    }
}