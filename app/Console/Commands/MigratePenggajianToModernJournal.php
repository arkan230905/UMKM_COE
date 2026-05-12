<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigratePenggajianToModernJournal extends Command
{
    protected $signature = 'migrate:penggajian-to-journal';
    protected $description = 'Migrate penggajian and pembayaran beban to modern journal system';

    public function handle()
    {
        $this->info('=== MIGRATING PENGGAJIAN & PEMBAYARAN BEBAN ===');
        
        try {
            // 1. Migrate penggajian
            $this->info("\nSTEP 1: MIGRATING PENGGAJIAN");
            
            $penggajianList = DB::table('penggajians')
                ->leftJoin('pegawais', 'penggajians.pegawai_id', '=', 'pegawais.id')
                ->select('penggajians.*', 'pegawais.nama')
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('journal_entries')
                        ->whereRaw('journal_entries.ref_type = "penggajian"')
                        ->whereRaw('journal_entries.ref_id = penggajians.id');
                })
                ->get();
            
            $this->info("Found " . $penggajianList->count() . " penggajian records to migrate");
            
            foreach ($penggajianList as $penggajian) {
                // Create journal entry
                $journalEntry = DB::table('journal_entries')->insertGetId([
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'ref_type' => 'penggajian',
                    'ref_id' => $penggajian->id,
                    'memo' => "Penggajian {$penggajian->nama}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Get COA IDs
                $coaBeban = DB::table('coas')->where('kode_akun', '52')->first();
                if (!$coaBeban) {
                    $coaBeban = DB::table('coas')->where('kode_akun', '54')->first();
                }
                
                $coaKas = DB::table('coas')->where('kode_akun', $penggajian->coa_kasbank)->first();
                if (!$coaKas) {
                    $coaKas = DB::table('coas')->where('kode_akun', '111')->first();
                }
                
                // Create journal lines - DEBIT
                DB::table('journal_lines')->insert([
                    'journal_entry_id' => $journalEntry,
                    'coa_id' => $coaBeban->id,
                    'debit' => $penggajian->total_gaji,
                    'credit' => 0,
                    'memo' => "Beban Gaji {$penggajian->nama}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Create journal lines - CREDIT
                DB::table('journal_lines')->insert([
                    'journal_entry_id' => $journalEntry,
                    'coa_id' => $coaKas->id,
                    'debit' => 0,
                    'credit' => $penggajian->total_gaji,
                    'memo' => "Pembayaran Gaji {$penggajian->nama}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->info("✅ Migrated penggajian ID: {$penggajian->id} - Rp " . number_format($penggajian->total_gaji, 0, ',', '.'));
            }
            
            // 2. Migrate pembayaran beban
            $this->info("\nSTEP 2: MIGRATING PEMBAYARAN BEBAN");
            
            $bebanList = DB::table('pembayaran_bebans')
                ->leftJoin('beban_operasional', 'pembayaran_bebans.beban_operasional_id', '=', 'beban_operasional.id')
                ->select('pembayaran_bebans.*', 'beban_operasional.nama_beban')
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('journal_entries')
                        ->whereRaw('journal_entries.ref_type = "pembayaran_beban"')
                        ->whereRaw('journal_entries.ref_id = pembayaran_bebans.id');
                })
                ->get();
            
            $this->info("Found " . $bebanList->count() . " pembayaran beban records to migrate");
            
            foreach ($bebanList as $beban) {
                // Create journal entry
                $journalEntry = DB::table('journal_entries')->insertGetId([
                    'tanggal' => $beban->tanggal,
                    'ref_type' => 'pembayaran_beban',
                    'ref_id' => $beban->id,
                    'memo' => 'Pembayaran Beban: ' . ($beban->keterangan ?: 'Tanpa catatan'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Get COA IDs
                $coaBeban = DB::table('coas')->where('kode_akun', '550')->first();
                $coaKas = DB::table('coas')->where('kode_akun', '111')->first();
                
                // Create journal lines - DEBIT
                DB::table('journal_lines')->insert([
                    'journal_entry_id' => $journalEntry,
                    'coa_id' => $coaBeban->id,
                    'debit' => $beban->jumlah,
                    'credit' => 0,
                    'memo' => 'Pembayaran Beban',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Create journal lines - CREDIT
                DB::table('journal_lines')->insert([
                    'journal_entry_id' => $journalEntry,
                    'coa_id' => $coaKas->id,
                    'debit' => 0,
                    'credit' => $beban->jumlah,
                    'memo' => 'Pembayaran Beban',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->info("✅ Migrated pembayaran beban ID: {$beban->id} - Rp " . number_format($beban->jumlah, 0, ',', '.'));
            }
            
            // 3. Verification
            $this->info("\nSTEP 3: VERIFICATION");
            
            $penggajianCount = DB::table('journal_entries')->where('ref_type', 'penggajian')->count();
            $bebanCount = DB::table('journal_entries')->where('ref_type', 'pembayaran_beban')->count();
            $totalCount = DB::table('journal_entries')->count();
            
            $this->info("Total journal_entries: $totalCount");
            $this->info("Penggajian in journal_entries: $penggajianCount");
            $this->info("Pembayaran Beban in journal_entries: $bebanCount");
            
            $this->info("\n✅ MIGRATION COMPLETE!");
            $this->info("Buka: http://127.0.0.1:8000/akuntansi/jurnal-umum");
            $this->info("Data penggajian dan pembayaran beban sudah muncul!");
            
        } catch (\Exception $e) {
            $this->error("❌ ERROR: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}