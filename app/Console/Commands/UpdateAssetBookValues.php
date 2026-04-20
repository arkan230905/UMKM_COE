<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Aset;
use Carbon\Carbon;

class UpdateAssetBookValues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:update-book-values 
                            {--force : Force update all assets}
                            {--asset-id= : Update specific asset by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update asset book values based on current month depreciation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== UPDATING ASSET BOOK VALUES ===');
        
        // Build query
        $query = Aset::where('status', 'aktif')
            ->whereNotNull('umur_manfaat')
            ->where('umur_manfaat', '>', 0);
        
        // Filter by specific asset if provided
        if ($this->option('asset-id')) {
            $query->where('id', $this->option('asset-id'));
        }
        
        $asets = $query->get();
        
        if ($asets->isEmpty()) {
            $this->warn('No assets found to update.');
            return 0;
        }
        
        $this->info("Found {$asets->count()} assets to update");
        
        $progressBar = $this->output->createProgressBar($asets->count());
        $progressBar->start();
        
        $updated = 0;
        $errors = 0;
        
        foreach ($asets as $aset) {
            try {
                // Store old values for comparison
                $oldNilaiBuku = $aset->nilai_buku;
                $oldAkumulasi = $aset->akumulasi_penyusutan;
                
                // Update nilai buku real-time
                $aset->updateNilaiBukuRealTime();
                
                // Refresh model to get updated values
                $aset->refresh();
                
                // Show changes if significant
                if (abs($aset->nilai_buku - $oldNilaiBuku) > 0.01 || 
                    abs($aset->akumulasi_penyusutan - $oldAkumulasi) > 0.01) {
                    
                    if ($this->option('verbose')) {
                        $this->newLine();
                        $this->line("Updated: {$aset->nama_aset}");
                        $this->line("  Old Book Value: Rp " . number_format($oldNilaiBuku, 2, ',', '.'));
                        $this->line("  New Book Value: Rp " . number_format($aset->nilai_buku, 2, ',', '.'));
                        $this->line("  Old Accumulated: Rp " . number_format($oldAkumulasi, 2, ',', '.'));
                        $this->line("  New Accumulated: Rp " . number_format($aset->akumulasi_penyusutan, 2, ',', '.'));
                    }
                }
                
                $updated++;
                
            } catch (\Exception $e) {
                $this->error("Error updating asset {$aset->id}: " . $e->getMessage());
                $errors++;
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("Update completed:");
        $this->line("  ✅ Successfully updated: {$updated} assets");
        
        if ($errors > 0) {
            $this->error("  ❌ Errors: {$errors} assets");
        }
        
        return 0;
    }
}