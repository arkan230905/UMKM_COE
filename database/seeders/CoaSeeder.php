<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Coa;

class CoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Hapus semua data COA yang ada
        Coa::truncate();
        
        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $coaData = [
            // ===================== ASSET (11) =====================
            ['kode_akun' => '11',   'nama_akun' => 'Aset', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '111',  'nama_akun' => 'Kas Bank', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '112',  'nama_akun' => 'Kas', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '113',  'nama_akun' => 'Kas Kecil', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '114',  'nama_akun' => 'Pers. Bahan Baku', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '1141', 'nama_akun' => 'Pers. Bahan Baku Jagung', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '115',  'nama_akun' => 'Pers. Bahan Pendukung', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '1151', 'nama_akun' => 'Pers. Bahan Pendukung Susu', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '1152', 'nama_akun' => 'Pers. Bahan Pendukung Keju', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '1153', 'nama_akun' => 'Pers. Bahan Pendukung Kemasan (Cup)', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '116',  'nama_akun' => 'Pers. Barang Jadi', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '1161', 'nama_akun' => 'Pers. Barang Jadi Jasuke', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '117',  'nama_akun' => 'Pers. Barang dalam Proses', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '118',  'nama_akun' => 'Piutang', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '119',  'nama_akun' => 'Peralatan', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '120',  'nama_akun' => 'Akumulasi Penyusutan Peralatan', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '125',  'nama_akun' => 'Mesin', 'tipe_akun' => 'Aset', 'saldo_awal' => null],
            ['kode_akun' => '126',  'nama_akun' => 'Akumulasi Penyusutan Mesin', 'tipe_akun' => 'Aset', 'saldo_awal' => null],

            // ===================== KEWAJIBAN (21) =====================
            ['kode_akun' => '21',   'nama_akun' => 'Hutang', 'tipe_akun' => 'Kewajiban', 'saldo_awal' => null],
            ['kode_akun' => '210',  'nama_akun' => 'Hutang Usaha', 'tipe_akun' => 'Kewajiban', 'saldo_awal' => null],
            ['kode_akun' => '211',  'nama_akun' => 'Hutang Gaji', 'tipe_akun' => 'Kewajiban', 'saldo_awal' => null],

            // ===================== MODAL (31) =====================
            ['kode_akun' => '31',   'nama_akun' => 'Modal', 'tipe_akun' => 'Modal', 'saldo_awal' => null],
            ['kode_akun' => '310',  'nama_akun' => 'Modal Usaha', 'tipe_akun' => 'Modal', 'saldo_awal' => null],
            ['kode_akun' => '311',  'nama_akun' => 'Prive', 'tipe_akun' => 'Modal', 'saldo_awal' => null],

            // ===================== PENDAPATAN (41) =====================
            ['kode_akun' => '41',   'nama_akun' => 'Penjualan', 'tipe_akun' => 'Pendapatan', 'saldo_awal' => null],
            ['kode_akun' => '410',  'nama_akun' => 'Penjualan - Jasuke', 'tipe_akun' => 'Pendapatan', 'saldo_awal' => null],
            ['kode_akun' => '42',   'nama_akun' => 'Retur Penjualan', 'tipe_akun' => 'Pendapatan', 'saldo_awal' => null],

            // ===================== BIAYA (51) =====================
            // BBB - Biaya Bahan Baku
            ['kode_akun' => '51',   'nama_akun' => 'BBB - Biaya Bahan Baku', 'tipe_akun' => 'Biaya Bahan Baku', 'saldo_awal' => null],
            ['kode_akun' => '510',  'nama_akun' => 'BBB - Jagung', 'tipe_akun' => 'Biaya Bahan Baku', 'saldo_awal' => null],

            // BTKL - Biaya Tenaga Kerja Langsung
            ['kode_akun' => '52',   'nama_akun' => 'BTKL', 'tipe_akun' => 'Biaya Tenaga Kerja Langsung', 'saldo_awal' => null],
            ['kode_akun' => '520',  'nama_akun' => 'BTKL - Produksi Jasuke', 'tipe_akun' => 'Biaya Tenaga Kerja Langsung', 'saldo_awal' => null],

            // BOP - Biaya Overhead Pabrik
            ['kode_akun' => '53',   'nama_akun' => 'BOP', 'tipe_akun' => 'Biaya Overhead Pabrik', 'saldo_awal' => null],
            ['kode_akun' => '530',  'nama_akun' => 'BOP - Susu', 'tipe_akun' => 'Biaya Overhead Pabrik', 'saldo_awal' => null],
            ['kode_akun' => '531',  'nama_akun' => 'BOP - Keju', 'tipe_akun' => 'Biaya Overhead Pabrik', 'saldo_awal' => null],
            ['kode_akun' => '532',  'nama_akun' => 'BOP - Kemasan', 'tipe_akun' => 'Biaya Overhead Pabrik', 'saldo_awal' => null],

            // BOP Lain - Biaya Overhead Pabrik Lainnya
            ['kode_akun' => '55',   'nama_akun' => 'BOP Lain', 'tipe_akun' => 'Biaya Overhead Pabrik', 'saldo_awal' => null],
            ['kode_akun' => '550',  'nama_akun' => 'BOP - Listrik', 'tipe_akun' => 'Biaya Overhead Pabrik', 'saldo_awal' => null],
            ['kode_akun' => '551',  'nama_akun' => 'BOP - Air', 'tipe_akun' => 'Biaya Overhead Pabrik', 'saldo_awal' => null],
            ['kode_akun' => '552',  'nama_akun' => 'BOP - Gas', 'tipe_akun' => 'Biaya Overhead Pabrik', 'saldo_awal' => null],
            ['kode_akun' => '553',  'nama_akun' => 'BOP - Penyusutan Peralatan', 'tipe_akun' => 'Biaya Overhead Pabrik', 'saldo_awal' => null],
        ];

        foreach ($coaData as $coa) {
            Coa::withoutGlobalScopes()->updateOrCreate(
                ['kode_akun' => $coa['kode_akun'], 'company_id' => null],
                [
                    'nama_akun' => $coa['nama_akun'],
                    'tipe_akun' => $coa['tipe_akun'],
                    'kategori_akun' => $coa['tipe_akun'], // Use tipe_akun as kategori_akun
                    'saldo_awal' => $coa['saldo_awal'] ?? 0,
                    'tanggal_saldo_awal' => now(),
                    'posted_saldo_awal' => false,
                    'company_id' => null,
                ]
            );
        }

        $this->command->info('COA seeder completed! Total: ' . count($coaData) . ' akun.');
    }
}
