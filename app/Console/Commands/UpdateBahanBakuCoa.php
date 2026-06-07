<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanBaku;
use App\Models\Coa;

class UpdateBahanBakuCoa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bahan:update-coa {--user-id=} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update COA Persediaan mapping for Bahan Baku based on nama_bahan';

    /**
     * Mapping nama bahan to COA code
     */
    protected $coaMappings = [
        'Ayam Potong' => '1141',
        'Ayam Kampung' => '1142',
        'Bebek' => '1143',
        'Ayam Lainnya' => '1144',
        'Jagung' => '1145',
        'Air' => '1150',
        'Minyak Goreng' => '1151',
        'Minyak' => '1151', // alias
        'Tepung Terigu' => '1152',
        'Terigu' => '1152', // alias
        'Tepung Maizena' => '1153',
        'Maizena' => '1153', // alias
        'Lada' => '1154',
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $dryRun = $this->option('dry-run');
        
        if (!$userId) {
            $this->error('--user-id is required');
            return 1;
        }
        
        $this->info('=== Updating COA Persediaan for Bahan Baku ===');
        $this->info('User ID: ' . $userId);
        $this->info('Mode: ' . ($dryRun ? 'DRY RUN (no changes)' : 'LIVE UPDATE'));
        $this->newLine();
        
        $updated = 0;
        $skipped = 0;
        $notFound = 0;
        
        // Get all bahan baku for user
        $bahanBakus = BahanBaku::where('user_id', $userId)->get();
        
        $this->info("Found {$bahanBakus->count()} bahan baku items");
        $this->newLine();
        
        foreach ($bahanBakus as $bb) {
            $matched = false;
            
            // Try to match with COA mapping
            foreach ($this->coaMappings as $pattern => $coaCode) {
                if (stripos($bb->nama_bahan, $pattern) !== false) {
                    // Check if COA exists
                    $coa = Coa::where('kode_akun', $coaCode)
                        ->where('user_id', $userId)
                        ->first();
                    
                    if ($coa) {
                        if ($bb->coa_persediaan_id === $coaCode) {
                            $this->line("  ⏭️  {$bb->nama_bahan} (ID: {$bb->id}) - Already has COA {$coaCode}");
                            $skipped++;
                        } else {
                            if (!$dryRun) {
                                $bb->coa_persediaan_id = $coaCode;
                                $bb->save();
                            }
                            
                            $oldCoa = $bb->coa_persediaan_id ?? 'NULL';
                            $this->info("  ✅ {$bb->nama_bahan} (ID: {$bb->id}) - Updated COA: {$oldCoa} → {$coaCode} ({$coa->nama_akun})");
                            $updated++;
                        }
                        $matched = true;
                        break;
                    } else {
                        $this->warn("  ⚠️  {$bb->nama_bahan} (ID: {$bb->id}) - COA {$coaCode} not found in database");
                        $notFound++;
                        $matched = true;
                        break;
                    }
                }
            }
            
            if (!$matched && !$bb->coa_persediaan_id) {
                $this->line("  ⏭️  {$bb->nama_bahan} (ID: {$bb->id}) - No COA mapping found, keeping NULL");
                $skipped++;
            }
        }
        
        $this->newLine();
        $this->info('=== Summary ===');
        $this->line("Updated: {$updated}");
        $this->line("Skipped: {$skipped}");
        $this->line("COA Not Found: {$notFound}");
        
        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a DRY RUN. Run without --dry-run to apply changes.');
        } else {
            $this->newLine();
            $this->info('✅ Update completed successfully!');
        }
        
        return 0;
    }
}
