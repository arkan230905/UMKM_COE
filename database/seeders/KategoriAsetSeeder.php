<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriAsetSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kategori_asets')->insert([
            ['jenis_aset_id' => 1, 'nama' => 'Tanah', 'umur_ekonomis' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['jenis_aset_id' => 1, 'nama' => 'Bangunan', 'umur_ekonomis' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['jenis_aset_id' => 1, 'nama' => 'Peralatan', 'umur_ekonomis' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['jenis_aset_id' => 1, 'nama' => 'Kendaraan', 'umur_ekonomis' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['jenis_aset_id' => 1, 'nama' => 'Furniture & Fixtures', 'umur_ekonomis' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['jenis_aset_id' => 2, 'nama' => 'Persediaan', 'umur_ekonomis' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['jenis_aset_id' => 2, 'nama' => 'Piutang', 'umur_ekonomis' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['jenis_aset_id' => 3, 'nama' => 'Goodwill', 'umur_ekonomis' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['jenis_aset_id' => 3, 'nama' => 'Paten', 'umur_ekonomis' => 10, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
