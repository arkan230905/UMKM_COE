<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoaJagungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder untuk COA bisnis JAGUNG (Corn)
     * Includes: Diskon Pembelian, WIP accounts, HPP, Bank accounts
     */
    public function run()
    {
        // User ID 0 = Template untuk semua user baru
        $userId = 0;
        
        $coaData = [
            // 1. Diskon Pembelian (Biaya/Expense)
            [
                'user_id' => $userId,
                'kode_akun' => '559',
                'nama_akun' => 'Diskon Pembelian',
                'tipe_akun' => 'Biaya',
                'kategori_akun' => 'Biaya',
                'is_akun_header' => 0,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'saldo_awal' => 0.00,
                'tanggal_saldo_awal' => null,
                'posted_saldo_awal' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // 2. WIP (Work in Process) - CORRECTED CODES: 1171, 1172, 1173
            [
                'user_id' => $userId,
                'kode_induk' => '117', // Parent: 117 - Persediaan Barang Dalam Proses
                'kode_akun' => '1171',
                'nama_akun' => 'Pers. Barang Dalam Proses - BBB (WIP BBB)',
                'tipe_akun' => 'Aset',
                'kategori_akun' => '-',
                'is_akun_header' => 0,
                'saldo_normal' => 'debit',
                'saldo_awal' => 0.00,
                'tanggal_saldo_awal' => null,
                'posted_saldo_awal' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'kode_induk' => '117', // Parent: 117
                'kode_akun' => '1172',
                'nama_akun' => 'Pers. Barang Dalam Proses - BTKL (WIP BTKL)',
                'tipe_akun' => 'Aset',
                'kategori_akun' => '-',
                'is_akun_header' => 0,
                'saldo_normal' => 'debit',
                'saldo_awal' => 0.00,
                'tanggal_saldo_awal' => null,
                'posted_saldo_awal' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'kode_induk' => '117', // Parent: 117
                'kode_akun' => '1173',
                'nama_akun' => 'Pers. Barang Dalam Proses - BOP (WIP BOP)',
                'tipe_akun' => 'Aset',
                'kategori_akun' => '-',
                'is_akun_header' => 0,
                'saldo_normal' => 'debit',
                'saldo_awal' => 0.00,
                'tanggal_saldo_awal' => null,
                'posted_saldo_awal' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // 3. Harga Pokok Penjualan (HPP)
            [
                'user_id' => $userId,
                'kode_induk' => '5', // Parent: 5 - Beban
                'kode_akun' => '56',
                'nama_akun' => 'Harga Pokok Penjualan',
                'tipe_akun' => 'Beban',
                'kategori_akun' => '-',
                'is_akun_header' => 0,
                'saldo_normal' => 'debit',
                'saldo_awal' => 0.00,
                'tanggal_saldo_awal' => null,
                'posted_saldo_awal' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // 4. Bank Accounts (if not exist)
            [
                'user_id' => $userId,
                'kode_induk' => '111', // Parent: 111 - Kas Bank
                'kode_akun' => '1111',
                'nama_akun' => 'Bank BCA',
                'tipe_akun' => 'Aset',
                'kategori_akun' => '-',
                'is_akun_header' => 0,
                'saldo_normal' => 'debit',
                'saldo_awal' => 0.00,
                'tanggal_saldo_awal' => null,
                'posted_saldo_awal' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'kode_induk' => '111', // Parent: 111
                'kode_akun' => '1112',
                'nama_akun' => 'Bank Mandiri',
                'tipe_akun' => 'Aset',
                'kategori_akun' => '-',
                'is_akun_header' => 0,
                'saldo_normal' => 'debit',
                'saldo_awal' => 0.00,
                'tanggal_saldo_awal' => null,
                'posted_saldo_awal' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        foreach ($coaData as $coa) {
            // Check if COA already exists for this user
            $exists = DB::table('coas')
                ->where('user_id', $userId)
                ->where('kode_akun', $coa['kode_akun'])
                ->exists();
            
            if (!$exists) {
                DB::table('coas')->insert($coa);
                $this->command->info("✅ COA {$coa['kode_akun']} - {$coa['nama_akun']} berhasil ditambahkan");
            } else {
                $this->command->warn("⚠️ COA {$coa['kode_akun']} - {$coa['nama_akun']} sudah ada, skip");
            }
        }
        
        $this->command->info("✅ Seeder COA Jagung selesai dijalankan!");
    }
}
