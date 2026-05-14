<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CorrectBOPDataSeeder extends Seeder
{
    /**
     * Correct BOP data to match production data exactly
     * Remove Keju component (Rp 120.000) that doesn't exist in production data
     * Use only components that exist in production data
     */
    public function run(): void
    {
        Log::info('Starting CorrectBOPDataSeeder');
        
        $user_id = 7;
        $produksiId = 1;
        
        // Delete all existing BOP journals
        DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_bop')
            ->delete();
        
        // Create correct BOP journals with exact production data
        DB::table('jurnal_umum')->insert([
            // Debit entry - total BOP (sum of all production components)
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1173', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BOP untuk Produksi Jasuke',
                'debit' => 170640, // 8040 + 3360 + 33360 + 77880 + 48000 = 170640
                'kredit' => 0,
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_bop',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Credit entries for actual production components
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('210', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BOP untuk Produksi Jasuke',
                'debit' => 0,
                'kredit' => 8040,  // Gas / BBM
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_bop',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('210', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BOP untuk Produksi Jasuke',
                'debit' => 0,
                'kredit' => 3360,  // Air & Kebersihan
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_bop',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('530', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BOP untuk Produksi Jasuke',
                'debit' => 0,
                'kredit' => 33360, // Listrik
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_bop',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('531', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BOP untuk Produksi Jasuke',
                'debit' => 0,
                'kredit' => 77880, // Susu
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_bop',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('532', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BOP untuk Produksi Jasuke',
                'debit' => 0,
                'kredit' => 48000, // Cup
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_bop',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
        
        // Update transfer WIP BOP to match correct total
        DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_transfer')
            ->where('keterangan', 'Transfer WIP BOP ke Barang Jadi')
            ->update(['kredit' => 170640]);
        
        // Update transfer WIP total to match correct total
        $totalWIPTransfer = 300000 + 54000 + 170640; // BBB + BTKL + BOP
        DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_transfer')
            ->where('keterangan', 'Transfer WIP ke Barang Jadi - Jasuke')
            ->update(['debit' => $totalWIPTransfer]);
        
        // Verify balance
        $totalDebit = DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_bop')
            ->sum('debit');
            
        $totalKredit = DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_bop')
            ->sum('kredit');
        
        Log::info("Correct BOP Data Fixed:");
        Log::info("- Total Debit: Rp " . number_format($totalDebit, 2, ',', '.'));
        Log::info("- Total Kredit: Rp " . number_format($totalKredit, 2, ',', '.'));
        Log::info("- Balance: Rp " . number_format($totalDebit - $totalKredit, 2, ',', '.'));
        
        Log::info("Production Data Components:");
        Log::info("- Gas / BBM: Rp 8.040");
        Log::info("- Air & Kebersihan: Rp 3.360");
        Log::info("- Listrik: Rp 33.360");
        Log::info("- Susu: Rp 77.880");
        Log::info("- Cup: Rp 48.000");
        Log::info("- Total: Rp 170.640");
        Log::info("- Removed: Keju (Rp 120.000) - not in production data");
        
        if ($totalDebit == $totalKredit) {
            Log::info('SUCCESS: BOP data is now correct and balanced!');
        } else {
            Log::warning('WARNING: BOP data still not balanced');
        }
        
        $this->command->info('Correct BOP data fix completed!');
        $this->command->info("Final balance: " . ($totalDebit - $totalKredit));
    }
    
    private function getCoaIdByKode($kodeAkun, $user_id)
    {
        $coa = DB::table('accounts')
            ->where('kode_akun', $kodeAkun)
            ->where('user_id', $user_id)
            ->first();
        
        return $coa ? $coa->id : null;
    }
}
