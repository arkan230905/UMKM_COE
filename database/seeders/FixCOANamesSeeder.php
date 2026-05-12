<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixCOANamesSeeder extends Seeder
{
    /**
     * Fix COA names to match production data
     * Update COA names for BOP components:
     * - COA 530: Change to "BOP - Listrik"
     * - COA 531: Change to "BOP - Susu"
     * - COA 532: Change to "BOP - Kemasan"
     */
    public function run(): void
    {
        Log::info('Starting FixCOANamesSeeder');
        
        $user_id = 7;
        
        // Fix COA names
        $coaUpdates = [
            [
                'kode_akun' => '530',
                'nama_akun' => 'BOP - Listrik',
                'user_id' => $user_id
            ],
            [
                'kode_akun' => '531',
                'nama_akun' => 'BOP - Susu',
                'user_id' => $user_id
            ],
            [
                'kode_akun' => '532',
                'nama_akun' => 'BOP - Kemasan',
                'user_id' => $user_id
            ]
        ];
        
        $updatedCount = 0;
        
        foreach ($coaUpdates as $update) {
            $affected = DB::table('coas')
                ->where('kode_akun', $update['kode_akun'])
                ->where('user_id', $user_id)
                ->update([
                    'nama_akun' => $update['nama_akun'],
                    'updated_at' => now()
                ]);
            
            if ($affected > 0) {
                $updatedCount++;
                Log::info("Updated COA {$update['kode_akun']} to '{$update['nama_akun']}'");
            }
        }
        
        // Verify COA names
        $coaList = DB::table('coas')
            ->where('user_id', $user_id)
            ->whereIn('kode_akun', ['530', '531', '532'])
            ->select('kode_akun', 'nama_akun')
            ->get();
        
        Log::info("COA Names After Fix:");
        foreach ($coaList as $coa) {
            Log::info("- COA {$coa->kode_akun}: {$coa->nama_akun}");
        }
        
        // Recreate BOP journals to reflect correct COA names
        $user_id = 7;
        $produksiId = 1;
        
        // Delete existing BOP journals
        DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_bop')
            ->delete();
        
        // Create correct BOP journals
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
            
            // Credit entries with correct COA
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
        
        Log::info("COA names and BOP journals fix completed!");
        Log::info("Updated COA names: {$updatedCount}");
        
        $this->command->info('COA names and BOP journals fix completed!');
        $this->command->info("Updated COA names: {$updatedCount}");
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
