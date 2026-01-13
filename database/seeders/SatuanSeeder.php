<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Satuan;

class SatuanSeeder extends Seeder
{
    public function run(): void
    {
        $satuans = [
            // Satuan Berat (dasar: gram)
            ['kode' => 'G', 'nama' => 'Gram', 'kategori' => 'berat', 'faktor_ke_dasar' => 1],
            ['kode' => 'KG', 'nama' => 'Kilogram', 'kategori' => 'berat', 'faktor_ke_dasar' => 1000],
            ['kode' => 'MG', 'nama' => 'Miligram', 'kategori' => 'berat', 'faktor_ke_dasar' => 0.001],
            ['kode' => 'ONS', 'nama' => 'Ons', 'kategori' => 'berat', 'faktor_ke_dasar' => 100],
            ['kode' => 'TON', 'nama' => 'Ton', 'kategori' => 'berat', 'faktor_ke_dasar' => 1000000],
            
            // Satuan Volume (dasar: mililiter)
            ['kode' => 'ML', 'nama' => 'Mililiter', 'kategori' => 'volume', 'faktor_ke_dasar' => 1],
            ['kode' => 'L', 'nama' => 'Liter', 'kategori' => 'volume', 'faktor_ke_dasar' => 1000],
            ['kode' => 'CL', 'nama' => 'Centiliter', 'kategori' => 'volume', 'faktor_ke_dasar' => 10],
            
            // Satuan Panjang (dasar: cm)
            ['kode' => 'CM', 'nama' => 'Centimeter', 'kategori' => 'panjang', 'faktor_ke_dasar' => 1],
            ['kode' => 'M', 'nama' => 'Meter', 'kategori' => 'panjang', 'faktor_ke_dasar' => 100],
            ['kode' => 'MM', 'nama' => 'Milimeter', 'kategori' => 'panjang', 'faktor_ke_dasar' => 0.1],
            
            // Satuan Jumlah (dasar: pcs)
            ['kode' => 'PCS', 'nama' => 'Pieces', 'kategori' => 'jumlah', 'faktor_ke_dasar' => 1],
            ['kode' => 'UNIT', 'nama' => 'Unit', 'kategori' => 'jumlah', 'faktor_ke_dasar' => 1],
            ['kode' => 'SET', 'nama' => 'Set', 'kategori' => 'jumlah', 'faktor_ke_dasar' => 1],
            ['kode' => 'BOX', 'nama' => 'Box', 'kategori' => 'jumlah', 'faktor_ke_dasar' => 1],
            ['kode' => 'PACK', 'nama' => 'Pack', 'kategori' => 'jumlah', 'faktor_ke_dasar' => 1],
            ['kode' => 'DUS', 'nama' => 'Dus', 'kategori' => 'jumlah', 'faktor_ke_dasar' => 1],
            ['kode' => 'LUSIN', 'nama' => 'Lusin', 'kategori' => 'jumlah', 'faktor_ke_dasar' => 12],
            ['kode' => 'KODI', 'nama' => 'Kodi', 'kategori' => 'jumlah', 'faktor_ke_dasar' => 20],
            
            // Satuan Waktu (dasar: jam)
            ['kode' => 'JAM', 'nama' => 'Jam', 'kategori' => 'waktu', 'faktor_ke_dasar' => 1],
            ['kode' => 'HARI', 'nama' => 'Hari', 'kategori' => 'waktu', 'faktor_ke_dasar' => 24],
        ];

        foreach ($satuans as $satuan) {
            Satuan::updateOrCreate(
                ['kode' => $satuan['kode']],
                $satuan
            );
        }
    }
}
