<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penggajian;
use App\Models\JurnalUmum;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class FixPenggajianJournal extends Command
{
    protected $signature = 'fix:penggajian-journal';
    protected $description = 'Create missing journal entries for penggajian';

    public function handle()
    {
        $this->info('=== FIXING PENGGAJIAN JOURNAL ENTRIES ===');
        
        try {
            DB::beginTransaction();
            
            // Get all penggajian that haven't been posted to journal
            $penggajianList = Penggajian::where('status_pembayaran', 'belum_lunas')->get();
            
            $this->info("Found " . $penggajianList->count() . " unpaid penggajian records");
            
            foreach ($penggajianList as $penggajian) {
                $this->info("\nProcessing Penggajian ID: {$penggajian->id}");
                $this->info("Date: {$penggajian->tanggal_penggajian}");
                $this->info("Total Gaji: " . number_format($penggajian->total_gaji, 0, ',', '.'));
                
                // Get pegawai info
                $pegawai = $penggajian->pegawai;
                if (!$pegawai) {
                    $this->error("ERROR: Pegawai not found for penggajian ID {$penggajian->id}");
                    continue;
                }
                
                $this->info("Pegawai: {$pegawai->nama}");
                
                // Check if journal entries already exist
                $existingJournal = JurnalUmum::where('tipe_referensi', 'penggajian')
                    ->where('referensi', $penggajian->id)
                    ->exists();
                    
                if ($existingJournal) {
                    $this->info("Journal entries already exist, skipping...");
                    continue;
                }
                
                // Get required COA accounts
                $coaBebanGaji = Coa::where('kode_akun', '52')->first(); // BTKL
                if (!$coaBebanGaji) {
                    $coaBebanGaji = Coa::where('kode_akun', '54')->first(); // BOP TENAGA KERJA TIDAK LANGSUNG
                }
                
                $coaKasBank = Coa::where('kode_akun', $penggajian->coa_kasbank)->first();
                
                if (!$coaBebanGaji) {
                    $this->error("ERROR: COA Beban Gaji not found");
                    continue;
                }
                
                if (!$coaKasBank) {
                    $this->error("ERROR: COA Kas/Bank not found for code: {$penggajian->coa_kasbank}");
                    continue;
                }
                
                $this->info("Using Beban Account: {$coaBebanGaji->kode_akun} - {$coaBebanGaji->nama_akun}");
                $this->info("Using Kas/Bank Account: {$coaKasBank->kode_akun} - {$coaKasBank->nama_akun}");
                
                // Create journal entries
                $keterangan = "Penggajian {$pegawai->nama}";
                
                // DEBIT: Beban Gaji
                JurnalUmum::create([
                    'coa_id' => $coaBebanGaji->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan,
                    'debit' => $penggajian->total_gaji,
                    'kredit' => 0,
                    'referensi' => $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => 1,
                ]);
                
                // CREDIT: Kas/Bank
                JurnalUmum::create([
                    'coa_id' => $coaKasBank->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan,
                    'debit' => 0,
                    'kredit' => $penggajian->total_gaji,
                    'referensi' => $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => 1,
                ]);
                
                // Update penggajian status
                $penggajian->status_pembayaran = 'lunas';
                $penggajian->tanggal_dibayar = $penggajian->tanggal_penggajian;
                $penggajian->save();
                
                $this->info("✓ Journal entries created successfully");
                $this->info("✓ Penggajian marked as paid");
            }
            
            DB::commit();
            $this->info("\n=== ALL DONE SUCCESSFULLY ===");
            
            // Show summary
            $totalJurnalPenggajian = JurnalUmum::where('tipe_referensi', 'penggajian')->count();
            $this->info("Total penggajian journal entries now: {$totalJurnalPenggajian}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\nERROR: " . $e->getMessage());
            $this->error("Transaction rolled back.");
        }
    }
}