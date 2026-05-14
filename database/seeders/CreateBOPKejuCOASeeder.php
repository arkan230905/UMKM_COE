<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateBOPKejuCOASeeder extends Seeder
{
    /**
     * Create separate COA account for BOP Keju (different from BOP Kemasan)
     * Update BOP journal to use correct COA for Keju component
     */
    public function run(): void
    {
        Log::info('Starting CreateBOPKejuCOASeeder');
        
        $user_id = 7;
        
        // Create COA for BOP Keju (separate from BOP Kemasan)
        $kejuCOA = [
            'user_id' => $user_id,
            'kode_akun' => '533', // Use next available code
            'nama_akun' => 'BOP - Keju',
            'tipe_akun' => 'Beban',
            'saldo_normal' => 'kredit',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Check if COA 533 already exists
        $existingCOA = DB::table('accounts')
            ->where('kode_akun', '533')
            ->where('user_id', $user_id)
            ->first();
        
        if (!$existingCOA) {
            DB::table('accounts')->insert($kejuCOA);
            Log::info("Created COA 533: BOP - Keju");
        } else {
            Log::info("COA 533: BOP - Keju already exists");
        }
        
        // Get COA ID for BOP Keju
        $kejuCOAId = $this->getCoaIdByKode('533', $user_id);
        
        // Update BOP journals to use correct COA for Keju
        $produksiId = 1;
        
        // Delete existing BOP journals
        DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_bop')
            ->delete();
        
        // Create complete BOP journals with correct COA mapping
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
                'coa_id' => $kejuCOAId, // Use COA 533 for Keju
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BOP untuk Produksi Jasuke',
                'debit' => 0,
                'kredit' => 120000, // Keju (with correct COA!)
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
        
        Log::info("BOP Keju COA Created:");
        Log::info("- Total Debit: Rp " . number_format($totalDebit, 2, ',', '.'));
        Log::info("- Total Kredit: Rp " . number_format($totalKredit, 2, ',', '.'));
        Log::info("- Balance: Rp " . number_format($totalDebit - $totalKredit, 2, ',', '.'));
        
        Log::info("Correct BOP COA Mapping:");
        Log::info("- Gas / BBM: COA 210 - Rp 8.040");
        Log::info("- Air & Kebersihan: COA 210 - Rp 3.360");
        Log::info("- Listrik: COA 530 - Rp 33.360");
        Log::info("- Susu: COA 531 - Rp 77.880");
        Log::info("- Keju: COA 533 - Rp 120.000 (SEPARATE COA!)");
        Log::info("- Cup: COA 532 - Rp 48.000");
        
        if ($totalDebit == $totalKredit) {
            Log::info('SUCCESS: BOP Keju COA created and journals are balanced!');
        } else {
            Log::warning('WARNING: BOP journals still not balanced');
        }
        
        $this->command->info('BOP Keju COA created successfully!');
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
