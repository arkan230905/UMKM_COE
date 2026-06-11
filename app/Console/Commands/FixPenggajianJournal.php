<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penggajian;
use App\Models\JurnalUmum;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;

class FixPenggajianJournal extends Command
{
    protected $signature = 'penggajian:fix-journal {penggajian_id?}';
    protected $description = 'Re-create journal entries for penggajian (fix missing gaji_pokok entries)';

    public function handle()
    {
        $penggajianId = $this->argument('penggajian_id');
        
        if ($penggajianId) {
            $penggajians = Penggajian::where('id', $penggajianId)->get();
        } else {
            // Fix all penggajian - get the latest ones
            $penggajians = Penggajian::orderBy('id', 'desc')->take(10)->get();
        }
        
        if ($penggajians->count() == 0) {
            $this->error('No penggajian found!');
            return 1;
        }
        
        $this->info("Found {$penggajians->count()} penggajian records to fix.");
        
        foreach ($penggajians as $pg) {
            $this->info("\n========================================");
            $this->info("Processing Penggajian ID: {$pg->id}");
            
            try {
                DB::beginTransaction();
                
                // Delete old journal entries
                $deleted = JurnalUmum::where('tipe_referensi', 'penggajian')
                    ->where('referensi', (string)$pg->id)
                    ->delete();
                
                $this->info("Deleted {$deleted} old journal entries");
                
                // Re-create journal entries using the controller method
                $pegawai = Pegawai::with('jabatanRelasi')->find($pg->pegawai_id);
                
                if (!$pegawai) {
                    $this->error("Pegawai not found for penggajian ID {$pg->id}");
                    DB::rollBack();
                    continue;
                }
                
                $controller = new \App\Http\Controllers\PenggajianController();
                $reflection = new \ReflectionClass($controller);
                $method = $reflection->getMethod('createJournalEntry');
                $method->setAccessible(true);
                
                // Call the private method
                $method->invoke($controller, $pg, $pegawai);
                
                DB::commit();
                
                $this->info("✅ Journal entries re-created successfully!");
                
                // Show the new entries
                $journals = JurnalUmum::with('coa')
                    ->where('tipe_referensi', 'penggajian')
                    ->where('referensi', (string)$pg->id)
                    ->get();
                
                $totalDebit = 0;
                $totalKredit = 0;
                
                foreach ($journals as $j) {
                    $totalDebit += $j->debit;
                    $totalKredit += $j->kredit;
                    
                    $this->line(sprintf(
                        "  %-10s %-30s  D: %12s  K: %12s",
                        $j->coa->kode_akun ?? 'N/A',
                        substr($j->coa->nama_akun ?? 'N/A', 0, 30),
                        $j->debit > 0 ? number_format($j->debit, 0) : '-',
                        $j->kredit > 0 ? number_format($j->kredit, 0) : '-'
                    ));
                }
                
                $this->info("TOTALS: Debit: " . number_format($totalDebit, 0) . " | Kredit: " . number_format($totalKredit, 0));
                
                if ($totalDebit == $totalKredit) {
                    $this->info("✅ BALANCED!");
                } else {
                    $this->error("❌ NOT BALANCED! Diff: " . number_format(abs($totalDebit - $totalKredit), 0));
                }
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Error fixing penggajian ID {$pg->id}: " . $e->getMessage());
                $this->error($e->getTraceAsString());
            }
        }
        
        $this->info("\n========================================");
        $this->info("Done!");
        
        return 0;
    }
}
