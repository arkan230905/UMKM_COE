<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Perusahaan;

class CoaDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies
        $companies = Perusahaan::all();
        
        if ($companies->isEmpty()) {
            $this->command->info('No companies found. Please create companies first.');
            return;
        }

        // Default COA data provided by user
        $defaultCoaData = [
            // ASET
            ['nama_akun' => 'Aset', 'kode_akun' => '11', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset'],
            ['nama_akun' => 'Kas Bank', 'kode_akun' => '111', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 100000000, 'kategori_akun' => 'Kas & Bank'],
            ['nama_akun' => 'Kas', 'kode_akun' => '112', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 75000000, 'kategori_akun' => 'Kas & Bank'],
            ['nama_akun' => 'Kas Kecil', 'kode_akun' => '113', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Kas & Bank'],
            ['nama_akun' => 'Pers. Bahan Baku', 'kode_akun' => '114', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Lancar'],
            ['nama_akun' => 'Pers. Bahan Baku Jagung', 'kode_akun' => '1141', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Lancar'],
            ['nama_akun' => 'Pers. Bahan Pendukung', 'kode_akun' => '115', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Lancar'],
            ['nama_akun' => 'Pers. Bahan Pendukung Susu', 'kode_akun' => '1151', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Lancar'],
            ['nama_akun' => 'Pers. Bahan Pendukung Keju', 'kode_akun' => '1152', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Lancar'],
            ['nama_akun' => 'Pers. Bahan Pendukung Kemasan (Cup)', 'kode_akun' => '1153', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Lancar'],
            ['nama_akun' => 'Pers. Barang Jadi', 'kode_akun' => '116', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Lancar'],
            ['nama_akun' => 'Pers. Barang Jadi Jasuke', 'kode_akun' => '1161', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Lancar'],
            ['nama_akun' => 'Pers. Barang dalam Proses', 'kode_akun' => '117', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Lancar'],
            ['nama_akun' => 'Pers. Barang Dalam Proses - BBB', 'kode_akun' => '1171', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Lancar'],
            ['nama_akun' => 'Pers. Barang Dalam Proses - BTKL', 'kode_akun' => '1172', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Lancar'],
            ['nama_akun' => 'Pers. Barang Dalam Proses - BOP', 'kode_akun' => '1173', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Lancar'],
            ['nama_akun' => 'Piutang', 'kode_akun' => '118', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Lancar'],
            ['nama_akun' => 'Peralatan', 'kode_akun' => '119', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Tetap'],
            ['nama_akun' => 'Akumulasi Penyusutan Peralatan', 'kode_akun' => '120', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Tetap'],
            ['nama_akun' => 'Mesin', 'kode_akun' => '125', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Tetap'],
            ['nama_akun' => 'Akumulasi Penyusutan Mesin', 'kode_akun' => '126', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Tetap'],
            ['nama_akun' => 'PPN Masukkan', 'kode_akun' => '127', 'tipe_akun' => 'Aset', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Aset Lancar'],
            
            // KEWAJIBAN
            ['nama_akun' => 'Hutang', 'kode_akun' => '21', 'tipe_akun' => 'Kewajiban', 'posisi' => 'Kredit', 'saldo_awal' => 0, 'kategori_akun' => 'Kewajiban Lancar'],
            ['nama_akun' => 'Hutang Usaha', 'kode_akun' => '210', 'tipe_akun' => 'Kewajiban', 'posisi' => 'Kredit', 'saldo_awal' => 0, 'kategori_akun' => 'Kewajiban Lancar'],
            ['nama_akun' => 'Hutang Gaji', 'kode_akun' => '211', 'tipe_akun' => 'Kewajiban', 'posisi' => 'Kredit', 'saldo_awal' => 0, 'kategori_akun' => 'Kewajiban Lancar'],
            ['nama_akun' => 'PPN Keluaran', 'kode_akun' => '212', 'tipe_akun' => 'Kewajiban', 'posisi' => 'Kredit', 'saldo_awal' => 0, 'kategori_akun' => 'Kewajiban Lancar'],
            
            // EKUITAS/MODAL
            ['nama_akun' => 'Modal', 'kode_akun' => '31', 'tipe_akun' => 'Equity', 'posisi' => 'Kredit', 'saldo_awal' => 0, 'kategori_akun' => 'Modal'],
            ['nama_akun' => 'Modal Usaha', 'kode_akun' => '310', 'tipe_akun' => 'Equity', 'posisi' => 'Kredit', 'saldo_awal' => 176164000, 'kategori_akun' => 'Modal'],
            ['nama_akun' => 'Prive', 'kode_akun' => '311', 'tipe_akun' => 'Modal', 'posisi' => 'Kredit', 'saldo_awal' => 0, 'kategori_akun' => 'Modal'],
            
            // PENDAPATAN
            ['nama_akun' => 'Penjualan', 'kode_akun' => '41', 'tipe_akun' => 'Pendapatan', 'posisi' => 'Kredit', 'saldo_awal' => 0, 'kategori_akun' => 'Pendapatan Usaha'],
            ['nama_akun' => 'Penjualan - Jasuke', 'kode_akun' => '410', 'tipe_akun' => 'Pendapatan', 'posisi' => 'Kredit', 'saldo_awal' => 0, 'kategori_akun' => 'Pendapatan Usaha'],
            ['nama_akun' => 'Retur Penjualan', 'kode_akun' => '42', 'tipe_akun' => 'Pendapatan', 'posisi' => 'Kredit', 'saldo_awal' => 0, 'kategori_akun' => 'Pendapatan Usaha'],
            
            // BIAYA
            ['nama_akun' => 'BBB - Biaya Bahan Baku', 'kode_akun' => '51', 'tipe_akun' => 'Biaya', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Harga Pokok Penjualan'],
            ['nama_akun' => 'BBB - Jagung', 'kode_akun' => '510', 'tipe_akun' => 'Biaya', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Harga Pokok Penjualan'],
            ['nama_akun' => 'Beban Tunjangan', 'kode_akun' => '513', 'tipe_akun' => 'Equity', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Beban Usaha'],
            ['nama_akun' => 'Beban Asuransi', 'kode_akun' => '514', 'tipe_akun' => 'Equity', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Beban Usaha'],
            ['nama_akun' => 'Beban Bonus', 'kode_akun' => '515', 'tipe_akun' => 'Equity', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Beban Usaha'],
            ['nama_akun' => 'Potongan Gaji', 'kode_akun' => '516', 'tipe_akun' => 'Equity', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Beban Usaha'],
            ['nama_akun' => 'BTKL', 'kode_akun' => '52', 'tipe_akun' => 'Biaya', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Harga Pokok Penjualan'],
            ['nama_akun' => 'BTKL - Produksi Jasuke', 'kode_akun' => '520', 'tipe_akun' => 'Biaya', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Harga Pokok Penjualan'],
            ['nama_akun' => 'BOP', 'kode_akun' => '53', 'tipe_akun' => 'Biaya', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Beban Produksi'],
            ['nama_akun' => 'BOP - Susu', 'kode_akun' => '530', 'tipe_akun' => 'Biaya', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Beban Produksi'],
            ['nama_akun' => 'BOP - Keju', 'kode_akun' => '531', 'tipe_akun' => 'Biaya', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Beban Produksi'],
            ['nama_akun' => 'BOP - Kemasan', 'kode_akun' => '532', 'tipe_akun' => 'Biaya', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Beban Produksi'],
            ['nama_akun' => 'Beban Sewa', 'kode_akun' => '54', 'tipe_akun' => 'Expense', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Beban Usaha'],
            ['nama_akun' => 'BOP Lain', 'kode_akun' => '55', 'tipe_akun' => 'Biaya', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Beban Produksi'],
            ['nama_akun' => 'BOP - Listrik', 'kode_akun' => '550', 'tipe_akun' => 'Biaya', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Beban Produksi'],
            ['nama_akun' => 'BOP - Air', 'kode_akun' => '551', 'tipe_akun' => 'Biaya', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Beban Produksi'],
            ['nama_akun' => 'BOP - Gas', 'kode_akun' => '552', 'tipe_akun' => 'Biaya', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Beban Produksi'],
            ['nama_akun' => 'BOP - Penyusutan Peralatan', 'kode_akun' => '553', 'tipe_akun' => 'Biaya', 'posisi' => 'Debit', 'saldo_awal' => 0, 'kategori_akun' => 'Beban Produksi'],
        ];

        foreach ($companies as $company) {
            $this->command->info("Creating COA for company: {$company->nama} (ID: {$company->id})");
            
            foreach ($defaultCoaData as $coa) {
                // Check if COA already exists for this company
                $existingCoa = DB::table('coas')
                    ->where('user_id', $company->id)
                    ->where('kode_akun', $coa['kode_akun'])
                    ->first();
                
                if (!$existingCoa) {
                    DB::table('coas')->insert([
                        'kode_akun' => $coa['kode_akun'],
                        'nama_akun' => $coa['nama_akun'],
                        'tipe_akun' => $coa['tipe_akun'],
                        'kategori_akun' => $coa['kategori_akun'],
                        'saldo_awal' => $coa['saldo_awal'],
                        'user_id' => $company->id,
                        'company_id' => $company->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Default COA seeder completed successfully!');
    }
}
