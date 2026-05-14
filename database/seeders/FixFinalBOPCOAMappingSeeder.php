<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixFinalBOPCOAMappingSeeder extends Seeder
{
    /**
     * Fix final BOP COA mapping based on actual production data
     * Ensure each component uses correct COA account
     */
    public function run(): void
    {
        Log::info('Starting FixFinalBOPCOAMappingSeeder');
        
        $user_id = 7;
        $produksiId = 1;
        
        // Delete all existing BOP journals
        DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_bop')
            ->delete();
        
        // Create BOP journals with CORRECT COA mapping based on production data
        DB::table('jurnal_umum')->insert([
            // Debit entry - total BOP
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1173', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BOP untuk Produksi Jasuke',
                'debit' => 290640,
                'kredit' => 0,
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_bop',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Credit entries with CORRECT COA mapping
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('552', $user_id), // BOP - Gas
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BOP - Pengukusan (Gas / BBM) ke Barang WIP BOP',
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
                'coa_id' => $this->getCoaIdByKode('536', $user_id), // Biaya Air & Kebersihan
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BOP - Pengukusan (Air & Kebersihan) ke Barang WIP BOP',
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
                'coa_id' => $this->getCoaIdByKode('550', $user_id), // BOP - Listrik
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BOP - Pengemasan Dan Pengtopingan (Listrik) ke Barang WIP BOP',
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
                'coa_id' => $this->getCoaIdByKode('531', $user_id), // BOP - Susu
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BOP - Pengemasan Dan Pengtopingan (Susu) ke Barang WIP BOP',
                'debit' => 0,
                'kredit' => 77880, // Susu (CORRECT COA!)
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_bop',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('533', $user_id), // BOP - Keju
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BOP - Pengemasan Dan Pengtopingan (Keju) ke Barang WIP BOP',
                'debit' => 0,
                'kredit' => 120000, // Keju (CORRECT COA!)
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_bop',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('532', $user_id), // BOP - Kemasan
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BOP - Pengemasan Dan Pengtopingan (Cup) ke Barang WIP BOP',
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
            ->update(['kredit' => 290640]);
        
        // Update transfer WIP total
        $totalWIPTransfer = 300000 + 54000 + 290640; // BBB + BTKL + BOP
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
        
        Log::info("Final BOP COA Mapping Fixed:");
        Log::info("- Total Debit: Rp " . number_format($totalDebit, 2, ',', '.'));
        Log::info("- Total Kredit: Rp " . number_format($totalKredit, 2, ',', '.'));
        Log::info("- Balance: Rp " . number_format($totalDebit - $totalKredit, 2, ',', '.'));
        
        Log::info("Correct BOP COA Mapping:");
        Log::info("- Gas / BBM: COA 552 - Rp 8.040");
        Log::info("- Air & Kebersihan: COA 536 - Rp 3.360");
        Log::info("- Listrik: COA 550 - Rp 33.360");
        Log::info("- Susu: COA 531 - Rp 77.880 (FIXED!)");
        Log::info("- Keju: COA 533 - Rp 120.000 (FIXED!)");
        Log::info("- Cup: COA 532 - Rp 48.000");
        
        if ($totalDebit == $totalKredit) {
            Log::info('SUCCESS: Final BOP COA mapping is correct and balanced!');
        } else {
            Log::warning('WARNING: Final BOP COA mapping still not balanced');
        }
        
        $this->command->info('Final BOP COA mapping fixed successfully!');
        $this->command->info("Final balance: " . ($totalDebit - $totalKredit));
    }
    
    private function getCoaIdByKode($kodeAkun, $user_id)
    {
        $coa = DB::table('coas')
            ->where('kode_akun', $kodeAkun)
            ->where('user_id', $user_id)
            ->first();
        
        return $coa ? $coa->id : null;
    }
}
