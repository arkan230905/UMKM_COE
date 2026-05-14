<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateCorrectProduksiJournalSeeder extends Seeder
{
    public function run(): void
    {
        Log::info('Starting CreateCorrectProduksiJournalSeeder');
        
        $user_id = 7;
        $produksiId = 1;
        
        // Delete existing produksi journals
        DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->whereIn('tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer'])
            ->delete();
        
        // Create BBB journals
        DB::table('jurnal_umum')->insert([
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1171', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Konsumsi BBB untuk Produksi Jasuke',
                'debit' => 300000,
                'kredit' => 0,
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_bbb',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1141', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Konsumsi Jagung untuk Produksi',
                'debit' => 0,
                'kredit' => 300000,
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_bbb',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
        
        // Create BTKL journals
        DB::table('jurnal_umum')->insert([
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1172', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Alokasi BTKL untuk Produksi Jasuke',
                'debit' => 54000,
                'kredit' => 0,
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_btkl',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('211', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Hutang Gaji untuk Produksi',
                'debit' => 0,
                'kredit' => 54000,
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_btkl',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
        
        // Create BOP journals from bop_proses data
        $bopProses = DB::table('bop_proses')
            ->where('user_id', $user_id)
            ->where('is_active', true)
            ->get();
        
        foreach ($bopProses as $bop) {
            $komponenBop = json_decode($bop->komponen_bop, true) ?? [];
            
            foreach ($komponenBop as $komponen) {
                $coaDebit = $komponen['coa_debit'] ?? '1173';
                $coaKredit = $komponen['coa_kredit'] ?? '210';
                $amount = $komponen['rate_per_hour'] ?? 0;
                
                DB::table('jurnal_umum')->insert([
                    [
                        'user_id' => $user_id,
                        'coa_id' => $this->getCoaIdByKode($coaDebit, $user_id),
                        'tanggal' => '2026-05-09',
                        'keterangan' => 'Alokasi BOP untuk Produksi Jasuke',
                        'debit' => $amount * 1000, // Assuming 1000 units
                        'kredit' => 0,
                        'referensi' => $produksiId,
                        'tipe_referensi' => 'produksi_bop',
                        'created_by' => $user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'user_id' => $user_id,
                        'coa_id' => $this->getCoaIdByKode($coaKredit, $user_id),
                        'tanggal' => '2026-05-09',
                        'keterangan' => 'Alokasi BOP untuk Produksi Jasuke',
                        'debit' => 0,
                        'kredit' => $amount * 1000,
                        'referensi' => $produksiId,
                        'tipe_referensi' => 'produksi_bop',
                        'created_by' => $user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
            }
        }
        
        // Create transfer journals
        DB::table('jurnal_umum')->insert([
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1161', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Transfer WIP ke Barang Jadi - Jasuke',
                'debit' => 644640,
                'kredit' => 0,
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_transfer',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1171', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Transfer WIP BBB ke Barang Jadi',
                'debit' => 0,
                'kredit' => 300000,
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_transfer',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1172', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Transfer WIP BTKL ke Barang Jadi',
                'debit' => 0,
                'kredit' => 54000,
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_transfer',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1173', $user_id),
                'tanggal' => '2026-05-09',
                'keterangan' => 'Transfer WIP BOP ke Barang Jadi',
                'debit' => 0,
                'kredit' => 290640,
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_transfer',
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
        
        Log::info('Correct produksi journal entries created successfully!');
        $this->command->info('Correct produksi journal structure created!');
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
