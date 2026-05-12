<?php

namespace App\Console\Commands;

use App\Models\ProsesProduksi;
use Illuminate\Console\Command;

class FixBtklConsistency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'btkl:fix-consistency {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix BTKL tariff inconsistencies based on employee qualifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('Checking BTKL consistency...');
        
        $btkls = ProsesProduksi::with('jabatan.pegawais')->get();
        $inconsistentCount = 0;
        $fixedCount = 0;
        
        foreach ($btkls as $btkl) {
            $validation = $btkl->validateTarifConsistency();
            
            if (!$validation['is_consistent']) {
                $inconsistentCount++;
                
                $this->warn("Inconsistent BTKL found:");
                $this->line("  - Kode: {$btkl->kode_proses}");
                $this->line("  - Nama: {$btkl->nama_proses}");
                $this->line("  - Jabatan: {$validation['jabatan_name']}");
                $this->line("  - Jumlah Pegawai: {$validation['jumlah_pegawai']}");
                $this->line("  - Current Tarif: Rp " . number_format($validation['current_tarif'], 0, ',', '.'));
                $this->line("  - Expected Tarif: Rp " . number_format($validation['expected_tarif'], 0, ',', '.'));
                
                if (!$isDryRun) {
                    $btkl->update(['tarif_btkl' => $validation['expected_tarif']]);
                    $fixedCount++;
                    $this->info("  ✓ Fixed!");
                } else {
                    $this->line("  → Would fix to: Rp " . number_format($validation['expected_tarif'], 0, ',', '.'));
                }
                
                $this->line('');
            }
        }
        
        if ($inconsistentCount === 0) {
            $this->info('✓ All BTKL data is consistent!');
        } else {
            if ($isDryRun) {
                $this->warn("Found {$inconsistentCount} inconsistent BTKL records.");
                $this->info("Run without --dry-run to fix them.");
            } else {
                $this->info("Fixed {$fixedCount} out of {$inconsistentCount} inconsistent BTKL records.");
            }
        }
        
        return 0;
    }
}