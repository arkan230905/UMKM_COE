<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultCoaSeeder extends Seeder
{
    /**
     * COA Default untuk User Baru - Ayam Ketumbar
     * Dipanggil otomatis saat user registrasi via CreateDefaultUserData listener
     * Atau manual dengan: php artisan db:seed --class=DefaultCoaSeeder
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
            // ASSET
            ['kode_akun' => '11',   'nama_akun' => 'Aset',                                    'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '111',  'nama_akun' => 'Kas Bank',                                'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '112',  'nama_akun' => 'Kas',                                     'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '113',  'nama_akun' => 'Kas Kecil',                               'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '114',  'nama_akun' => 'Pers. Bahan Baku',                        'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1141', 'nama_akun' => 'Pers. Bahan Baku ayam potong',            'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1142', 'nama_akun' => 'Pers. Bahan Baku ayam kampung',           'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1143', 'nama_akun' => 'Pers. Bahan Baku bebek',                  'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1144', 'nama_akun' => 'Pers. Bahan Baku ayam lainnya',           'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '115',  'nama_akun' => 'Pers. Bahan Pendukung',                   'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1150', 'nama_akun' => 'Pers. Bahan Pendukung Air',               'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1151', 'nama_akun' => 'Pers. Bahan Pendukung Minyak Goreng',      'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1152', 'nama_akun' => 'Pers. Bahan Pendukung Tepung Terigu',     'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1153', 'nama_akun' => 'Pers. Bahan Pendukung Tepung Maizena',    'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1154', 'nama_akun' => 'Pers. Bahan Pendukung Lada',               'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1155', 'nama_akun' => 'Pers. Bahan Pendukung Bubuk Kaldu',       'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1156', 'nama_akun' => 'Pers. Bahan Pendukung Bubuk Bawang Putih','tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1157', 'nama_akun' => 'Pers. Bahan Pendukung Kemasan',           'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '116',  'nama_akun' => 'Pers. Barang Jadi',                       'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1161', 'nama_akun' => 'Pers. Barang Jadi Ayam Crispy Macdi',      'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1162', 'nama_akun' => 'Pers. Barang Jadi Ayam Goreng Bundo',      'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '117',  'nama_akun' => 'Pers. Barang dalam Proses',               'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '118',  'nama_akun' => 'Piutang',                                 'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '119',  'nama_akun' => 'Peralatan',                               'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '120',  'nama_akun' => 'Akumulasi Penyusutan Peralatan',          'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '121',  'nama_akun' => 'Gedung',                                  'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '122',  'nama_akun' => 'Akumulasi Penyusutan Gedung',             'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '123',  'nama_akun' => 'Kendaraan',                               'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '124',  'nama_akun' => 'Akumulasi Penyusutan Kendaraan',          'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '125',  'nama_akun' => 'Mesin',                                   'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '126',  'nama_akun' => 'Akumulasi Penyusutan Mesin',              'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '127',  'nama_akun' => 'PPN Masukkan',                            'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],

            // KEWAJIBAN
            ['kode_akun' => '21',   'nama_akun' => 'Hutang',                                  'tipe_akun' => 'Kewajiban',  'saldo_normal' => 'kredit'],
            ['kode_akun' => '210',  'nama_akun' => 'Hutang Usaha',                            'tipe_akun' => 'Kewajiban',  'saldo_normal' => 'kredit'],
            ['kode_akun' => '211',  'nama_akun' => 'Hutang Gaji',                             'tipe_akun' => 'Kewajiban',  'saldo_normal' => 'kredit'],
            ['kode_akun' => '212',  'nama_akun' => 'PPN Keluaran',                            'tipe_akun' => 'Kewajiban',  'saldo_normal' => 'kredit'],

            // MODAL
            ['kode_akun' => '31',   'nama_akun' => 'Modal',                                   'tipe_akun' => 'Modal',      'saldo_normal' => 'kredit'],
            ['kode_akun' => '310',  'nama_akun' => 'Modal Usaha',                             'tipe_akun' => 'Modal',      'saldo_normal' => 'kredit'],
            ['kode_akun' => '311',  'nama_akun' => 'Prive',                                   'tipe_akun' => 'Modal',      'saldo_normal' => 'kredit'],

            // PENDAPATAN
            ['kode_akun' => '41',   'nama_akun' => 'Penjualan',                               'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '410',  'nama_akun' => 'Penjualan - Produk Ayam Crispy Macdi',    'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '411',  'nama_akun' => 'Penjualan - Produk Ayam Goreng Bundo',     'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '42',   'nama_akun' => 'Retur Penjualan',                         'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '43',   'nama_akun' => 'Pendapatan Lain-Lain',                    'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],

            // BIAYA BAHAN BAKU
            ['kode_akun' => '51',   'nama_akun' => 'BBB-Biaya Bahan Baku',                    'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '510',  'nama_akun' => 'BBB-ayam potong',                         'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '511',  'nama_akun' => 'BBB-ayam kampung',                        'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '512',  'nama_akun' => 'BBB-bebek',                               'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],

            // BIAYA TENAGA KERJA LANGSUNG
            ['kode_akun' => '52',   'nama_akun' => 'BTKL-Biaya Tenaga Kerja Langsung',         'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '520',  'nama_akun' => 'BTKL-Perbumbuan',                         'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '521',  'nama_akun' => 'BTKL-Penggorengan',                       'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '522',  'nama_akun' => 'BTKL-Pengemasan',                         'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],

            // BIAYA OVERHEAD PABRIK
            ['kode_akun' => '53',   'nama_akun' => 'BOP-Biaya Overhead Pabrik',                'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '530',  'nama_akun' => 'BOP-Biaya Bahan Baku Tidak Langsung',     'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '531',  'nama_akun' => 'BOP-Air',                                 'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '532',  'nama_akun' => 'BOP-Minyak Goreng',                       'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '533',  'nama_akun' => 'BOP-Tepung Terigu',                      'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '534',  'nama_akun' => 'BOP-Tepung Maizena',                      'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '535',  'nama_akun' => 'BOP- Lada',                               'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '536',  'nama_akun' => 'BOP- Bubuk Kaldu',                       'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '537',  'nama_akun' => 'BOP- Bubuk Bawang Putih',                'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '538',  'nama_akun' => 'BOP-Kemasan',                             'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],

            // BIAYA TENAGA KERJA TIDAK LANGSUNG
            ['kode_akun' => '54',   'nama_akun' => 'BOP BTKTL-Biaya Tenaga Kerja Tidak Langsung', 'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],

            // BIAYA OVERHEAD PABRIK LAINNYA
            ['kode_akun' => '55',   'nama_akun' => 'BOP - Lainnya',                           'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '550',  'nama_akun' => 'BOP - Beban Listrik',                     'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '551',  'nama_akun' => 'BOP - Beban Sewa Tempat',                 'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '552',  'nama_akun' => 'BOP - Beban Penyusutan Gedung',           'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '553',  'nama_akun' => 'BOP - Beban Penyusutan Peralatan',        'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '554',  'nama_akun' => 'BOP - Beban Penyusutan Kendaraan',        'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '555',  'nama_akun' => 'BOP - Beban Penyusutan Mesin',            'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '556',  'nama_akun' => 'BOP - Beban Air',                         'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '557',  'nama_akun' => 'BOP - Lainnya',                           'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '558',  'nama_akun' => 'Beban Transport Pembelian',              'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '559',  'nama_akun' => 'Diskon Pembelian',                       'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
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
                'saldo_awal'         => 0,
                'tanggal_saldo_awal' => $now,
                'posted_saldo_awal'  => 0,
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
        }

        DB::table('coas')->insert($rows);
    }
}
