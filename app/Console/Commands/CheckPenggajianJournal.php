<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penggajian;
use App\Models\JurnalUmum;

class CheckPenggajianJournal extends Command
{
    protected $signature = 'penggajian:check-journal {penggajian_id?}';
    protected $description = 'Check journal entries for penggajian';

    public function handle()
    {
        $penggajianId = $this->argument('penggajian_id');
        
        if ($penggajianId) {
            $penggajians = Penggajian::where('id', $penggajianId)->get();
        } else {
            // Get last 3 penggajian
            $penggajians = Penggajian::with('pegawai')->orderBy('id', 'desc')->take(3)->get();
        }
        
        foreach ($penggajians as $pg) {
            $this->info("\n========================================");
            $this->info("Penggajian ID: {$pg->id}");
            $this->info("Pegawai: {$pg->pegawai->nama}");
            $this->info("Tanggal: {$pg->tanggal_penggajian}");
            $this->info("----------------------------------------");
            $this->info("Gaji Pokok: Rp " . number_format($pg->gaji_pokok, 0));
            $this->info("Tunjangan: Rp " . number_format($pg->total_tunjangan, 0));
            $this->info("Asuransi: Rp " . number_format($pg->asuransi, 0));
            $this->info("Total Gaji: Rp " . number_format($pg->total_gaji, 0));
            
            // Get journal entries
            $journals = JurnalUmum::with('coa')
                ->where('tipe_referensi', 'penggajian')
                ->where('referensi', (string)$pg->id)
                ->orderBy('id')
                ->get();
            
            $this->info("\n--- Jurnal Entries ---");
            $totalDebit = 0;
            $totalKredit = 0;
            
            if ($journals->count() > 0) {
                foreach ($journals as $j) {
                    $totalDebit += $j->debit;
                    $totalKredit += $j->kredit;
                    
                    $this->line(sprintf(
                        "%-15s %-35s  Debit: %15s  Kredit: %15s",
                        $j->coa->kode_akun ?? 'N/A',
                        substr($j->coa->nama_akun ?? 'N/A', 0, 35),
                        $j->debit > 0 ? 'Rp ' . number_format($j->debit, 0) : '-',
                        $j->kredit > 0 ? 'Rp ' . number_format($j->kredit, 0) : '-'
                    ));
                }
                
                $this->info("----------------------------------------");
                $this->info(sprintf("TOTAL:           Debit: Rp %s  Kredit: Rp %s", 
                    number_format($totalDebit, 0), 
                    number_format($totalKredit, 0)
                ));
                
                $diff = $totalDebit - $totalKredit;
                if ($diff == 0) {
                    $this->info("✅ BALANCE!");
                } else {
                    $this->error("❌ NOT BALANCE! Selisih: Rp " . number_format(abs($diff), 0));
                }
            } else {
                $this->warn("⚠️  NO JOURNAL ENTRIES FOUND!");
            }
        }
        
        return 0;
    }
}
