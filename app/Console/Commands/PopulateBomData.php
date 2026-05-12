<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BomSyncService;
use App\Models\Produk;
use App\Models\BomJobCosting;
use App\Models\Btkl;
use App\Models\BopProses;

class PopulateBomData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bom:populate-all 
                            {--force : Force repopulate even if data exists}
                            {--product= : Populate specific product by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-populate BTKL and BOP data for all products in BOM system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting BOM Data Population...');
        $this->newLine();
        
        try {
            // Check if master data exists
            $btklCount = Btkl::where('is_active', true)->count();
            $bopCount = BopProses::where('is_active', true)->count();
            
            $this->info("📊 Master Data Status:");
            $this->line("   - Active BTKL processes: {$btklCount}");
            $this->line("   - Active BOP processes: {$bopCount}");
            $this->newLine();
            
            if ($btklCount == 0 || $bopCount == 0) {
                $this->error('❌ No BTKL or BOP master data found!');
                $this->line('   Please create BTKL and BOP data first before running this command.');
                return 1;
            }
            
            // Handle specific product
            if ($this->option('product')) {
                return $this->populateSpecificProduct($this->option('product'));
            }
            
            // Handle all products
            return $this->populateAllProducts();
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Populate data for all products
     */
    private function populateAllProducts()
    {
        $products = Produk::all();
        $this->info("🎯 Found {$products->count()} products to process");
        $this->newLine();
        
        if ($products->isEmpty()) {
            $this->warn('⚠️  No products found in database.');
            return 0;
        }
        
        $progressBar = $this->output->createProgressBar($products->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        
        $createdCount = 0;
        $updatedCount = 0;
        $errorCount = 0;
        
        foreach ($products as $product) {
            $progressBar->setMessage("Processing: {$product->nama_produk}");
            
            try {
                $bomJobCosting = BomJobCosting::where('produk_id', $product->id)->first();
                
                if (!$bomJobCosting) {
                    // Create new BomJobCosting
                    $bomJobCosting = BomJobCosting::create([
                        'produk_id' => $product->id,
                        'jumlah_produk' => 1,
                        'total_bbb' => 0,
                        'total_btkl' => 0,
                        'total_bahan_pendukung' => 0,
                        'total_bop' => 0,
                        'total_hpp' => 0,
                        'hpp_per_unit' => 0
                    ]);
                    $createdCount++;
                } else {
                    $updatedCount++;
                }
                
                // Sync BTKL and BOP data (this will repopulate if --force is used)
                if ($this->option('force') || !$bomJobCosting->detailBTKL()->exists() || !$bomJobCosting->detailBOP()->exists()) {
                    BomSyncService::syncBTKLForBom($bomJobCosting);
                    BomSyncService::syncBOPForBom($bomJobCosting);
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $this->newLine();
                $this->error("   ❌ Error processing {$product->nama_produk}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Summary
        $this->info('✅ Population completed!');
        $this->newLine();
        $this->info('📈 Summary:');
        $this->line("   - Products processed: {$products->count()}");
        $this->line("   - BomJobCosting created: {$createdCount}");
        $this->line("   - BomJobCosting updated: {$updatedCount}");
        $this->line("   - Errors: {$errorCount}");
        $this->newLine();
        
        if ($errorCount == 0) {
            $this->info('🎉 All products now have BTKL and BOP data!');
        } else {
            $this->warn("⚠️  {$errorCount} products had errors. Check the logs above.");
        }
        
        return 0;
    }
    
    /**
     * Populate data for specific product
     */
    private function populateSpecificProduct($productId)
    {
        $product = Produk::find($productId);
        
        if (!$product) {
            $this->error("❌ Product with ID {$productId} not found!");
            return 1;
        }
        
        $this->info("🎯 Processing specific product: {$product->nama_produk}");
        $this->newLine();
        
        try {
            $bomJobCosting = BomJobCosting::where('produk_id', $product->id)->first();
            
            if (!$bomJobCosting) {
                $bomJobCosting = BomJobCosting::create([
                    'produk_id' => $product->id,
                    'jumlah_produk' => 1,
                    'total_bbb' => 0,
                    'total_btkl' => 0,
                    'total_bahan_pendukung' => 0,
                    'total_bop' => 0,
                    'total_hpp' => 0,
                    'hpp_per_unit' => 0
                ]);
                $this->info('✅ Created BomJobCosting');
            } else {
                $this->info('✅ Found existing BomJobCosting');
            }
            
            // Sync BTKL and BOP
            BomSyncService::syncBTKLForBom($bomJobCosting);
            BomSyncService::syncBOPForBom($bomJobCosting);
            
            $this->info('✅ Populated BTKL and BOP data');
            $this->newLine();
            
            // Show summary
            $bomJobCosting->refresh();
            $this->info('📊 Cost Summary:');
            $this->line("   - Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2));
            $this->line("   - Total BOP: Rp " . number_format($bomJobCosting->total_bop, 2));
            $this->line("   - HPP per unit: Rp " . number_format($bomJobCosting->hpp_per_unit, 2));
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }
}