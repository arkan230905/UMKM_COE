<?php

namespace App\Console\Commands;

use App\Models\BiayaBahanBaku;
use Illuminate\Console\Command;

class SyncBiayaBahanBakuHarga extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biaya-bahan:sync-harga 
                            {--user= : Sync only for specific user ID}
                            {--produk= : Sync only for specific produk ID}
                            {--force : Force sync even if prices match}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync harga biaya bahan baku dari master data bahan baku';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sync harga biaya bahan baku...');
        
        // Build query
        $query = BiayaBahanBaku::with('bahanBaku');
        
        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
            $this->info("Filtering for user ID: {$userId}");
        }
        
        if ($produkId = $this->option('produk')) {
            $query->where('produk_id', $produkId);
            $this->info("Filtering for produk ID: {$produkId}");
        }
        
        $biayaBahans = $query->get();
        $total = $biayaBahans->count();
        
        if ($total === 0) {
            $this->warn('No biaya bahan baku found to sync.');
            return 0;
        }
        
        $this->info("Found {$total} biaya bahan baku to process.");
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($biayaBahans as $biayaBahan) {
            try {
                $force = $this->option('force');
                
                // Skip if harga sama dan tidak force
                if (!$force && !$biayaBahan->isHargaOutdated()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }
                
                // Sync harga
                if ($biayaBahan->syncHargaFromMaster()) {
                    $updated++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("\nError syncing ID {$biayaBahan->id}: " . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Summary
        $this->info("Sync completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Total Processed', $total],
                ['Updated', $updated],
                ['Skipped (No Change)', $skipped],
                ['Errors', $errors],
            ]
        );
        
        return 0;
    }
}
