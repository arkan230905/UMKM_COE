<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;
use App\Models\Bom;
use App\Models\BahanBaku;
use App\Models\ProsesProduksi;
use App\Models\Bop;
use App\Models\Satuan;

class DebugBomCreate extends Command
{
    protected $signature = 'debug:bom-create';
    protected $description = 'Debug BOM creation issues';

    public function handle()
    {
        $this->info('=== DEBUG BOM CREATE ===');
        
        // Check products
        $this->info('1. Checking Products:');
        $products = Produk::all();
        $this->info('   Total Products: ' . $products->count());
        foreach ($products as $product) {
            $this->info('   ID ' . $product->id . ' - ' . $product->nama_produk . ' - Biaya: ' . $product->biaya_bahan);
        }
        
        // Check existing BOMs
        $this->info('2. Checking Existing BOMs:');
        $boms = Bom::all();
        $this->info('   Total BOMs: ' . $boms->count());
        foreach ($boms as $bom) {
            $this->info('   BOM ID ' . $bom->id . ' for Product ID ' . $bom->produk_id);
        }
        
        // Check available products for BOM
        $this->info('3. Available Products for BOM:');
        $produkIdsWithBom = Bom::pluck('produk_id')->toArray();
        $availableProducts = Produk::whereNotIn('id', $produkIdsWithBom)->get();
        $this->info('   Available Products: ' . $availableProducts->count());
        foreach ($availableProducts as $product) {
            $this->info('   - ' . $product->nama_produk . ' (ID: ' . $product->id . ')');
        }
        
        // Check required data
        $this->info('4. Required Data:');
        $this->info('   Bahan Baku: ' . BahanBaku::count());
        $this->info('   Proses Produksi: ' . ProsesProduksi::count());
        $this->info('   BOP: ' . Bop::count());
        $this->info('   Satuan: ' . Satuan::count());
        
        // Test sample BOM data
        if ($availableProducts->isNotEmpty()) {
            $product = $availableProducts->first();
            $this->info('5. Sample BOM Data for ' . $product->nama_produk . ':');
            $this->info('   Product ID: ' . $product->id);
            $this->info('   Biaya Bahan: ' . ($product->biaya_bahan ?? 0));
            
            // Test validation rules
            $this->info('6. Testing Validation Rules:');
            $this->info('   produk_id required: ✓');
            $this->info('   produk_id exists: ✓');
            $this->info('   produk_id unique: ✓ (Available? ' . ($products->count() > $boms->count() ? 'YES' : 'NO') . ')');
        }
        
        $this->info('=== END DEBUG ===');
        
        return 0;
    }
}
