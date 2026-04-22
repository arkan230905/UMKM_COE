<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Satuan;

class SatuanSeeder extends Seeder
{
    public function run(): void
    {
        $satuans = [
            ['kode' => 'ONS',   'nama' => 'Ons',          'kategori' => 'berat',   'faktor_ke_dasar' => 100],
            ['kode' => 'KG',    'nama' => 'Kilogram',     'kategori' => 'berat',   'faktor_ke_dasar' => 1000],
            ['kode' => 'ML',    'nama' => 'Mililiter',    'kategori' => 'volume',  'faktor_ke_dasar' => 1],
            ['kode' => 'G',     'nama' => 'Gram',         'kategori' => 'berat',   'faktor_ke_dasar' => 1],
            ['kode' => 'LTR',   'nama' => 'Liter',        'kategori' => 'volume',  'faktor_ke_dasar' => 1000],
            ['kode' => 'PTG',   'nama' => 'Potong',       'kategori' => 'jumlah',  'faktor_ke_dasar' => 1],
            ['kode' => 'EKOR',  'nama' => 'Ekor',         'kategori' => 'jumlah',  'faktor_ke_dasar' => 1],
            ['kode' => 'SDT',   'nama' => 'Sendok Teh',   'kategori' => 'volume',  'faktor_ke_dasar' => 5],
            ['kode' => 'SDM',   'nama' => 'Sendok Makan', 'kategori' => 'volume',  'faktor_ke_dasar' => 15],
            ['kode' => 'PCS',   'nama' => 'Pieces',       'kategori' => 'jumlah',  'faktor_ke_dasar' => 1],
            ['kode' => 'BNGKS', 'nama' => 'Bungkus',      'kategori' => 'jumlah',  'faktor_ke_dasar' => 1],
            ['kode' => 'CUP',   'nama' => 'Cup',          'kategori' => 'jumlah',  'faktor_ke_dasar' => 1],
            ['kode' => 'GL',    'nama' => 'Galon',        'kategori' => 'volume',  'faktor_ke_dasar' => 19000],
            ['kode' => 'TBG',   'nama' => 'Tabung',       'kategori' => 'jumlah',  'faktor_ke_dasar' => 1],
            ['kode' => 'SNG',   'nama' => 'Siung',        'kategori' => 'jumlah',  'faktor_ke_dasar' => 1],
        ];

        foreach ($satuans as $satuan) {
            Satuan::updateOrCreate(
                ['kode' => $satuan['kode']],
                $satuan
            );
        }
    }
}
