<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisAsetSeeder extends Seeder
{
    public function run()
    {
        $jenisAsets = [
            ['nama' => 'Aset Tetap', 'deskripsi' => 'Aset yang digunakan dalam operasional jangka panjang'],
            ['nama' => 'Aset Lancar', 'deskripsi' => 'Aset yang dapat dikonversi menjadi kas dalam waktu singkat'],
            ['nama' => 'Aset Tidak Berwujud', 'deskripsi' => 'Aset yang tidak memiliki bentuk fisik seperti hak paten, merek dagang'],
        ];

        foreach ($jenisAsets as $jenis) {
            DB::table('jenis_asets')->insert([
                'nama' => $jenis['nama'],
                'deskripsi' => $jenis['deskripsi'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $kategoriAsets = [
            ['kode' => 'TNH', 'nama' => 'Tanah', 'jenis_aset_id' => 1],
            ['kode' => 'BGN', 'nama' => 'Bangunan', 'jenis_aset_id' => 1],
            ['kode' => 'KND', 'nama' => 'Kendaraan', 'jenis_aset_id' => 1],
            ['kode' => 'PRL', 'nama' => 'Peralatan', 'jenis_aset_id' => 1],
            ['kode' => 'MSN', 'nama' => 'Mesin', 'jenis_aset_id' => 1],
            ['kode' => 'INV', 'nama' => 'Inventaris Kantor', 'jenis_aset_id' => 1],
        ];

        foreach ($kategoriAsets as $kategori) {
            DB::table('kategori_asets')->insert([
                'kode' => $kategori['kode'],
                'nama' => $kategori['nama'],
                'jenis_aset_id' => $kategori['jenis_aset_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
