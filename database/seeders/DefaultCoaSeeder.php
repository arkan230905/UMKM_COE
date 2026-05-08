<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultCoaSeeder extends Seeder
{
    /**
     * Buat 51 COA default untuk user baru yang register.
     * Dipanggil dari CreateDefaultUserData listener.
     */
    public function run(int $userId): void
    {
        // Jangan buat ulang jika sudah ada
        if (DB::table('coas')->where('user_id', $userId)->exists()) {
            return;
        }

        $now = now();

        $coas = [
            // NO | NAMA AKUN                              | KODE  | TIPE        | POSISI
            ['kode_akun' => '11',   'nama_akun' => 'Aset',                                    'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '111',  'nama_akun' => 'Kas Bank',                                'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '112',  'nama_akun' => 'Kas',                                     'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '113',  'nama_akun' => 'Kas Kecil',                               'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '114',  'nama_akun' => 'Pers. Bahan Baku',                        'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1141', 'nama_akun' => 'Pers. Bahan Baku Jagung',                 'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '115',  'nama_akun' => 'Pers. Bahan Pendukung',                   'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1151', 'nama_akun' => 'Pers. Bahan Pendukung Susu',              'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1152', 'nama_akun' => 'Pers. Bahan Pendukung Keju',              'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1153', 'nama_akun' => 'Pers. Bahan Pendukung Kemasan (Cup)',     'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '116',  'nama_akun' => 'Pers. Barang Jadi',                       'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1161', 'nama_akun' => 'Pers. Barang Jadi Jasuke',                'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '117',  'nama_akun' => 'Pers. Barang dalam Proses',               'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1171', 'nama_akun' => 'Pers. Barang Dalam Proses - BBB',         'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1172', 'nama_akun' => 'Pers. Barang Dalam Proses - BTKL',       'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1173', 'nama_akun' => 'Pers. Barang Dalam Proses - BOP',        'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '118',  'nama_akun' => 'Piutang',                                 'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '119',  'nama_akun' => 'Peralatan',                               'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '120',  'nama_akun' => 'Akumulasi Penyusutan Peralatan',          'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
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
            ['kode_akun' => '410',  'nama_akun' => 'Penjualan - Jasuke',                      'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '42',   'nama_akun' => 'Retur Penjualan',                         'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],

            // BIAYA
            ['kode_akun' => '51',   'nama_akun' => 'BBB - Biaya Bahan Baku',                  'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '510',  'nama_akun' => 'BBB - Jagung',                            'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '513',  'nama_akun' => 'Beban Tunjangan',                         'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '514',  'nama_akun' => 'Beban Asuransi',                          'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '515',  'nama_akun' => 'Beban Bonus',                             'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '516',  'nama_akun' => 'Potongan Gaji',                           'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '52',   'nama_akun' => 'BTKL',                                    'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '520',  'nama_akun' => 'BTKL - Produksi Jasuke',                  'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '53',   'nama_akun' => 'BOP',                                     'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '530',  'nama_akun' => 'BOP - Susu',                              'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '531',  'nama_akun' => 'BOP - Keju',                              'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '532',  'nama_akun' => 'BOP - Kemasan',                           'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '54',   'nama_akun' => 'Beban Sewa',                              'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '55',   'nama_akun' => 'BOP Lain',                                'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '550',  'nama_akun' => 'BOP - Listrik',                           'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '551',  'nama_akun' => 'BOP - Air',                               'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '552',  'nama_akun' => 'BOP - Gas',                               'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '553',  'nama_akun' => 'BOP - Penyusutan Peralatan',              'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '56',   'nama_akun' => 'Harga Pokok Penjualan',                   'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '590',  'nama_akun' => 'Beban Administrasi Bank',                'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '591',  'nama_akun' => 'Beban Pajak',                              'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '592',  'nama_akun' => 'Beban Denda',                              'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '593',  'nama_akun' => 'Beban Kerugian',                           'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '594',  'nama_akun' => 'Beban Lain-lain',                         'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '536',  'nama_akun' => 'Biaya Air & Kebersihan',                  'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
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
