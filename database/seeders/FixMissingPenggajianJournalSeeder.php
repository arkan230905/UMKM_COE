<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Penggajian;
use App\Models\Pegawai;
use App\Models\JournalEntry;
use App\Models\Coa;
use App\Services\JournalService;

class FixMissingPenggajianJournalSeeder extends Seeder
{
    public function run()
    {
        $journalService = app(JournalService::class);
        
        echo "=== FIX MISSING PENGGAJIAN JOURNALS ===\n\n";
        
        // Ambil semua penggajian
        $penggajians = Penggajian::with('pegawai')->get();
        
        $fixed = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($penggajians as $penggajian) {
            $pegawai = $penggajian->pegawai;
            if (!$pegawai) {
                echo "  ❌ Penggajian #{$penggajian->id} - Pegawai tidak ditemukan\n";
                $errors++;
                continue;
            }
            
            // Cek apakah sudah ada jurnal
            $existingJournal = JournalEntry::where('ref_type', 'penggajian')
                ->where('ref_id', $penggajian->id)
                ->first();
            
            // Cari COA Beban Gaji
            $coaBebanGaji = Coa::whereRaw('LOWER(nama_akun) LIKE ?', ['%beban gaji%'])
                ->orWhere('kode_akun', '501')
                ->first();
            
            if (!$coaBebanGaji) {
                echo "  ❌ Penggajian #{$penggajian->id} - COA Beban Gaji tidak ditemukan\n";
                $errors++;
                continue;
            }
            
            // Tentukan akun kas/bank
            $cashCode = $penggajian->coa_kasbank ?? '1101';
            
            if ($existingJournal) {
                // Cek apakah jurnal menggunakan akun yang benar
                $lines = $existingJournal->lines;
                $usesCorrectAccount = false;
                
                foreach ($lines as $line) {
                    if ($line->account && $line->account->code == $cashCode && $line->credit > 0) {
                        $usesCorrectAccount = true;
                        break;
                    }
                }
                
                if ($usesCorrectAccount) {
                    echo "  ⏭️  Penggajian #{$penggajian->id} - Jurnal sudah benar\n";
                    $skipped++;
                    continue;
                }
                
                // Hapus jurnal lama dan buat baru
                echo "  🔄 Penggajian #{$penggajian->id} - Update jurnal (akun salah)\n";
                $existingJournal->delete();
            }
            
            // Buat jurnal baru
            try {
                $journalService->post(
                    $penggajian->tanggal_penggajian,
                    'penggajian',
                    (int)$penggajian->id,
                    'Penggajian - ' . $pegawai->nama,
                    [
                        ['code' => $coaBebanGaji->kode_akun, 'debit' => (float)$penggajian->total_gaji, 'credit' => 0],
                        ['code' => $cashCode, 'debit' => 0, 'credit' => (float)$penggajian->total_gaji],
                    ]
                );
                
                echo "  ✅ Penggajian #{$penggajian->id} - Jurnal berhasil dibuat\n";
                echo "     Pegawai: {$pegawai->nama}\n";
                echo "     Tanggal: {$penggajian->tanggal_penggajian}\n";
                echo "     Beban: {$coaBebanGaji->kode_akun} - {$coaBebanGaji->nama_akun}\n";
                echo "     Kas/Bank: {$cashCode}\n";
                echo "     Nominal: Rp " . number_format($penggajian->total_gaji, 0, ',', '.') . "\n\n";
                
                if ($existingJournal) {
                    $updated++;
                } else {
                    $fixed++;
                }
            } catch (\Exception $e) {
                echo "  ❌ Penggajian #{$penggajian->id} - ERROR: {$e->getMessage()}\n\n";
                $errors++;
            }
        }
        
        echo "\n=== SUMMARY ===\n";
        echo "Total Penggajian: " . $penggajians->count() . "\n";
        echo "✅ Fixed (baru): {$fixed}\n";
        echo "🔄 Updated (diperbaiki): {$updated}\n";
        echo "⏭️  Skipped (sudah benar): {$skipped}\n";
        echo "❌ Errors: {$errors}\n";
        echo "\n=== SELESAI ===\n";
    }
}
