<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder Satuan — idempotent, aman dijalankan berulang kali.
 * php artisan db:seed --class=SatuanSeeder
 */
class SatuanSeeder extends Seeder
{
    public function run(): void
    {
        $satuans = [
            ['kode' => 'ONS',   'nama' => 'Ons',          'tipe' => 'unit', 'kategori' => 'berat'],
            ['kode' => 'KG',    'nama' => 'Kilogram',      'tipe' => 'unit', 'kategori' => 'berat'],
            ['kode' => 'ML',    'nama' => 'Mililiter',     'tipe' => 'unit', 'kategori' => 'volume'],
            ['kode' => 'G',     'nama' => 'Gram',          'tipe' => 'unit', 'kategori' => 'berat'],
            ['kode' => 'LTR',   'nama' => 'Liter',         'tipe' => 'unit', 'kategori' => 'volume'],
            ['kode' => 'PTG',   'nama' => 'Potong',        'tipe' => 'unit', 'kategori' => 'jumlah'],
            ['kode' => 'EKOR',  'nama' => 'Ekor',          'tipe' => 'unit', 'kategori' => 'jumlah'],
            ['kode' => 'SDT',   'nama' => 'Sendok Teh',    'tipe' => 'unit', 'kategori' => 'volume'],
            ['kode' => 'SDM',   'nama' => 'Sendok Makan',  'tipe' => 'unit', 'kategori' => 'volume'],
            ['kode' => 'PCS',   'nama' => 'Pieces',        'tipe' => 'unit', 'kategori' => 'jumlah'],
            ['kode' => 'BNGKS', 'nama' => 'Bungkus',       'tipe' => 'unit', 'kategori' => 'jumlah'],
            ['kode' => 'WATT',  'nama' => 'Watt',          'tipe' => 'unit', 'kategori' => 'jumlah'],
            ['kode' => 'GL',    'nama' => 'Galon',         'tipe' => 'unit', 'kategori' => 'volume'],
            ['kode' => 'TBG',   'nama' => 'Tabung',        'tipe' => 'unit', 'kategori' => 'jumlah'],
            ['kode' => 'SNG',   'nama' => 'Siung',         'tipe' => 'unit', 'kategori' => 'jumlah'],
            ['kode' => 'CUP',   'nama' => 'Cup',           'tipe' => 'unit', 'kategori' => 'jumlah'],
            ['kode' => 'KLG',   'nama' => 'Kaleng',        'tipe' => 'unit', 'kategori' => 'jumlah'],
        ];

        foreach ($satuans as $satuan) {
            DB::table('satuans')->updateOrInsert(
                ['kode' => $satuan['kode']],
                [
                    'nama'       => $satuan['nama'],
                    'tipe'       => $satuan['tipe'],
                    'kategori'   => $satuan['kategori'],
                    'is_active'  => 1,
                    'is_dasar'   => 0,
                    'nilai_konversi'  => 1,
                    'faktor_ke_dasar' => 1,
                    'faktor'     => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // Sync ke satuan_grups juga (yang dipakai tampilan)
            DB::table('satuan_grups')->updateOrInsert(
                ['kode' => $satuan['kode']],
                [
                    'nama'       => $satuan['nama'],
                    'keterangan' => $satuan['nama'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->command->info('✅ SatuanSeeder selesai — ' . count($satuans) . ' satuan di-upsert.');
    }
}
