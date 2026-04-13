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
            // ===================== ASET (11) =====================
            ['kode_akun' => '11',   'nama_akun' => 'Aset',                                    'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset',              'saldo_normal' => 'debit'],
            ['kode_akun' => '111',  'nama_akun' => 'Kas',                                     'tipe_akun' => 'Asset', 'kategori_akun' => 'Kas & Bank',        'saldo_normal' => 'debit'],
            ['kode_akun' => '112',  'nama_akun' => 'Bank',                                    'tipe_akun' => 'Asset', 'kategori_akun' => 'Kas & Bank',        'saldo_normal' => 'debit'],
            ['kode_akun' => '113',  'nama_akun' => 'Persediaan Bahan Baku',                   'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan',        'saldo_normal' => 'debit'],
            ['kode_akun' => '1131', 'nama_akun' => 'Persediaan Bahan Baku - Ayam Potong',     'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan',        'saldo_normal' => 'debit'],
            ['kode_akun' => '114',  'nama_akun' => 'Persediaan Bahan Penolong',               'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan',        'saldo_normal' => 'debit'],
            ['kode_akun' => '1141', 'nama_akun' => 'Persediaan Bahan Penolong - Garam',       'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan',        'saldo_normal' => 'debit'],
            ['kode_akun' => '1142', 'nama_akun' => 'Persediaan Bahan Penolong - Merica Bubuk','tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan',        'saldo_normal' => 'debit'],
            ['kode_akun' => '1143', 'nama_akun' => 'Persediaan Bahan Penolong - Tepung Basah','tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan',        'saldo_normal' => 'debit'],
            ['kode_akun' => '1144', 'nama_akun' => 'Persediaan Bahan Penolong - Tepung Kering','tipe_akun' => 'Asset','kategori_akun' => 'Persediaan',        'saldo_normal' => 'debit'],
            ['kode_akun' => '1145', 'nama_akun' => 'Persediaan Bahan Penolong - Minyak Goreng','tipe_akun' => 'Asset','kategori_akun' => 'Persediaan',        'saldo_normal' => 'debit'],
            ['kode_akun' => '115',  'nama_akun' => 'Persediaan Barang Dalam Proses',          'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan',        'saldo_normal' => 'debit'],
            ['kode_akun' => '116',  'nama_akun' => 'Persediaan Barang Jadi',                  'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan',        'saldo_normal' => 'debit'],
            ['kode_akun' => '1161', 'nama_akun' => 'Persediaan Barang Jadi - Ayam Crispy',    'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan',        'saldo_normal' => 'debit'],
            ['kode_akun' => '117',  'nama_akun' => 'Peralatan Produksi',                      'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset Tetap',        'saldo_normal' => 'debit'],
            ['kode_akun' => '118',  'nama_akun' => 'Akumulasi Penyusutan Peralatan',          'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset Tetap',        'saldo_normal' => 'kredit'],
            ['kode_akun' => '119',  'nama_akun' => 'Gedung',                                  'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset Tetap',        'saldo_normal' => 'debit'],
            ['kode_akun' => '120',  'nama_akun' => 'Akumulasi Penyusutan Gedung',             'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset Tetap',        'saldo_normal' => 'kredit'],
            ['kode_akun' => '121',  'nama_akun' => 'PPN Masukan',                             'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset Lancar',       'saldo_normal' => 'debit'],

            // ===================== KEWAJIBAN (21) =====================
            ['kode_akun' => '21',   'nama_akun' => 'Kewajiban',                               'tipe_akun' => 'Liability', 'kategori_akun' => 'Kewajiban',     'saldo_normal' => 'kredit'],
            ['kode_akun' => '211',  'nama_akun' => 'Utang Usaha',                             'tipe_akun' => 'Liability', 'kategori_akun' => 'Kewajiban',     'saldo_normal' => 'kredit'],
            ['kode_akun' => '212',  'nama_akun' => 'Utang Gaji',                              'tipe_akun' => 'Liability', 'kategori_akun' => 'Kewajiban',     'saldo_normal' => 'kredit'],

            // ===================== MODAL (31) =====================
            ['kode_akun' => '31',   'nama_akun' => 'Modal',                                   'tipe_akun' => 'Equity', 'kategori_akun' => 'Modal',            'saldo_normal' => 'kredit'],
            ['kode_akun' => '311',  'nama_akun' => 'Modal Pemilik',                           'tipe_akun' => 'Equity', 'kategori_akun' => 'Modal',            'saldo_normal' => 'kredit'],
            ['kode_akun' => '312',  'nama_akun' => 'Prive',                                   'tipe_akun' => 'Equity', 'kategori_akun' => 'Modal',            'saldo_normal' => 'debit'],

            // ===================== PENDAPATAN (41) =====================
            ['kode_akun' => '41',   'nama_akun' => 'Pendapatan',                              'tipe_akun' => 'Revenue', 'kategori_akun' => 'Pendapatan',      'saldo_normal' => 'kredit'],
            ['kode_akun' => '411',  'nama_akun' => 'Penjualan',                               'tipe_akun' => 'Revenue', 'kategori_akun' => 'Pendapatan',      'saldo_normal' => 'kredit'],
            ['kode_akun' => '4111', 'nama_akun' => 'Penjualan - Ayam Crispy',                 'tipe_akun' => 'Revenue', 'kategori_akun' => 'Pendapatan',      'saldo_normal' => 'kredit'],

            // ===================== BIAYA (51) =====================
            // BBB - Biaya Bahan Baku
            ['kode_akun' => '51',   'nama_akun' => 'Biaya Bahan Baku',                        'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Produksi',  'saldo_normal' => 'debit'],
            ['kode_akun' => '511',  'nama_akun' => 'BBB - Biaya Bahan Baku',                  'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Bahan Baku','saldo_normal' => 'debit'],
            ['kode_akun' => '5111', 'nama_akun' => 'BBB - Ayam Potong',                       'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Bahan Baku','saldo_normal' => 'debit'],

            // BTKL - Biaya Tenaga Kerja Langsung
            ['kode_akun' => '514',  'nama_akun' => 'BTKL - Biaya Tenaga Kerja Langsung',      'tipe_akun' => 'Expense', 'kategori_akun' => 'BTKL',           'saldo_normal' => 'debit'],
            ['kode_akun' => '5141', 'nama_akun' => 'BTKL - Perbumbuan',                       'tipe_akun' => 'Expense', 'kategori_akun' => 'BTKL',           'saldo_normal' => 'debit'],
            ['kode_akun' => '5142', 'nama_akun' => 'BTKL - Penggorengan',                     'tipe_akun' => 'Expense', 'kategori_akun' => 'BTKL',           'saldo_normal' => 'debit'],

            // BOP - Biaya Overhead Pabrik
            ['kode_akun' => '515',  'nama_akun' => 'BOP - Biaya Overhead Pabrik',             'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP',             'saldo_normal' => 'debit'],
            ['kode_akun' => '5151', 'nama_akun' => 'BOP - Garam',                             'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP',             'saldo_normal' => 'debit'],
            ['kode_akun' => '5152', 'nama_akun' => 'BOP - Merica Bubuk',                      'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP',             'saldo_normal' => 'debit'],
            ['kode_akun' => '5153', 'nama_akun' => 'BOP - Kaldu Bubuk',                       'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP',             'saldo_normal' => 'debit'],
            ['kode_akun' => '5154', 'nama_akun' => 'BOP - Ketumbar Bubuk',                    'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP',             'saldo_normal' => 'debit'],
            ['kode_akun' => '5155', 'nama_akun' => 'BOP - Tepung',                            'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP',             'saldo_normal' => 'debit'],
            ['kode_akun' => '5156', 'nama_akun' => 'BOP - Minyak Goreng',                     'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP',             'saldo_normal' => 'debit'],

            // BOP BTKTL - Biaya Tenaga Kerja Tidak Langsung
            ['kode_akun' => '516',  'nama_akun' => 'BOP BTKTL - Biaya Tenaga Kerja Tidak Langsung', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BTKTL',    'saldo_normal' => 'debit'],
            ['kode_akun' => '5161', 'nama_akun' => 'BOP BTKTL - Biaya Pegawai 1',             'tipe_akun' => 'Expense', 'kategori_akun' => 'BTKTL',           'saldo_normal' => 'debit'],

            // BOP TL - Biaya Tidak Langsung
            ['kode_akun' => '517',  'nama_akun' => 'BOP TL - Biaya Tidak Langsung',           'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '5171', 'nama_akun' => 'BOP TL - Biaya Gas',                      'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '5172', 'nama_akun' => 'BOP TL - Biaya Listrik',                  'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '5173', 'nama_akun' => 'BOP TL - Biaya Penyusutan Peralatan',     'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '5174', 'nama_akun' => 'BOP TL - Biaya Overhead Lain-lain',       'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '5175', 'nama_akun' => 'BOP TL - Biaya Transportasi',             'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '5176', 'nama_akun' => 'BOP TL - Biaya Servis',                   'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '5177', 'nama_akun' => 'BOP TL - Biaya Kebersihan',               'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung', 'saldo_normal' => 'debit'],
        ];

        foreach ($coaData as $coa) {
            Coa::withoutGlobalScopes()->updateOrCreate(
                ['kode_akun' => $coa['kode_akun']],
                [
                    'nama_akun' => $coa['nama_akun'],
                    'tipe_akun' => $coa['tipe_akun'],
                    'kategori_akun' => $coa['kategori_akun'],
                    'saldo_normal' => $coa['saldo_normal'],
                    'saldo_awal' => 0,
                ]
            );
        }

        $this->command->info('COA seeder completed! Total: ' . count($coaData) . ' akun.');
    }
}