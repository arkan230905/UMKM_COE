<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinalBOPCOAMappingSeeder extends Seeder
{
    /**
     * Final fix for BOP COA mapping to match production data exactly
     * Correct mapping based on actual production data:
     * - Gas / BBM (Rp 8.040): COA 210 (Hutang Usaha)
     * - Air & Kebersihan (Rp 3.360): COA 210 (Hutang Usaha)
     * - Listrik (Rp 33.360): COA 530 (BOP - Listrik)
     * - Susu (Rp 77.880): COA 531 (BOP - Susu)
     * - Keju (Rp 120.000): COA 532 (BOP - Keju)
     * - Cup (Rp 48.000): COA 532 (BOP - Kemasan)
     */
    public function run(): void
    {
        Log::info('Starting FinalBOPCOAMappingSeeder');
        
        $user_id = 7;
        $produksiId = 1;
        
        // Delete all existing BOP journals
        DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_bop')
            ->delete();
        
        // Create correct BOP journals with exact COA mapping
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
            
            // Credit entries with correct COA mapping
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
        
        // Verify balance and COA mapping
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
        
        // Get COA details for verification
        $coaDetails = DB::table('jurnal_umum')
            ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
            ->where('jurnal_umum.user_id', $user_id)
            ->where('jurnal_umum.referensi', $produksiId)
            ->where('jurnal_umum.tipe_referensi', 'produksi_bop')
            ->where('jurnal_umum.kredit', '>', 0)
            ->select('coas.kode_akun', 'coas.nama_akun', 'jurnal_umum.kredit')
            ->get();
        
        Log::info("Final BOP COA Mapping Fixed:");
        Log::info("- Total Debit: Rp " . number_format($totalDebit, 2, ',', '.'));
        Log::info("- Total Kredit: Rp " . number_format($totalKredit, 2, ',', '.'));
        Log::info("- Balance: Rp " . number_format($totalDebit - $totalKredit, 2, ',', '.'));
        
        Log::info("COA Mapping Details:");
        foreach ($coaDetails as $detail) {
            Log::info("- COA {$detail->kode_akun}: Rp " . number_format($detail->kredit, 2, ',', '.') . " ({$detail->nama_akun})");
        }
        
        if ($totalDebit == $totalKredit) {
            Log::info('SUCCESS: Final BOP COA mapping is correct and balanced!');
        } else {
            Log::warning('WARNING: Final BOP COA mapping still not balanced');
        }
        
        $this->command->info('Final BOP COA mapping fix completed!');
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
