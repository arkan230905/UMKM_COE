<?php

namespace App\Console\Commands;

use App\Models\Aset;
use App\Services\AssetDepreciationService;
use Illuminate\Console\Command;

class RecalculateDepreciationSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recalculate-depreciation-schedule {--asset-id= : Specific asset ID to recalculate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate depreciation schedule with pro-rata first year calculation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assetId = $this->option('asset-id');
        
        if ($assetId) {
            // Recalculate specific asset
            $asset = Aset::find($assetId);
            
            if (!$asset) {
                $this->error("Asset with ID {$assetId} not found!");
                return 1;
            }
            
            $this->recalculateAsset($asset);
        } else {
            // Recalculate all assets
            $assets = Aset::whereNotNull('tarif_penyusutan')->get();
            
            if ($assets->isEmpty()) {
                $this->info('No assets found with tarif_penyusutan to recalculate.');
                return 0;
            }
            
            $this->info("Recalculating {$assets->count()} assets...");
            
            foreach ($assets as $asset) {
                $this->recalculateAsset($asset);
            }
        }
        
        $this->info('Depreciation schedules recalculated successfully!');
        return 0;
    }
    
    private function recalculateAsset(Aset $asset)
    {
        $this->line("Recalculating asset: {$asset->nama_aset} (ID: {$asset->id})");
        
        // Show first year calculation
        $tahunan = $asset->hitungBebanPenyusutanTahunan();
        $tahunPertama = $asset->hitungPenyusutanTahunPertama();
        
        $this->line("  Annual depreciation: Rp " . number_format($tahunan, 2));
        $this->line("  First year depreciation: Rp " . number_format($tahunPertama, 2));
        
        // Get acquisition date info
        $tanggalPerolehan = $asset->tanggal_akuisisi ?? $asset->tanggal_beli;
        if ($tanggalPerolehan) {
            $tanggal = \Carbon\Carbon::parse($tanggalPerolehan);
            $bulanPerolehan = $tanggal->month;
            $sisaBulan = 12 - $bulanPerolehan + 1;
            $this->line("  Acquisition month: " . $tanggal->format('F') . " ({$sisaBulan} months remaining)");
        }
        
        // Recalculate depreciation schedule
        try {
            $depreciationService = new AssetDepreciationService();
            $depreciationService->computeAndPost($asset);
            $this->line("  Schedule recalculated successfully!");
        } catch (\Exception $e) {
            $this->error("  Error recalculating schedule: " . $e->getMessage());
        }
        
        $this->line("");
    }
}
