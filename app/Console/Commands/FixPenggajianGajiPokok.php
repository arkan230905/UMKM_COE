<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penggajian;
use App\Models\JurnalUmum;
use Illuminate\Support\Facades\DB;

class FixPenggajianGajiPokok extends Command
{
    protected $signature = 'penggajian:fix-gaji-pokok {--dry-run : Preview without making changes}';
    protected $description = 'Fix penggajian records where gaji_pokok is 0 but total_gaji > 0';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in DRY-RUN mode. No changes will be made.');
        }

        // Find penggajian with gaji_pokok = 0 but total_gaji > 0
        $problematicPenggajians = Penggajian::where('gaji_pokok', 0)
            ->where('total_gaji', '>', 0)
            ->with('pegawai')
            ->get();

        if ($problematicPenggajians->isEmpty()) {
            $this->info('✅ No problematic penggajian records found. All good!');
            return 0;
        }

        $this->info("Found {$problematicPenggajians->count()} penggajian record(s) with gaji_pokok = 0:");
        $this->newLine();

        foreach ($problematicPenggajians as $penggajian) {
            $this->line("ID: {$penggajian->id} ({$penggajian->nomor_penggajian})");
            $this->line("  Pegawai: {$penggajian->pegawai->nama}");
            $this->line("  Tanggal: {$penggajian->tanggal_penggajian}");
            $this->line("  Gaji Pokok: Rp " . number_format($penggajian->gaji_pokok, 0, ',', '.'));
            $this->line("  Total Tunjangan: Rp " . number_format($penggajian->total_tunjangan, 0, ',', '.'));
            $this->line("  Bonus: Rp " . number_format($penggajian->bonus, 0, ',', '.'));
            $this->line("  Asuransi: Rp " . number_format($penggajian->asuransi, 0, ',', '.'));
            $this->line("  Potongan: Rp " . number_format($penggajian->potongan, 0, ',', '.'));
            $this->line("  Total Gaji: Rp " . number_format($penggajian->total_gaji, 0, ',', '.'));

            // Calculate what gaji_pokok should be
            // Formula: total_gaji = gaji_pokok + tunjangan + bonus - asuransi - potongan
            // So: gaji_pokok = total_gaji - tunjangan - bonus + asuransi + potongan
            $calculatedGajiPokok = $penggajian->total_gaji 
                                 - $penggajian->total_tunjangan 
                                 - $penggajian->bonus 
                                 + $penggajian->asuransi 
                                 + $penggajian->potongan;

            $this->warn("  → Calculated Gaji Pokok should be: Rp " . number_format($calculatedGajiPokok, 0, ',', '.'));

            if (!$dryRun) {
                DB::transaction(function() use ($penggajian, $calculatedGajiPokok) {
                    // Update gaji_pokok
                    $penggajian->update([
                        'gaji_pokok' => $calculatedGajiPokok
                    ]);

                    // Delete old journal entries
                    JurnalUmum::where('referensi', $penggajian->id)
                        ->where('tipe_referensi', 'penggajian')
                        ->delete();

                    $this->info("  ✅ Updated gaji_pokok and deleted old journal entries");
                    $this->comment("  ⚠️  Please re-post this penggajian from the web UI to create correct journal entries");
                });
            } else {
                $this->comment("  Would update gaji_pokok to Rp " . number_format($calculatedGajiPokok, 0, ',', '.'));
                $this->comment("  Would delete journal entries and require re-posting");
            }
            
            $this->newLine();
        }

        if (!$dryRun) {
            $this->info("Successfully fixed {$problematicPenggajians->count()} penggajian record(s)!");
            $this->warn("IMPORTANT: Please re-post these penggajian records from the web UI to create correct journal entries.");
        } else {
            $this->info("DRY-RUN complete. Run without --dry-run to apply changes.");
        }

        return 0;
    }
}
