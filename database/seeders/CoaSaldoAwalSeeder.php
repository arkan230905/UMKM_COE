<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CoaSaldoAwalSeeder extends Seeder
{
    /**
     * Seeder untuk menambahkan Saldo Awal COA
     * 
     * Data ini mencakup:
     * - Aset (Kas Bank, Kas Kecil, Persediaan)
     * - Kewajiban (Hutang Usaha, Hutang Gaji, PPN)
     * - Modal
     * - Pendapatan (Penjualan)
     * - Biaya Produksi (BBB, BTKL, BOP)
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $coasWithSaldoAwal = [
            // ==========================================
            // ASET - KAS & BANK
            // ==========================================
            [
                'kode_akun' => '1111',
                'nama_akun' => 'Bank BRI',
                'tipe_akun' => 'Aset',
                'saldo_normal' => 'debit',
                'saldo_awal' => 100000000, // RP 100.000.000
                'tanggal_saldo_awal' => now()->format('Y-m-d'),
            ],
            [
                'kode_akun' => '1112',
                'nama_akun' => 'Bank BCA',
                'tipe_akun' => 'Aset',
                'saldo_normal' => 'debit',
                'saldo_awal' => 50000000, // RP 50.000.000
                'tanggal_saldo_awal' => now()->format('Y-m-d'),
            ],
            [
                'kode_akun' => '1113',
                'nama_akun' => 'Bank Mandiri',
                'tipe_akun' => 'Aset',
                'saldo_normal' => 'debit',
                'saldo_awal' => 50000000, // RP 50.000.000
                'tanggal_saldo_awal' => now()->format('Y-m-d'),
            ],
            [
                'kode_akun' => '112',
                'nama_akun' => 'Kas',
                'tipe_akun' => 'Aset',
                'saldo_normal' => 'debit',
                'saldo_awal' => 75000000, // RP 75.000.000
                'tanggal_saldo_awal' => now()->format('Y-m-d'),
            ],
            [
                'kode_akun' => '113',
                'nama_akun' => 'Kas Kecil',
                'tipe_akun' => 'Aset',
                'saldo_normal' => 'debit',
                'saldo_awal' => 1000000, // RP 1.000.000
                'tanggal_saldo_awal' => now()->format('Y-m-d'),
            ],

            // ==========================================
            // ASET - PIUTANG
            // ==========================================
            [
                'kode_akun' => '118',
                'nama_akun' => 'Piutang',
                'tipe_akun' => 'Aset',
                'saldo_normal' => 'debit',
                'saldo_awal' => 11000000, // RP 11.000.000
                'tanggal_saldo_awal' => now()->format('Y-m-d'),
            ],

            // ==========================================
            // KEWAJIBAN
            // ==========================================
            [
                'kode_akun' => '211',
                'nama_akun' => 'Hutang Usaha',
                'tipe_akun' => 'Kewajiban',
                'saldo_normal' => 'kredit',
                'saldo_awal' => 12000000, // RP 12.000.000
                'tanggal_saldo_awal' => now()->format('Y-m-d'),
            ],

            // ==========================================
            // MODAL
            // ==========================================
            [
                'kode_akun' => '311',
                'nama_akun' => 'Modal Usaha',
                'tipe_akun' => 'Modal',
                'saldo_normal' => 'kredit',
                'saldo_awal' => 275000000, // RP 275.000.000
                'tanggal_saldo_awal' => now()->format('Y-m-d'),
            ],
        ];

        // Update existing COA dengan saldo awal
        foreach ($coasWithSaldoAwal as $data) {
            $coa = DB::table('coas')
                ->where('kode_akun', $data['kode_akun'])
                ->first();

            if ($coa) {
                // Update saldo awal untuk COA yang sudah ada
                DB::table('coas')
                    ->where('kode_akun', $data['kode_akun'])
                    ->update([
                        'saldo_awal' => $data['saldo_awal'],
                        'tanggal_saldo_awal' => $data['tanggal_saldo_awal'],
                        'updated_at' => $now,
                    ]);
                
                $this->command->info("✅ Updated: {$data['kode_akun']} ({$data['nama_akun']}) - Rp " . number_format($data['saldo_awal'], 0, ',', '.'));
            } else {
                // Insert baru jika belum ada
                DB::table('coas')->insert([
                    'user_id' => null,
                    'company_id' => null,
                    'kode_akun' => $data['kode_akun'],
                    'nama_akun' => $data['nama_akun'],
                    'tipe_akun' => $data['tipe_akun'],
                    'kategori_akun' => '-',
                    'is_akun_header' => 0,
                    'kode_induk' => null,
                    'saldo_normal' => $data['saldo_normal'],
                    'saldo_awal' => $data['saldo_awal'],
                    'tanggal_saldo_awal' => $data['tanggal_saldo_awal'],
                    'posted_saldo_awal' => 0,
                    'keterangan' => null,
                    'nomor_rekening' => null,
                    'atas_nama' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                
                $this->command->info("✅ Created: {$data['kode_akun']} ({$data['nama_akun']}) - Rp " . number_format($data['saldo_awal'], 0, ',', '.'));
            }
        }

        $this->command->info("\n📊 Saldo Awal Summary:");
        $this->command->info("────────────────────────────────────");
        $this->command->info("Total Aset: Rp " . number_format(100000000 + 50000000 + 50000000 + 75000000 + 1000000 + 11000000, 0, ',', '.'));
        $this->command->info("Total Kewajiban: Rp " . number_format(12000000, 0, ',', '.'));
        $this->command->info("Total Modal: Rp " . number_format(275000000, 0, ',', '.'));
        $this->command->info("────────────────────────────────────");
    }
}
