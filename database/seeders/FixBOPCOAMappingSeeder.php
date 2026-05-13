<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixBOPCOAMappingSeeder extends Seeder
{
    /**
     * Fix BOP COA mapping to match actual production data
     * Correct COA mapping: Gas/BBM=210, Air=210, Listrik=530, Susu=531, Keju=532, Cup=532
     */
    public function run(): void
    {
        Log::info('Starting FixBOPCOAMappingSeeder');
        
        $user_id = 7;
        $produksiId = 1;
        
        // Delete existing BOP journals
        DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_bop')
            ->delete();
        
        // Create correct BOP journals with proper COA mapping
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
            
            // Credit entries - individual components with correct COA
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
                'kredit' => 120000, // Keju
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
        
        Log::info("BOP COA Mapping Fixed:");
        Log::info("- Total Debit: Rp " . number_format($totalDebit, 2, ',', '.'));
        Log::info("- Total Kredit: Rp " . number_format($totalKredit, 2, ',', '.'));
        Log::info("- Balance: Rp " . number_format($totalDebit - $totalKredit, 2, ',', '.'));
        
        // Log COA mapping details
        Log::info("COA Mapping:");
        Log::info("- Gas / BBM (Rp 8.040): COA 210 (Hutang Usaha)");
        Log::info("- Air & Kebersihan (Rp 3.360): COA 210 (Hutang Usaha)");
        Log::info("- Listrik (Rp 33.360): COA 530 (BOP - Listrik)");
        Log::info("- Susu (Rp 77.880): COA 531 (BOP - Susu)");
        Log::info("- Keju (Rp 120.000): COA 532 (BOP - Keju)");
        Log::info("- Cup (Rp 48.000): COA 532 (BOP - Kemasan)");
        
        if ($totalDebit == $totalKredit) {
            Log::info('SUCCESS: BOP COA mapping is now correct and balanced!');
        } else {
            Log::warning('WARNING: BOP COA mapping still not balanced');
        }
        
        $this->command->info('BOP COA mapping fix completed!');
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
