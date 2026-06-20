<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ReplaceAllCoaSeeder
 *
 * Menghapus SEMUA COA yang ada untuk SETIAP user, lalu mengisi ulang
 * dengan daftar akun baru sesuai struktur yang telah disetujui.
 *
 * Jalankan dengan:
 *   php artisan db:seed --class=ReplaceAllCoaSeeder
 *
 * PERHATIAN: Seeder ini akan menghapus seluruh data COA yang ada.
 *            Pastikan sudah backup database sebelum menjalankan.
 */
class ReplaceAllCoaSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->get();

        if ($users->isEmpty()) {
            if ($this->command) {
                $this->command->error('Tidak ada user ditemukan di database.');
            }
            return;
        }

        $now = now();

        $coaList = [
            // =========================================================
            // ASET (11)
            // =========================================================
            ['kode_akun' => '11',   'nama_akun' => 'Aset',                                          'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],

            // Kas Bank (111)
            ['kode_akun' => '111',  'nama_akun' => 'Kas Bank',                                      'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1111', 'nama_akun' => 'Bank BRI',                                      'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1112', 'nama_akun' => 'Bank BCA',                                      'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1123', 'nama_akun' => 'Bank Mandiri',                                  'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1124', 'nama_akun' => 'Seabank',                                       'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],

            // Kas & Kas Kecil
            ['kode_akun' => '112',  'nama_akun' => 'Kas',                                           'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '113',  'nama_akun' => 'Kas Kecil',                                     'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],

            // Persediaan Bahan Baku (114)
            ['kode_akun' => '114',  'nama_akun' => 'Pers. Bahan Baku',                              'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1141', 'nama_akun' => 'Pers. Bahan Baku ayam potong',                  'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1142', 'nama_akun' => 'Pers. Bahan Baku ayam kampung',                 'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1143', 'nama_akun' => 'Pers. Bahan Baku Jagung',                       'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],

            // Persediaan Bahan Pendukung (115)
            ['kode_akun' => '115',  'nama_akun' => 'Pers. Bahan Pendukung',                         'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1151', 'nama_akun' => 'Pers. Bahan Pendukung Air',                     'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1152', 'nama_akun' => 'Pers. Bahan Pendukung Minyak Goreng',           'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1153', 'nama_akun' => 'Pers. Bahan Pendukung Tepung Terigu',           'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1154', 'nama_akun' => 'Pers. Bahan Pendukung Tepung Maizena',          'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1155', 'nama_akun' => 'Pers. Bahan Pendukung Lada',                    'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1156', 'nama_akun' => 'Pers. Bahan Pendukung Bubuk Kaldu',             'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1157', 'nama_akun' => 'Pers. Bahan Pendukung Bubuk Bawang Putih',      'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1158', 'nama_akun' => 'Pers. Bahan Pendukung Susu',                    'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1159', 'nama_akun' => 'Pers. Bahan Pendukung Keju',                    'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1160', 'nama_akun' => 'Pers. Bahan Pendukung Kemasan',                 'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],

            // Persediaan Barang Jadi (116)
            ['kode_akun' => '116',  'nama_akun' => 'Pers. Barang Jadi',                             'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1161', 'nama_akun' => 'Pers. Barang Jadi Ayam Crispy Macdi',           'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1162', 'nama_akun' => 'Pers. Barang Jadi Ayam Goreng Bundo',           'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1163', 'nama_akun' => 'Pers. Barang Jadi Jasuke',                      'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],

            // Persediaan Barang Dalam Proses (117)
            ['kode_akun' => '117',  'nama_akun' => 'Pers. Barang dalam Proses',                     'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1171', 'nama_akun' => 'Pers. Barang Dalam Proses - BBB (WIP BBB)',     'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1172', 'nama_akun' => 'Pers. Barang Dalam Proses - BTKL (WIP BTKL)',   'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '1173', 'nama_akun' => 'Pers. Barang Dalam Proses - BOP (WIP BOP)',     'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],

            // Aset Tetap & Lainnya
            ['kode_akun' => '118',  'nama_akun' => 'Piutang',                                       'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '119',  'nama_akun' => 'Peralatan',                                     'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '120',  'nama_akun' => 'Akumulasi Penyusutan Peralatan',                'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '121',  'nama_akun' => 'Gedung',                                        'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '122',  'nama_akun' => 'Akumulasi Penyusutan Gedung',                   'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '123',  'nama_akun' => 'Kendaraan',                                     'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '124',  'nama_akun' => 'Akumulasi Penyusutan Kendaraan',                'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '125',  'nama_akun' => 'Mesin',                                         'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '126',  'nama_akun' => 'Akumulasi Penyusutan Mesin',                    'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],
            ['kode_akun' => '127',  'nama_akun' => 'PPN Masukkan',                                  'tipe_akun' => 'Aset',      'saldo_normal' => 'debit'],

            // =========================================================
            // KEWAJIBAN (21)
            // =========================================================
            ['kode_akun' => '21',   'nama_akun' => 'Hutang',                                        'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '210',  'nama_akun' => 'Hutang Usaha',                                  'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '211',  'nama_akun' => 'Hutang Gaji',                                   'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '212',  'nama_akun' => 'PPN Keluaran',                                  'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'kredit'],

            // =========================================================
            // MODAL (31)
            // =========================================================
            ['kode_akun' => '31',   'nama_akun' => 'Modal',                                         'tipe_akun' => 'Modal',     'saldo_normal' => 'kredit'],
            ['kode_akun' => '311',  'nama_akun' => 'Modal Usaha',                                   'tipe_akun' => 'Modal',     'saldo_normal' => 'kredit'],
            ['kode_akun' => '312',  'nama_akun' => 'Prive',                                         'tipe_akun' => 'Modal',     'saldo_normal' => 'kredit'],

            // =========================================================
            // PENDAPATAN (41)
            // =========================================================
            ['kode_akun' => '41',   'nama_akun' => 'Penjualan',                                     'tipe_akun' => 'Pendapatan','saldo_normal' => 'kredit'],
            ['kode_akun' => '411',  'nama_akun' => 'Penjualan - Produk Ayam Crispy Macdi',          'tipe_akun' => 'Pendapatan','saldo_normal' => 'kredit'],
            ['kode_akun' => '412',  'nama_akun' => 'Penjualan - Produk Ayam Goreng Bundo',          'tipe_akun' => 'Pendapatan','saldo_normal' => 'kredit'],
            ['kode_akun' => '413',  'nama_akun' => 'Penjualan - Jasuke',                            'tipe_akun' => 'Pendapatan','saldo_normal' => 'kredit'],
            ['kode_akun' => '42',   'nama_akun' => 'Retur Penjualan',                               'tipe_akun' => 'Pendapatan','saldo_normal' => 'kredit'],
            ['kode_akun' => '43',   'nama_akun' => 'Pendapatan Lain-Lain',                          'tipe_akun' => 'Pendapatan','saldo_normal' => 'kredit'],

            // =========================================================
            // BEBAN - BBB (Biaya Bahan Baku) (51)
            // =========================================================
            ['kode_akun' => '51',   'nama_akun' => 'BBB - Biaya Bahan Baku',                        'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '511',  'nama_akun' => 'BBB - ayam potong',                             'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '512',  'nama_akun' => 'BBB - ayam kampung',                            'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '513',  'nama_akun' => 'BBB - Jagung',                                  'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],

            // =========================================================
            // BEBAN - BTKL (Biaya Tenaga Kerja Langsung) (52)
            // =========================================================
            ['kode_akun' => '52',   'nama_akun' => 'BTKL-Biaya Tenaga Kerja Langsung',              'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '521',  'nama_akun' => 'Beban Gaji dan upah (BTKL) - Penggorengan',     'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '522',  'nama_akun' => 'Beban Gaji dan upah (BTKL) - Perbumbuan',       'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '523',  'nama_akun' => 'Beban Gaji dan upah (BTKL) - Pengemasan',       'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '524',  'nama_akun' => 'Beban Gaji dan upah (BTKL) - Pengukusan',       'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],

            // =========================================================
            // BEBAN - BOP (Biaya Overhead Pabrik) (53)
            // =========================================================
            ['kode_akun' => '53',   'nama_akun' => 'BOP',                                           'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '531',  'nama_akun' => 'BOP-Air',                                       'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '532',  'nama_akun' => 'BOP-Minyak Goreng',                             'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '533',  'nama_akun' => 'BOP-Tepung Terigu',                             'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '534',  'nama_akun' => 'BOP-Tepung Maizena',                            'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '535',  'nama_akun' => 'BOP-Lada',                                      'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '536',  'nama_akun' => 'BOP-Bubuk Kaldu',                               'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '537',  'nama_akun' => 'BOP-Susu',                                      'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '538',  'nama_akun' => 'BOP-Keju',                                      'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '539',  'nama_akun' => 'BOP-Kemasan',                                   'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],

            // =========================================================
            // BEBAN - BOP BTKTL (Biaya Tenaga Kerja Tidak Langsung) (54)
            // =========================================================
            ['kode_akun' => '54',   'nama_akun' => 'BOP BTKTL-Biaya Tenaga Kerja Tidak Langsung',   'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '541',  'nama_akun' => 'BOP BTKTL - Admin',                             'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '542',  'nama_akun' => 'BOP BTKTL - Kasir',                             'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '543',  'nama_akun' => 'BOP BTKTL - Manager',                           'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],

            // =========================================================
            // BEBAN - BOP Lainnya (55)
            // =========================================================
            ['kode_akun' => '55',   'nama_akun' => 'BOP Lainnya',                                   'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '551',  'nama_akun' => 'BOP - Listrik',                                 'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '552',  'nama_akun' => 'BOP - Air',                                     'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '553',  'nama_akun' => 'BOP - Gas',                                     'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '554',  'nama_akun' => 'BOP - Penyusutan Gedung',                       'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '555',  'nama_akun' => 'BOP - Penyusutan Peralatan',                    'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '556',  'nama_akun' => 'BOP - Penyusutan Kendaraan',                    'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '557',  'nama_akun' => 'BOP - Penyusutan Mesin',                            'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '558',  'nama_akun' => 'Beban Transport Pembelian',                     'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],
            ['kode_akun' => '559',  'nama_akun' => 'Diskon Pembelian',                              'tipe_akun' => 'Beban',     'saldo_normal' => 'debit'],

            // =========================================================
            // BEBAN - Harga Pokok Penjualan (56)
            // =========================================================
            ['kode_akun' => '56',   'nama_akun' => 'Harga Pokok Penjualan',                            'tipe_akun' => 'Beban',  'saldo_normal' => 'debit'],
            ['kode_akun' => '561',  'nama_akun' => 'Harga Pokok Penjualan - Produk Ayam Crispy Macdi', 'tipe_akun' => 'Beban',  'saldo_normal' => 'debit'],
            ['kode_akun' => '562',  'nama_akun' => 'Harga Pokok Penjualan - Produk Ayam Goreng Bundo', 'tipe_akun' => 'Beban',  'saldo_normal' => 'debit'],
            ['kode_akun' => '563',  'nama_akun' => 'Harga Pokok Penjualan - Jasuke',                   'tipe_akun' => 'Beban',  'saldo_normal' => 'debit'],
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($users as $user) {
            // Hapus COA lama milik user ini
            $deleted = DB::table('coas')->where('user_id', $user->id)->delete();

            // Siapkan data baru
            $rows = [];
            foreach ($coaList as $coa) {
                $rows[] = [
                    'user_id'            => $user->id,
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

            if ($this->command) {
                $this->command->info("User ID {$user->id} ({$user->name}): {$deleted} akun lama dihapus, " . count($rows) . " akun baru ditambahkan.");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        if ($this->command) {
            $this->command->info('===================================================');
            $this->command->info('COA berhasil diganti untuk ' . $users->count() . ' user.');
            $this->command->info('Total akun per user: ' . count($coaList));
            $this->command->info('===================================================');
        }
    }
}
