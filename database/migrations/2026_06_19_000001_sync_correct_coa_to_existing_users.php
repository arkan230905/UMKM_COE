<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Sync COA yang sudah diperbaiki ke semua user yang sudah ada.
 *
 * Perubahan dari COA lama ke COA baru:
 * - Tambah: 11511 (Pers. Bahan Pendukung Coklat), 11512 (Pers. Bahan Pendukung Kemasan)
 * - Hapus: 1160 (ganti ke 11512)
 * - Tambah: 1164 (Pers. Barang Jadi Pisang Tanduk)
 * - Koreksi Kewajiban: 211=Hutang Usaha, 212=Hutang Gaji, 213=Hutang Asuransi, 214=PPN Keluaran
 *   (sebelumnya: 210=Hutang Usaha, 211=Hutang Gaji, 212=PPN Keluaran)
 * - Tambah: 414 (Penjualan - Pisang Tanduk)
 * - Tambah: 514 (BBB - Pisang), 515 (Beban Tunjangan), 516 (Beban Asuransi),
 *            517 (Beban Bonus), 518 (Potongan Gaji)
 * - Koreksi nama BOP: 537=BOP-Bubuk Bawang Putih, 538=BOP-Susu, 539=BOP-Keju
 *   (sebelumnya: 537=BOP-Susu, 538=BOP-Keju, 539=BOP-Kemasan)
 * - Tambah: 5311 (BOP-Coklat), 5322 (BOP-Kemasan)
 * - Koreksi nama BOP BTKTL: 541=Admin Produksi, 542=Manager Produksi, 543=Mandor
 *   (sebelumnya: 541=Admin, 542=Kasir, 543=Manager)
 * - Tambah: 564 (Harga Pokok Penjualan - Pisang Tanduk)
 * - Set saldo awal sesuai data perusahaan
 */
return new class extends Migration
{
    /**
     * Daftar lengkap COA yang benar sesuai Chart of Accounts resmi.
     * Total: 101 akun
     */
    private function getCorrectCoas(): array
    {
        return [
            // ASET (11)
            ['kode_akun' => '11',    'nama_akun' => 'Aset',                                            'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '111',   'nama_akun' => 'Kas Bank',                                        'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1111',  'nama_akun' => 'Bank BRI',                                        'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 100000000],
            ['kode_akun' => '1112',  'nama_akun' => 'Bank BCA',                                        'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 50000000],
            ['kode_akun' => '1123',  'nama_akun' => 'Bank Mandiri',                                    'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 50000000],
            ['kode_akun' => '1124',  'nama_akun' => 'Seabank',                                         'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '112',   'nama_akun' => 'Kas',                                             'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 75000000],
            ['kode_akun' => '113',   'nama_akun' => 'Kas Kecil',                                       'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 1000000],
            ['kode_akun' => '114',   'nama_akun' => 'Pers. Bahan Baku',                                'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1141',  'nama_akun' => 'Pers. Bahan Baku ayam potong',                    'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1142',  'nama_akun' => 'Pers. Bahan Baku ayam kampung',                   'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1143',  'nama_akun' => 'Pers. Bahan Baku Jagung',                         'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
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
            ['kode_akun' => '116',   'nama_akun' => 'Pers. Barang Jadi',                               'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1161',  'nama_akun' => 'Pers. Barang Jadi Ayam Crispy Macdi',             'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1162',  'nama_akun' => 'Pers. Barang Jadi Ayam Goreng Bundo',             'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1163',  'nama_akun' => 'Pers. Barang Jadi Jasuke',                        'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1164',  'nama_akun' => 'Pers. Barang Jadi Pisang Tanduk',                 'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '117',   'nama_akun' => 'Pers. Barang dalam Proses',                       'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1171',  'nama_akun' => 'Pers. Barang Dalam Proses - BBB (WIP BBB)',       'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1172',  'nama_akun' => 'Pers. Barang Dalam Proses - BTKL (WIP BTKL)',    'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '1173',  'nama_akun' => 'Pers. Barang Dalam Proses - BOP (WIP BOP)',      'tipe_akun' => 'Aset',      'saldo_normal' => 'debit', 'saldo_awal' => 0],
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

            // KEWAJIBAN (21)
            ['kode_akun' => '21',    'nama_akun' => 'Hutang',                                          'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '211',   'nama_akun' => 'Hutang Usaha',                                    'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'saldo_awal' => 12000000],
            ['kode_akun' => '212',   'nama_akun' => 'Hutang Gaji',                                     'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '213',   'nama_akun' => 'Hutang Asuransi',                                 'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '214',   'nama_akun' => 'PPN Keluaran',                                    'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],

            // MODAL (31)
            ['kode_akun' => '31',    'nama_akun' => 'Modal',                                           'tipe_akun' => 'Modal',     'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '311',   'nama_akun' => 'Modal Usaha',                                     'tipe_akun' => 'Modal',     'saldo_normal' => 'kredit', 'saldo_awal' => 275000000],
            ['kode_akun' => '312',   'nama_akun' => 'Prive',                                           'tipe_akun' => 'Modal',     'saldo_normal' => 'kredit', 'saldo_awal' => 0],

            // PENDAPATAN (41)
            ['kode_akun' => '41',    'nama_akun' => 'Penjualan',                                       'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '411',   'nama_akun' => 'Penjualan - Produk Ayam Crispy Macdi',            'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '412',   'nama_akun' => 'Penjualan - Produk Ayam Goreng Bundo',            'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '413',   'nama_akun' => 'Penjualan - Jasuke',                              'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '414',   'nama_akun' => 'Penjualan - Pisang Tanduk',                       'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '42',    'nama_akun' => 'Retur Penjualan',                                 'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],
            ['kode_akun' => '43',    'nama_akun' => 'Pendapatan Lain-Lain',                            'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit', 'saldo_awal' => 0],

            // BEBAN BBB (51)
            ['kode_akun' => '51',    'nama_akun' => 'BBB - Biaya Bahan Baku',                          'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '511',   'nama_akun' => 'BBB - ayam potong',                               'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '512',   'nama_akun' => 'BBB - ayam kampung',                              'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '513',   'nama_akun' => 'BBB - Jagung',                                    'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '514',   'nama_akun' => 'BBB - Pisang',                                    'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '515',   'nama_akun' => 'Beban Tunjangan',                                 'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '516',   'nama_akun' => 'Beban Asuransi',                                  'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '517',   'nama_akun' => 'Beban Bonus',                                     'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '518',   'nama_akun' => 'Potongan Gaji',                                   'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // BEBAN BTKL (52)
            ['kode_akun' => '52',    'nama_akun' => 'BTKL-Biaya Tenaga Kerja Langsung',                'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '521',   'nama_akun' => 'Beban Gaji dan upah (BTKL)  - Penggorengan',      'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '522',   'nama_akun' => 'Beban Gaji dan upah (BTKL)  - Perbumbuan',        'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '523',   'nama_akun' => 'Beban Gaji dan upah (BTKL)  - Pengemasan',        'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '524',   'nama_akun' => 'Beban Gaji dan upah (BTKL)  - Pengukusan',        'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // BEBAN BOP (53)
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
            ['kode_akun' => '5311',  'nama_akun' => 'BOP - Coklat',                                    'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '5322',  'nama_akun' => 'BOP-Kemasan',                                     'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // BEBAN BOP BTKTL (54)
            ['kode_akun' => '54',    'nama_akun' => 'BOP BTKTL-Biaya Tenaga Kerja Tidak Langsung',     'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '541',   'nama_akun' => 'BOP BTKTL - Admin Produksi',                      'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '542',   'nama_akun' => 'BOP BTKTL - Manager Produksi',                    'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '543',   'nama_akun' => 'BOP BTKTL - Mandor',                              'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],

            // BEBAN BOP Lainnya (55)
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

            // BEBAN HPP (56)
            ['kode_akun' => '56',    'nama_akun' => 'Harga Pokok Penjualan',                           'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '561',   'nama_akun' => 'Harga Pokok Penjualan - Produk Ayam Crispy Macdi','tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '562',   'nama_akun' => 'Harga Pokok Penjualan - Produk Ayam Goreng Bundo','tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '563',   'nama_akun' => 'Harga Pokok Penjualan - Jasuke',                  'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
            ['kode_akun' => '564',   'nama_akun' => 'Harga Pokok Penjualan - Pisang Tanduk',           'tipe_akun' => 'Beban',     'saldo_normal' => 'debit', 'saldo_awal' => 0],
        ];
    }

    /**
     * Nama-nama lama yang perlu diperbarui (kode_akun => nama_akun baru)
     */
    private function getNamesToUpdate(): array
    {
        return [
            '211' => 'Hutang Usaha',
            '212' => 'Hutang Gaji',
            '213' => 'Hutang Asuransi',
            '214' => 'PPN Keluaran',
            '537' => 'BOP - Bubuk Bawang Putih',
            '538' => 'BOP - Susu',
            '539' => 'BOP - Keju',
            '541' => 'BOP BTKTL - Admin Produksi',
            '542' => 'BOP BTKTL - Manager Produksi',
            '543' => 'BOP BTKTL - Mandor',
        ];
    }

    public function up(): void
    {
        $now = now();
        $correctCoas = $this->getCorrectCoas();
        $namesToUpdate = $this->getNamesToUpdate();

        // Ambil semua user yang punya COA
        $userIds = DB::table('coas')->distinct()->pluck('user_id')->filter();

        // Jika tidak ada user dengan COA, skip
        if ($userIds->isEmpty()) {
            Log::info('Migration sync_correct_coa: Tidak ada user dengan COA, skip.');
            return;
        }

        foreach ($userIds as $userId) {
            $addedCount = 0;
            $updatedCount = 0;

            // 1. Tambahkan akun yang belum ada
            foreach ($correctCoas as $coa) {
                $exists = DB::table('coas')
                    ->where('user_id', $userId)
                    ->where('kode_akun', $coa['kode_akun'])
                    ->exists();

                if (!$exists) {
                    try {
                        DB::table('coas')->insert([
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
                        ]);
                        $addedCount++;
                    } catch (\Exception $e) {
                        Log::warning("sync_correct_coa: Gagal insert kode {$coa['kode_akun']} untuk user {$userId}: " . $e->getMessage());
                    }
                }
            }

            // 2. Perbarui nama akun yang berubah
            foreach ($namesToUpdate as $kodeAkun => $namaAkunBaru) {
                try {
                    $affected = DB::table('coas')
                        ->where('user_id', $userId)
                        ->where('kode_akun', $kodeAkun)
                        ->where('nama_akun', '!=', $namaAkunBaru)
                        ->update([
                            'nama_akun'  => $namaAkunBaru,
                            'updated_at' => $now,
                        ]);
                    if ($affected > 0) {
                        $updatedCount++;
                    }
                } catch (\Exception $e) {
                    Log::warning("sync_correct_coa: Gagal update nama kode {$kodeAkun} untuk user {$userId}: " . $e->getMessage());
                }
            }

            // 3. Rename kode lama 210 (Hutang Usaha) jika masih ada dan 211 belum ada dengan nama yang benar
            //    Sebelumnya seeder memakai 210=Hutang Usaha, 211=Hutang Gaji, 212=PPN Keluaran
            //    Sekarang: 211=Hutang Usaha, 212=Hutang Gaji, 213=Hutang Asuransi, 214=PPN Keluaran
            //    Jika user punya 210=Hutang Usaha tapi tidak punya 211=Hutang Usaha, rename kodenya
            $has210 = DB::table('coas')
                ->where('user_id', $userId)
                ->where('kode_akun', '210')
                ->exists();
            $has211AsHutangUsaha = DB::table('coas')
                ->where('user_id', $userId)
                ->where('kode_akun', '211')
                ->where('nama_akun', 'Hutang Usaha')
                ->exists();

            if ($has210 && !$has211AsHutangUsaha) {
                // Cek apakah 211 sudah ada dengan nama lain
                $existing211 = DB::table('coas')
                    ->where('user_id', $userId)
                    ->where('kode_akun', '211')
                    ->first();

                if (!$existing211) {
                    // Rename 210 → 211
                    try {
                        DB::table('coas')
                            ->where('user_id', $userId)
                            ->where('kode_akun', '210')
                            ->update([
                                'kode_akun'  => '211',
                                'nama_akun'  => 'Hutang Usaha',
                                'updated_at' => $now,
                            ]);
                        $updatedCount++;
                        Log::info("sync_correct_coa: Renamed COA 210→211 (Hutang Usaha) untuk user {$userId}");
                    } catch (\Exception $e) {
                        Log::warning("sync_correct_coa: Gagal rename 210→211 untuk user {$userId}: " . $e->getMessage());
                    }
                }
            }

            Log::info("sync_correct_coa: User {$userId} → ditambahkan {$addedCount} akun, diperbarui {$updatedCount} akun.");
        }

        Log::info('Migration sync_correct_coa selesai. COA sudah disinkronisasi ke semua user.');
    }

    public function down(): void
    {
        // Hapus akun-akun yang BARU ditambahkan oleh migration ini
        $newKodes = ['11511', '11512', '1164', '213', '214', '414', '514', '515', '516', '517', '518', '5311', '5322', '564'];

        DB::table('coas')
            ->whereIn('kode_akun', $newKodes)
            ->delete();

        Log::info('Migration sync_correct_coa rolled back: akun baru dihapus.');
    }
};
