<?php

namespace App\Console\Commands;

use App\Models\Aset;
use Illuminate\Console\Command;

class UpdateDepreciationValues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-depreciation-values {--asset-id= : Specific asset ID to update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update depreciation values for assets using new formula (tarif × harga perolehan)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assetId = $this->option('asset-id');
        
        if ($assetId) {
            // Update specific asset
            $asset = Aset::find($assetId);
            
            if (!$asset) {
                $this->error("Asset with ID {$assetId} not found!");
                return 1;
            }
            
            $this->updateSingleAsset($asset);
        } else {
            // Update all assets
            $assets = Aset::whereNotNull('tarif_penyusutan')->get();
            
            if ($assets->isEmpty()) {
                $this->info('No assets found with tarif_penyusutan to update.');
                return 0;
            }
            
            $this->info("Updating {$assets->count()} assets...");
            
            foreach ($assets as $asset) {
                $this->updateSingleAsset($asset);
            }
        }
        
        $this->info('Depreciation values updated successfully!');
        return 0;
    }
    
    private function updateSingleAsset(Aset $asset)
    {
        $this->line("Updating asset: {$asset->nama_aset} (ID: {$asset->id})");
        
        // Show current values
        $this->line("  Current - Tahunan: Rp " . number_format($asset->penyusutan_per_tahun, 2) . 
                   ", Bulanan: Rp " . number_format($asset->penyusutan_per_bulan, 2));
        
        // Update with new calculation
        $asset->updatePenyusutanValues();
        
        // Show updated values
        $this->line("  Updated - Tahunan: Rp " . number_format($asset->penyusutan_per_tahun, 2) . 
                   ", Bulanan: Rp " . number_format($asset->penyusutan_per_bulan, 2));
        
        // Show calculation
        $total = $asset->harga_perolehan + $asset->biaya_perolehan;
        $expectedTahunan = $total * ($asset->tarif_penyusutan / 100);
        $expectedBulanan = $expectedTahunan / 12;
        
        $this->line("  Calculation: Rp " . number_format($total, 2) . " × " . $asset->tarif_penyusutan . "% = Rp " . number_format($expectedTahunan, 2) . " per tahun");
        $this->line("");
    }
}
