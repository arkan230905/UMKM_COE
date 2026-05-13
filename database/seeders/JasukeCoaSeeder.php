<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JasukeCoaSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua ID perusahaan yang ada
        $companies = DB::table('companies')->pluck('id');

        if ($companies->isEmpty()) {
            $this->command->error('❌ Gagal: Tidak ada data di tabel companies. Jalankan CompanySeeder dulu!');
            return;
        }

        // Struktur Akun: [kode, nama, tipe, induk, saldo_normal, kategori, is_header, saldo_awal]
        $accounts = [
            // ASET
            ['11',   'Aset',                                     'Aset', null,  'debit',  'Aset',                    1],
            ['111',  'Kas Bank',                                 'Aset', '11',  'debit',  'Aset Lancar',             0, 100000000],
            ['112',  'Kas',                                      'Aset', '11',  'debit',  'Aset Lancar',             0, 75000000],
            
            // PERSEDIAAN (Penting untuk Proses Costing)
            ['114',  'Pers. Bahan Baku',                         'Aset', '11',  'debit',  'Aset Lancar',             1],
            ['1141', 'Pers. Bahan Baku Jagung',                  'Aset', '114', 'debit',  'Aset Lancar',             0],
            ['115',  'Pers. Bahan Pendukung',                    'Aset', '11',  'debit',  'Aset Lancar',             1],
            ['1151', 'Pers. Bahan Pendukung Susu',               'Aset', '115', 'debit',  'Aset Lancar',             0],
            ['116',  'Pers. Barang Jadi',                        'Aset', '11',  'debit',  'Aset Lancar',             1],
            ['1161', 'Pers. Barang Jadi Jasuke',                 'Aset', '116', 'debit',  'Aset Lancar',             0],
            ['117',  'Pers. Barang dalam Proses',                'Aset', '11',  'debit',  'Aset Lancar',             0],

            // MODAL
            ['31',   'Modal',                                    'Modal', null,  'kredit', 'Modal', 1],
            ['310',  'Modal Usaha',                              'Modal', '31',  'kredit', 'Modal', 0, 264450000],

            // BIAYA PRODUKSI (BBB, BTKL, BOP)
            ['51',   'BBB - Biaya Bahan Baku',                   'Biaya Bahan Baku', null,  'debit', 'Biaya Produksi', 1],
            ['510',  'BBB - Jagung',                             'Biaya Bahan Baku', '51',  'debit', 'Biaya Produksi', 0],
            ['52',   'BTKL',                                     'Biaya Tenaga Kerja Langsung', null,  'debit', 'Biaya Produksi', 1],
            ['520',  'BTKL - Produksi Jasuke',                   'Biaya Tenaga Kerja Langsung', '52',  'debit', 'Biaya Produksi', 0],
            ['53',   'BOP',                                      'Biaya Overhead Pabrik', null,  'debit', 'Biaya Produksi', 1],
            ['530',  'BOP - Susu',                               'Biaya Overhead Pabrik', '53',  'debit', 'Biaya Produksi', 0],

            // HPP
            ['1600', 'Harga Pokok Penjualan',                    'Biaya Bahan Baku',              null,   'debit', 'HPP', 1],
            ['1601', 'HPP - Bahan Baku',                         'Biaya Bahan Baku',              '1600', 'debit', 'HPP', 0],
            ['1602', 'HPP - BTKL',                               'Biaya Tenaga Kerja Langsung',   '1600', 'debit', 'HPP', 0],
            ['1603', 'HPP - Overhead Pabrik',                    'Biaya Overhead Pabrik',         '1600', 'debit', 'HPP', 0],
        ];

        foreach ($companies as $id) {
            foreach ($accounts as $account) {
                DB::table('coas')->updateOrInsert(
                    ['kode_akun' => $account[0], 'company_id' => $id],
                    [
                        'nama_akun'      => $account[1],
                        'tipe_akun'      => $account[2],
                        'kode_induk'     => $account[3],
                        'saldo_normal'   => $account[4],
                        'kategori_akun'  => $account[5],
                        'is_akun_header' => $account[6],
                        'saldo_awal'     => $account[7] ?? 0,
                        'updated_at'     => now(),
                        'created_at'     => now(),
                    ]
                );
            }
        }

        $this->command->info('✅ JasukeCoaSeeder selesai untuk ' . $companies->count() . ' perusahaan.');
    }
}