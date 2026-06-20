<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultCoaSeeder extends Seeder
{
    /**
     * COA Default untuk User Baru
     * Dipanggil otomatis saat user registrasi via CreateDefaultUserData listener
     * Atau manual dengan: php artisan db:seed --class=DefaultCoaSeeder
     *
     * Mencakup produk: Ayam Crispy Macdi, Ayam Goreng Bundo, Jasuke, Pisang Tanduk
     *
     * Daftar COA sesuai dengan Chart of Accounts resmi perusahaan:
     * Total: 101 akun
     */
    public function run(?int $userId = null): void
    {
        // Jika userId tidak diberikan, gunakan user pertama dari database
        if ($userId === null) {
            $user = DB::table('users')->first();
            if (!$user) {
                if ($this->command) {
                    $this->command->error('No users found in database. Please create a user first.');
                }
                return;
            }
            $userId = $user->id;
        }

        // Jangan buat ulang jika sudah ada
        if (DB::table('coas')->where('user_id', $userId)->exists()) {
            if ($this->command) {
                $this->command->info("COA already exists for user ID: {$userId}");
            }
            return;
        }

        $now = now();

        $coas = [
            // =========================================================
            // ASET (11)
            // =========================================================
            ['kode_akun' => '11',    'nama_akun' => 'Aset',                                            'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // Kas Bank (111)
            ['kode_akun' => '111',   'nama_akun' => 'Kas Bank',                                        'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1111',  'nama_akun' => 'Bank BRI',                                        'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 100000000],
            ['kode_akun' => '1112',  'nama_akun' => 'Bank BCA',                                        'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 50000000],
            ['kode_akun' => '1113',  'nama_akun' => 'Bank Mandiri',                                    'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 50000000],
            ['kode_akun' => '1114',  'nama_akun' => 'Seabank',                                         'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // Kas & Kas Kecil
            ['kode_akun' => '112',   'nama_akun' => 'Kas',                                             'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 75000000],
            ['kode_akun' => '113',   'nama_akun' => 'Kas Kecil',                                       'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 1000000],

            // Persediaan Bahan Baku (114)
            ['kode_akun' => '114',   'nama_akun' => 'Pers. Bahan Baku',                                'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1141',  'nama_akun' => 'Pers. Bahan Baku ayam potong',                    'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1142',  'nama_akun' => 'Pers. Bahan Baku ayam kampung',                   'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1143',  'nama_akun' => 'Pers. Bahan Baku Jagung',                         'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // Persediaan Bahan Pendukung (115)
            ['kode_akun' => '115',   'nama_akun' => 'Pers. Bahan Pendukung',                           'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1151',  'nama_akun' => 'Pers. Bahan Pendukung Air',                       'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1152',  'nama_akun' => 'Pers. Bahan Pendukung Minyak Goreng',             'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1153',  'nama_akun' => 'Pers. Bahan Pendukung Tepung Terigu',             'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1154',  'nama_akun' => 'Pers. Bahan Pendukung Tepung Maizena',            'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1155',  'nama_akun' => 'Pers. Bahan Pendukung Lada',                      'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1156',  'nama_akun' => 'Pers. Bahan Pendukung Bubuk Kaldu',               'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1157',  'nama_akun' => 'Pers. Bahan Pendukung Bubuk Bawang Putih',        'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1158',  'nama_akun' => 'Pers. Bahan Pendukung Susu',                      'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1159',  'nama_akun' => 'Pers. Bahan Pendukung Keju',                      'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '11511', 'nama_akun' => 'Pers. Bahan Pendukung Coklat',                    'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '11512', 'nama_akun' => 'Pers. Bahan Pendukung Kemasan',                   'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // Persediaan Barang Jadi (116)
            ['kode_akun' => '116',   'nama_akun' => 'Pers. Barang Jadi',                               'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1161',  'nama_akun' => 'Pers. Barang Jadi Ayam Crispy Macdi',             'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1162',  'nama_akun' => 'Pers. Barang Jadi Ayam Goreng Bundo',             'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1163',  'nama_akun' => 'Pers. Barang Jadi Jasuke',                        'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1164',  'nama_akun' => 'Pers. Barang Jadi Pisang Tanduk',                 'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // Persediaan Barang Dalam Proses (117)
            ['kode_akun' => '117',   'nama_akun' => 'Pers. Barang dalam Proses',                       'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1171',  'nama_akun' => 'Pers. Barang Dalam Proses - BBB (WIP BBB)',       'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1172',  'nama_akun' => 'Pers. Barang Dalam Proses - BTKL (WIP BTKL)',     'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1173',  'nama_akun' => 'Pers. Barang Dalam Proses - BOP (WIP BOP)',       'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // Aset Lainnya
            ['kode_akun' => '118',   'nama_akun' => 'Piutang',                                         'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 11000000],
            ['kode_akun' => '119',   'nama_akun' => 'Peralatan',                                       'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '120',   'nama_akun' => 'Akumulasi Penyusutan Peralatan',                  'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '121',   'nama_akun' => 'Gedung',                                          'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '122',   'nama_akun' => 'Akumulasi Penyusutan Gedung',                     'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '123',   'nama_akun' => 'Kendaraan',                                       'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '124',   'nama_akun' => 'Akumulasi Penyusutan Kendaraan',                  'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '125',   'nama_akun' => 'Mesin',                                           'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '126',   'nama_akun' => 'Akumulasi Penyusutan Mesin',                      'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '127',   'nama_akun' => 'PPN Masukkan',                                    'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // =========================================================
            // KEWAJIBAN (21)
            // =========================================================
            ['kode_akun' => '21',    'nama_akun' => 'Hutang',                                          'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '211',   'nama_akun' => 'Hutang Usaha',                                    'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'saldo_awal' => 12000000],
            ['kode_akun' => '212',   'nama_akun' => 'Hutang Gaji',                                     'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '213',   'nama_akun' => 'Hutang Asuransi',                                 'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '214',   'nama_akun' => 'PPN Keluaran',                                    'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],

            // =========================================================
            // MODAL (31)
            // =========================================================
            ['kode_akun' => '31',    'nama_akun' => 'Modal',                                           'tipe_akun' => 'Modal',     'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '311',   'nama_akun' => 'Modal Usaha',                                     'tipe_akun' => 'Modal',     'saldo_normal' => 'kredit', 'saldo_awal' => 275000000],
            ['kode_akun' => '312',   'nama_akun' => 'Prive',                                           'tipe_akun' => 'Modal',     'saldo_normal' => 'kredit', 'saldo_awal' => 0],

            // =========================================================
            // PENDAPATAN (41)
            // =========================================================
            ['kode_akun' => '41',    'nama_akun' => 'Penjualan',                                       'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '411',   'nama_akun' => 'Penjualan - Produk Ayam Crispy Macdi',            'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '412',   'nama_akun' => 'Penjualan - Produk Ayam Goreng Bundo',            'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '413',   'nama_akun' => 'Penjualan - Jasuke',                              'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '414',   'nama_akun' => 'Penjualan - Pisang Tanduk',                       'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '42',    'nama_akun' => 'Retur Penjualan',                                 'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '43',    'nama_akun' => 'Pendapatan Lain-Lain',                            'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],

            // =========================================================
            // BEBAN - BBB (Biaya Bahan Baku) (51)
            // =========================================================
            ['kode_akun' => '51',    'nama_akun' => 'BBB - Biaya Bahan Baku',                          'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '511',   'nama_akun' => 'BBB - ayam potong',                               'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '512',   'nama_akun' => 'BBB - ayam kampung',                              'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '513',   'nama_akun' => 'BBB - Jagung',                                    'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '514',   'nama_akun' => 'BBB - Pisang',                                    'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '515',   'nama_akun' => 'Beban Tunjangan',                                 'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '516',   'nama_akun' => 'Beban Asuransi',                                  'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '517',   'nama_akun' => 'Beban Bonus',                                     'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '518',   'nama_akun' => 'Potongan Gaji',                                   'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // =========================================================
            // BEBAN - BTKL (Biaya Tenaga Kerja Langsung) (52)
            // =========================================================
            ['kode_akun' => '52',    'nama_akun' => 'BTKL-Biaya Tenaga Kerja Langsung',                'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '521',   'nama_akun' => 'Beban Gaji dan upah (BTKL)  - Penggorengan',      'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '522',   'nama_akun' => 'Beban Gaji dan upah (BTKL)  - Perbumbuan',        'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '523',   'nama_akun' => 'Beban Gaji dan upah (BTKL)  - Pengemasan',        'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '524',   'nama_akun' => 'Beban Gaji dan upah (BTKL)  - Pengukusan',        'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // =========================================================
            // BEBAN - BOP (Biaya Overhead Pabrik) (53)
            // =========================================================
            ['kode_akun' => '53',    'nama_akun' => 'BOP',                                             'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '531',   'nama_akun' => 'BOP-Air',                                         'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '532',   'nama_akun' => 'BOP-Minyak Goreng',                               'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '533',   'nama_akun' => 'BOP-Tepung Terigu',                               'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '534',   'nama_akun' => 'BOP-Tepung Maizena',                              'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '535',   'nama_akun' => 'BOP- Lada',                                       'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '536',   'nama_akun' => 'BOP- Bubuk Kaldu Ayam',                           'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '537',   'nama_akun' => 'BOP - Bubuk Bawang Putih',                        'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '538',   'nama_akun' => 'BOP - Susu',                                      'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '539',   'nama_akun' => 'BOP - Keju',                                      'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '5.311', 'nama_akun' => 'BOP - Coklat',                                    'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '5.322', 'nama_akun' => 'BOP-Kemasan',                                     'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // =========================================================
            // BEBAN - BOP BTKTL (Biaya Tenaga Kerja Tidak Langsung) (54)
            // =========================================================
            ['kode_akun' => '54',    'nama_akun' => 'BOP BTKTL-Biaya Tenaga Kerja Tidak Langsung',     'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '541',   'nama_akun' => 'BOP BTKTL - Admin Produksi',                      'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '542',   'nama_akun' => 'BOP BTKTL - Manager Produksi',                    'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '543',   'nama_akun' => 'BOP BTKTL - Mandor',                              'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // =========================================================
            // BEBAN - BOP Lainnya (55)
            // =========================================================
            ['kode_akun' => '55',    'nama_akun' => 'BOP Lainnya',                                     'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '551',   'nama_akun' => 'BOP - Listrik',                                   'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '552',   'nama_akun' => 'BOP - Air',                                       'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '553',   'nama_akun' => 'BOP - Gas',                                       'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '554',   'nama_akun' => 'BOP - Penyusutan Gedung',                         'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '555',   'nama_akun' => 'BOP - Penyusutan Peralatan',                      'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '556',   'nama_akun' => 'BOP - Penyusutan Kendaraan',                      'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '557',   'nama_akun' => 'BOP - Biaya Penyusutan Mesin',                    'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '558',   'nama_akun' => 'Beban Transport Pembelian',                       'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '559',   'nama_akun' => 'Diskon Pembelian',                                'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // =========================================================
            // BEBAN - Harga Pokok Penjualan (56)
            // =========================================================
            ['kode_akun' => '56',    'nama_akun' => 'Harga Pokok Penjualan',                           'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '561',   'nama_akun' => 'Harga Pokok Penjualan - Produk Ayam Crispy Macdi','tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '562',   'nama_akun' => 'Harga Pokok Penjualan - Produk Ayam Goreng Bundo','tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '563',   'nama_akun' => 'Harga Pokok Penjualan - Jasuke',                  'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '564',   'nama_akun' => 'Harga Pokok Penjualan - Pisang Tanduk',           'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
        ];

        $rows = [];
        foreach ($coas as $coa) {
            $rows[] = [
                'user_id'            => $userId,
                'kode_akun'          => $coa['kode_akun'],
                'nama_akun'          => $coa['nama_akun'],
                'tipe_akun'          => $coa['tipe_akun'],
                'kategori_akun'      => $coa['tipe_akun'],
                'saldo_normal'       => $coa['saldo_normal'],
                'saldo_awal'         => $coa['saldo_awal'],
                'tanggal_saldo_awal' => $now,
                'posted_saldo_awal'  => 0,
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
        }

        DB::table('coas')->insert($rows);

        if ($this->command) {
            $this->command->info("COA berhasil di-seed untuk user ID: {$userId} (" . count($rows) . " akun)");
        }
    }
}
