<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder COA untuk usaha Jasuke.
 * Idempotent: aman dijalankan berulang kali.
 * Gunakan: php artisan db:seed --class=JasukeCoaSeeder
 */
class JasukeCoaSeeder extends Seeder
{
    public function run(): void
    {
        // company_id = 1 (sesuaikan jika berbeda)
        $companyId = 1;

        /**
         * Format tiap akun:
         * [kode_akun, nama_akun, tipe_akun, kode_induk, saldo_normal, kategori_akun, is_header]
         *
         * tipe_akun mengikuti enum yang sudah ada di tabel:
         *   Aset | Kewajiban | Modal | Pendapatan |
         *   Biaya Bahan Baku | Biaya Tenaga Kerja Langsung |
         *   Biaya Overhead Pabrik | BOP Tidak Langsung Lainnya
         */
        $accounts = [
            // ── ASET ──────────────────────────────────────────────────────
            ['11',   'Aset',                              'Aset',                          null,  'debit',  'Aset',         1],
            ['111',  'Kas Bank',                          'Aset',                          '11',  'debit',  'Aset Lancar',  0],
            ['112',  'Kas',                               'Aset',                          '11',  'debit',  'Aset Lancar',  0],
            ['113',  'Kas Kecil',                         'Aset',                          '11',  'debit',  'Aset Lancar',  0],
            ['114',  'Pers. Bahan Baku',                  'Aset',                          '11',  'debit',  'Aset Lancar',  1],
            ['1141', 'Pers. Bahan Baku Jagung',           'Aset',                          '114', 'debit',  'Aset Lancar',  0],
            ['115',  'Pers. Bahan Pendukung',             'Aset',                          '11',  'debit',  'Aset Lancar',  1],
            ['1151', 'Pers. Bahan Pendukung Susu',        'Aset',                          '115', 'debit',  'Aset Lancar',  0],
            ['1152', 'Pers. Bahan Pendukung Keju',        'Aset',                          '115', 'debit',  'Aset Lancar',  0],
            ['1153', 'Pers. Bahan Pendukung Kemasan (Cup)','Aset',                         '115', 'debit',  'Aset Lancar',  0],
            ['116',  'Pers. Barang Jadi',                 'Aset',                          '11',  'debit',  'Aset Lancar',  1],
            ['1161', 'Pers. Barang Jadi Jasuke',          'Aset',                          '116', 'debit',  'Aset Lancar',  0],
            ['117',  'Pers. Barang dalam Proses',         'Aset',                          '11',  'debit',  'Aset Lancar',  0],
            ['118',  'Piutang',                           'Aset',                          '11',  'debit',  'Aset Lancar',  0],
            ['119',  'Peralatan',                         'Aset',                          '11',  'debit',  'Aset Tidak Lancar', 0],
            ['120',  'Akumulasi Penyusutan Peralatan',    'Aset',                          '11',  'kredit', 'Aset Tidak Lancar', 0],
            ['125',  'Mesin',                             'Aset',                          '11',  'debit',  'Aset Tidak Lancar', 0],
            ['126',  'Akumulasi Penyusutan Mesin',        'Aset',                          '11',  'kredit', 'Aset Tidak Lancar', 0],

            // ── KEWAJIBAN ─────────────────────────────────────────────────
            ['21',   'Hutang',                            'Kewajiban',                     null,  'kredit', 'Kewajiban',    1],
            ['210',  'Hutang Usaha',                      'Kewajiban',                     '21',  'kredit', 'Kewajiban Jangka Pendek', 0],
            ['211',  'Hutang Gaji',                       'Kewajiban',                     '21',  'kredit', 'Kewajiban Jangka Pendek', 0],

            // ── MODAL ─────────────────────────────────────────────────────
            ['31',   'Modal',                             'Modal',                         null,  'kredit', 'Modal',        1],
            ['310',  'Modal Usaha',                       'Modal',                         '31',  'kredit', 'Modal',        0],
            ['311',  'Prive',                             'Modal',                         '31',  'debit',  'Modal',        0],

            // ── PENDAPATAN ────────────────────────────────────────────────
            ['41',   'Penjualan',                         'Pendapatan',                    null,  'kredit', 'Pendapatan',   1],
            ['410',  'Penjualan - Jasuke',                'Pendapatan',                    '41',  'kredit', 'Pendapatan',   0],
            ['42',   'Retur Penjualan',                   'Pendapatan',                    null,  'debit',  'Pendapatan',   0],

            // ── BIAYA BAHAN BAKU ──────────────────────────────────────────
            ['51',   'BBB - Biaya Bahan Baku',            'Biaya Bahan Baku',              null,  'debit',  'Biaya Produksi', 1],
            ['510',  'BBB - Jagung',                      'Biaya Bahan Baku',              '51',  'debit',  'Biaya Produksi', 0],

            // ── BTKL ──────────────────────────────────────────────────────
            ['52',   'BTKL',                              'Biaya Tenaga Kerja Langsung',   null,  'debit',  'Biaya Produksi', 1],
            ['520',  'BTKL - Produksi Jasuke',            'Biaya Tenaga Kerja Langsung',   '52',  'debit',  'Biaya Produksi', 0],

            // ── BOP BAHAN ─────────────────────────────────────────────────
            ['53',   'BOP',                               'Biaya Overhead Pabrik',         null,  'debit',  'Biaya Produksi', 1],
            ['530',  'BOP - Susu',                        'Biaya Overhead Pabrik',         '53',  'debit',  'Biaya Produksi', 0],
            ['531',  'BOP - Keju',                        'Biaya Overhead Pabrik',         '53',  'debit',  'Biaya Produksi', 0],
            ['532',  'BOP - Kemasan',                     'Biaya Overhead Pabrik',         '53',  'debit',  'Biaya Produksi', 0],

            // ── BOP LAIN ──────────────────────────────────────────────────
            ['55',   'BOP Lain',                          'BOP Tidak Langsung Lainnya',    null,  'debit',  'Biaya Produksi', 1],
            ['550',  'BOP - Listrik',                     'BOP Tidak Langsung Lainnya',    '55',  'debit',  'Biaya Produksi', 0],
            ['551',  'BOP - Air',                         'BOP Tidak Langsung Lainnya',    '55',  'debit',  'Biaya Produksi', 0],
            ['552',  'BOP - Gas',                         'BOP Tidak Langsung Lainnya',    '55',  'debit',  'Biaya Produksi', 0],
            ['553',  'BOP - Penyusutan Peralatan',        'BOP Tidak Langsung Lainnya',    '55',  'debit',  'Biaya Produksi', 0],
        ];

        // Urutan insert: parent dulu, baru child (hindari FK violation)
        // Array sudah diurutkan dari parent ke child di atas.
        foreach ($accounts as [$kode, $nama, $tipe, $induk, $saldoNormal, $kategori, $isHeader]) {
            DB::table('coas')->updateOrInsert(
                // Kondisi pencarian — unik per kode + company
                ['kode_akun' => $kode, 'company_id' => $companyId],
                // Data yang di-set / di-update
                [
                    'nama_akun'    => $nama,
                    'tipe_akun'    => $tipe,
                    'kode_induk'   => $induk,
                    'saldo_normal' => $saldoNormal,
                    'kategori_akun'=> $kategori,
                    'is_akun_header' => $isHeader,
                    'saldo_awal'   => 0,
                    'company_id'   => $companyId,
                    'updated_at'   => now(),
                    'created_at'   => now(),
                ]
            );
        }

        $this->command->info('✅ JasukeCoaSeeder selesai — ' . count($accounts) . ' akun di-upsert.');
    }
}
