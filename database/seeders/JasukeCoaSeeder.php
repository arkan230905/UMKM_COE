<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder COA gabungan Jasuke + akun lama.
 * Idempotent — aman dijalankan berulang kali.
 * php artisan db:seed --class=JasukeCoaSeeder
 */
class JasukeCoaSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1;

        // [kode_akun, nama_akun, tipe_akun, kode_induk, saldo_normal, kategori_akun, is_header]
        $accounts = [
            // ── ASET ─────────────────────────────────────────────────────────────────
            ['11',   'Aset',                                     'Aset', null,  'debit',  'Aset',                    1],
            ['111',  'Kas Bank',                                 'Aset', '11',  'debit',  'Aset Lancar',             0, 100000000],
            ['112',  'Kas',                                      'Aset', '11',  'debit',  'Aset Lancar',             0, 75000000],
            ['113',  'Kas Kecil',                                'Aset', '11',  'debit',  'Aset Lancar',             0],
            // Persediaan Bahan Baku
            ['114',  'Pers. Bahan Baku',                         'Aset', '11',  'debit',  'Aset Lancar',             1],
            ['1141', 'Pers. Bahan Baku Jagung',                  'Aset', '114', 'debit',  'Aset Lancar',             0],
            ['1142', 'Pers. Bahan Baku Ayam Potong',             'Aset', '114', 'debit',  'Aset Lancar',             0],
            ['1143', 'Pers. Bahan Baku Ayam Kampung',            'Aset', '114', 'debit',  'Aset Lancar',             0],
            ['1144', 'Pers. Bahan Baku Bebek',                   'Aset', '114', 'debit',  'Aset Lancar',             0],
            ['1145', 'Pers. Bahan Baku Ayam Lainnya',            'Aset', '114', 'debit',  'Aset Lancar',             0],
            // Persediaan Bahan Pendukung
            ['115',  'Pers. Bahan Pendukung',                    'Aset', '11',  'debit',  'Aset Lancar',             1],
            ['1150', 'Pers. Bahan Pendukung Air',                'Aset', '115', 'debit',  'Aset Lancar',             0],
            ['1151', 'Pers. Bahan Pendukung Susu',               'Aset', '115', 'debit',  'Aset Lancar',             0],
            ['1152', 'Pers. Bahan Pendukung Keju',               'Aset', '115', 'debit',  'Aset Lancar',             0],
            ['1153', 'Pers. Bahan Pendukung Kemasan (Cup)',       'Aset', '115', 'debit',  'Aset Lancar',             0],
            ['1154', 'Pers. Bahan Pendukung Minyak Goreng',      'Aset', '115', 'debit',  'Aset Lancar',             0],
            ['1155', 'Pers. Bahan Pendukung Tepung Terigu',      'Aset', '115', 'debit',  'Aset Lancar',             0],
            ['1156', 'Pers. Bahan Pendukung Tepung Maizena',     'Aset', '115', 'debit',  'Aset Lancar',             0],
            ['1157', 'Pers. Bahan Pendukung Lada',               'Aset', '115', 'debit',  'Aset Lancar',             0],
            ['1158', 'Pers. Bahan Pendukung Bubuk Kaldu',        'Aset', '115', 'debit',  'Aset Lancar',             0],
            ['1159', 'Pers. Bahan Pendukung Bubuk Bawang Putih', 'Aset', '115', 'debit',  'Aset Lancar',             0],
            ['1160', 'Pers. Bahan Pendukung Kemasan',            'Aset', '115', 'debit',  'Aset Lancar',             0],
            // Persediaan Barang Jadi
            ['116',  'Pers. Barang Jadi',                        'Aset', '11',  'debit',  'Aset Lancar',             1],
            ['1161', 'Pers. Barang Jadi Jasuke',                 'Aset', '116', 'debit',  'Aset Lancar',             0],
            ['1162', 'Pers. Barang Jadi Ayam Crispy Macdi',      'Aset', '116', 'debit',  'Aset Lancar',             0],
            ['1163', 'Pers. Barang Jadi Ayam Goreng Bundo',      'Aset', '116', 'debit',  'Aset Lancar',             0],
            // Lainnya
            ['117',  'Pers. Barang dalam Proses',                'Aset', '11',  'debit',  'Aset Lancar',             0],
            ['118',  'Piutang',                                  'Aset', '11',  'debit',  'Aset Lancar',             0],
            ['119',  'Peralatan',                                'Aset', '11',  'debit',  'Aset Tidak Lancar',       0],
            ['120',  'Akumulasi Penyusutan Peralatan',           'Aset', '11',  'kredit', 'Aset Tidak Lancar',       0],
            ['121',  'Gedung',                                   'Aset', '11',  'debit',  'Aset Tidak Lancar',       0],
            ['122',  'Akumulasi Penyusutan Gedung',              'Aset', '11',  'kredit', 'Aset Tidak Lancar',       0],
            ['123',  'Kendaraan',                                'Aset', '11',  'debit',  'Aset Tidak Lancar',       0],
            ['124',  'Akumulasi Penyusutan Kendaraan',           'Aset', '11',  'kredit', 'Aset Tidak Lancar',       0],
            ['125',  'Mesin',                                    'Aset', '11',  'debit',  'Aset Tidak Lancar',       0],
            ['126',  'Akumulasi Penyusutan Mesin',               'Aset', '11',  'kredit', 'Aset Tidak Lancar',       0],
            ['127',  'PPN Masukan',                              'Aset', '11',  'debit',  'Aset Lancar',             0],

            // ── KEWAJIBAN ─────────────────────────────────────────────────────────────
            ['21',   'Hutang',                                   'Kewajiban', null,  'kredit', 'Kewajiban',               1],
            ['210',  'Hutang Usaha',                             'Kewajiban', '21',  'kredit', 'Kewajiban Jangka Pendek', 0],
            ['211',  'Hutang Gaji',                              'Kewajiban', '21',  'kredit', 'Kewajiban Jangka Pendek', 0],
            ['212',  'PPN Keluaran',                             'Kewajiban', '21',  'kredit', 'Kewajiban Jangka Pendek', 0],

            // ── MODAL ─────────────────────────────────────────────────────────────────
            ['31',   'Modal',                                    'Modal', null,  'kredit', 'Modal', 1],
            ['310',  'Modal Usaha',                              'Modal', '31',  'kredit', 'Modal', 0, 264450000],
            ['311',  'Prive',                                    'Modal', '31',  'debit',  'Modal', 0],

            // ── PENDAPATAN ────────────────────────────────────────────────────────────
            ['41',   'Penjualan',                                'Pendapatan', null,  'kredit', 'Pendapatan', 1],
            ['410',  'Penjualan - Jasuke',                       'Pendapatan', '41',  'kredit', 'Pendapatan', 0],
            ['411',  'Penjualan - Ayam Crispy Macdi',            'Pendapatan', '41',  'kredit', 'Pendapatan', 0],
            ['412',  'Penjualan - Ayam Goreng Bundo',            'Pendapatan', '41',  'kredit', 'Pendapatan', 0],
            ['42',   'Retur Penjualan',                          'Pendapatan', null,  'debit',  'Pendapatan', 0],
            ['43',   'Pendapatan Ongkir',                        'Pendapatan', null,  'kredit', 'Pendapatan', 0],

            // ── BIAYA BAHAN BAKU (BBB) ────────────────────────────────────────────────
            ['51',   'BBB - Biaya Bahan Baku',                   'Biaya Bahan Baku', null,  'debit', 'Biaya Produksi', 1],
            ['510',  'BBB - Jagung',                             'Biaya Bahan Baku', '51',  'debit', 'Biaya Produksi', 0],
            ['511',  'BBB - Ayam Potong',                        'Biaya Bahan Baku', '51',  'debit', 'Biaya Produksi', 0],
            ['512',  'BBB - Ayam Kampung',                       'Biaya Bahan Baku', '51',  'debit', 'Biaya Produksi', 0],
            ['513',  'BBB - Bebek',                              'Biaya Bahan Baku', '51',  'debit', 'Biaya Produksi', 0],

            // ── BTKL ──────────────────────────────────────────────────────────────────
            ['52',   'BTKL',                                     'Biaya Tenaga Kerja Langsung', null,  'debit', 'Biaya Produksi', 1],
            ['520',  'BTKL - Produksi Jasuke',                   'Biaya Tenaga Kerja Langsung', '52',  'debit', 'Biaya Produksi', 0],
            ['521',  'BTKL - Perbumbuan',                        'Biaya Tenaga Kerja Langsung', '52',  'debit', 'Biaya Produksi', 0],
            ['522',  'BTKL - Penggorengan',                      'Biaya Tenaga Kerja Langsung', '52',  'debit', 'Biaya Produksi', 0],
            ['523',  'BTKL - Pengemasan',                        'Biaya Tenaga Kerja Langsung', '52',  'debit', 'Biaya Produksi', 0],

            // ── BOP BAHAN ─────────────────────────────────────────────────────────────
            ['53',   'BOP',                                      'Biaya Overhead Pabrik', null,  'debit', 'Biaya Produksi', 1],
            ['530',  'BOP - Susu',                               'Biaya Overhead Pabrik', '53',  'debit', 'Biaya Produksi', 0],
            ['531',  'BOP - Keju',                               'Biaya Overhead Pabrik', '53',  'debit', 'Biaya Produksi', 0],
            ['532',  'BOP - Kemasan',                            'Biaya Overhead Pabrik', '53',  'debit', 'Biaya Produksi', 0],
            ['533',  'BOP - Air',                                'Biaya Overhead Pabrik', '53',  'debit', 'Biaya Produksi', 0],
            ['534',  'BOP - Minyak Goreng',                      'Biaya Overhead Pabrik', '53',  'debit', 'Biaya Produksi', 0],
            ['535',  'BOP - Tepung Terigu',                      'Biaya Overhead Pabrik', '53',  'debit', 'Biaya Produksi', 0],
            ['536',  'BOP - Tepung Maizena',                     'Biaya Overhead Pabrik', '53',  'debit', 'Biaya Produksi', 0],
            ['537',  'BOP - Lada',                               'Biaya Overhead Pabrik', '53',  'debit', 'Biaya Produksi', 0],
            ['538',  'BOP - Bubuk Kaldu',                        'Biaya Overhead Pabrik', '53',  'debit', 'Biaya Produksi', 0],
            ['539',  'BOP - Bubuk Bawang Putih',                 'Biaya Overhead Pabrik', '53',  'debit', 'Biaya Produksi', 0],

            // ── BTKTL ─────────────────────────────────────────────────────────────────
            ['54',   'BTKTL',                                    'Biaya Tenaga Kerja Tidak Langsung', null,  'debit', 'Biaya Produksi', 1],
            ['540',  'BTKTL - Pegawai Pemasaran',                'Biaya Tenaga Kerja Tidak Langsung', '54',  'debit', 'Biaya Produksi', 0],
            ['541',  'BTKTL - Pegawai Kemasan',                  'Biaya Tenaga Kerja Tidak Langsung', '54',  'debit', 'Biaya Produksi', 0],
            ['542',  'BTKTL - Satpam Pabrik',                    'Biaya Tenaga Kerja Tidak Langsung', '54',  'debit', 'Biaya Produksi', 0],
            ['543',  'BTKTL - Cleaning Service',                 'Biaya Tenaga Kerja Tidak Langsung', '54',  'debit', 'Biaya Produksi', 0],
            ['544',  'BTKTL - Mandor',                           'Biaya Tenaga Kerja Tidak Langsung', '54',  'debit', 'Biaya Produksi', 0],
            ['545',  'BTKTL - Pegawai Keuangan',                 'Biaya Tenaga Kerja Tidak Langsung', '54',  'debit', 'Biaya Produksi', 0],
            ['546',  'BTKTL - Lainnya',                          'Biaya Tenaga Kerja Tidak Langsung', '54',  'debit', 'Biaya Produksi', 0],

            // ── BOP TIDAK LANGSUNG LAINNYA ────────────────────────────────────────────
            ['55',   'BOP Lain',                                 'BOP Tidak Langsung Lainnya', null,  'debit', 'Biaya Produksi', 1],
            ['550',  'BOP - Listrik',                            'BOP Tidak Langsung Lainnya', '55',  'debit', 'Biaya Produksi', 0],
            ['551',  'BOP - Sewa Tempat',                        'BOP Tidak Langsung Lainnya', '55',  'debit', 'Biaya Produksi', 0],
            ['552',  'BOP - Penyusutan Gedung',                  'BOP Tidak Langsung Lainnya', '55',  'debit', 'Biaya Produksi', 0],
            ['553',  'BOP - Penyusutan Peralatan',               'BOP Tidak Langsung Lainnya', '55',  'debit', 'Biaya Produksi', 0],
            ['554',  'BOP - Penyusutan Kendaraan',               'BOP Tidak Langsung Lainnya', '55',  'debit', 'Biaya Produksi', 0],
            ['555',  'BOP - Penyusutan Mesin',                   'BOP Tidak Langsung Lainnya', '55',  'debit', 'Biaya Produksi', 0],
            ['556',  'BOP - Air Tambahan',                       'BOP Tidak Langsung Lainnya', '55',  'debit', 'Biaya Produksi', 0],
            ['557',  'BOP - Lainnya',                            'BOP Tidak Langsung Lainnya', '55',  'debit', 'Biaya Produksi', 0],
            ['558',  'Beban Transport Pembelian',                'BOP Tidak Langsung Lainnya', '55',  'debit', 'Biaya Produksi', 0],
            ['559',  'Diskon Pembelian',                         'BOP Tidak Langsung Lainnya', '55',  'debit', 'Biaya Produksi', 0],

            // ── HPP ───────────────────────────────────────────────────────────────────
            ['1600', 'Harga Pokok Penjualan',                    'Biaya Bahan Baku',              null,   'debit', 'HPP', 1],
            ['1601', 'HPP - Bahan Baku',                         'Biaya Bahan Baku',              '1600', 'debit', 'HPP', 0],
            ['1602', 'HPP - BTKL',                               'Biaya Tenaga Kerja Langsung',   '1600', 'debit', 'HPP', 0],
            ['1603', 'HPP - Overhead Pabrik',                    'Biaya Overhead Pabrik',         '1600', 'debit', 'HPP', 0],
        ];

        // ── GUARD: Hapus COA orphan (company_id NULL atau duplikat) ──────────────
        // Ini mencegah duplikat setelah git pull + db:seed berulang
        $validCodes = array_column($accounts, 0);

        // Hapus COA dengan company_id NULL (data lama/orphan)
        DB::table('coas')->whereNull('company_id')->delete();

        // Hapus COA dengan company_id = companyId yang kode_akunnya tidak ada di daftar
        DB::table('coas')
            ->where('company_id', $companyId)
            ->whereNotIn('kode_akun', $validCodes)
            ->whereNotExists(function ($q) {
                // Jangan hapus jika masih direferensikan di journal_lines
                $q->select(DB::raw(1))
                  ->from('journal_lines')
                  ->whereColumn('journal_lines.coa_id', 'coas.id');
            })
            ->delete();

        // ── UPSERT semua akun Jasuke ──────────────────────────────────────────────
        foreach ($accounts as $account) {
            [$kode, $nama, $tipe, $induk, $saldoNormal, $kategori, $isHeader] = $account;
            $saldoAwal = $account[7] ?? 0;

            DB::table('coas')->updateOrInsert(
                ['kode_akun' => $kode, 'company_id' => $companyId],
                [
                    'nama_akun'    => $nama,
                    'tipe_akun'    => $tipe,
                    'saldo_normal' => $saldoNormal,
                    'kategori_akun'=> $kategori,
                    'saldo_awal'   => $saldoAwal,
                    'company_id'   => $companyId,
                    'updated_at'   => now(),
                    'created_at'   => now(),
                ]
            );
        }

        $this->command->info('✅ JasukeCoaSeeder selesai — ' . count($accounts) . ' akun di-upsert.');
    }
}
