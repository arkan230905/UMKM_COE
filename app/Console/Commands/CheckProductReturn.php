<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;

class CheckProductReturn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-product-return';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check product in return details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Product in Return Details...');
        
        // Check what product_id = 1 refers to
        $product = Produk::find(1);
        
        if ($product) {
            $this->info('Product ID 1:');
            $this->line('  Name: ' . $product->nama_produk);
            $this->line('  Stock: ' . $product->stok);
            $this->line('  Satuan: ' . ($product->satuan->nama ?? 'PCS'));
        } else {
            $this->error('Product ID 1 not found');
        }
        
        // Check all products
        $this->info('');
        $this->info('All Products:');
        $products = Produk::all();
        
        foreach ($products as $product) {
            $this->line('  ID: ' . $product->id . ' - ' . $product->nama_produk . ' (Stock: ' . $product->stok . ')');
        }
        
        return 0;
    }
}
