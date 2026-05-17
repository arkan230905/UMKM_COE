<?php

namespace App\Console\Commands;

use App\Models\Produk;
use Illuminate\Console\Command;

class DebugProducts extends Command
{
    protected $signature = 'debug:products';
    protected $description = 'Debug products data';

    public function handle()
    {
        $this->info('=== DEBUGGING PRODUCTS ===');
        
        $totalProducts = Produk::withoutGlobalScopes()->count();
        $this->info("Total products: {$totalProducts}");
        
        if ($totalProducts === 0) {
            $this->error("❌ Tidak ada produk!");
            return;
        }
        
        $this->info("\n=== LATEST PRODUCTS ===");
        $latestProducts = Produk::withoutGlobalScopes()
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();
        
        foreach ($latestProducts as $product) {
            $this->line("ID: {$product->id}, Nama: {$product->nama_produk}, Stok: {$product->stok}");
        }
        
        $this->info("\n=== PRODUCTS WITH STOCK > 0 ===");
        $productsWithStock = Produk::withoutGlobalScopes()
            ->where('stok', '>', 0)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();
        
        $this->info("Products with stock: {$productsWithStock->count()}");
        
        foreach ($productsWithStock as $product) {
            $this->line("ID: {$product->id}, Nama: {$product->nama_produk}, Stok: {$product->stok}");
        }
    }
}
