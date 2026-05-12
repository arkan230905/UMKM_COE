<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert data langsung ke tabel asets
        DB::table('asets')->insert([
            [
                'nama' => 'Kursi Salon',
                'kategori' => 'Furniture & Fixtures',
                'harga' => 4000000,
                'tanggal_beli' => now()->subYears(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Kursi Cuci Rambut',
                'kategori' => 'Furniture & Fixtures',
                'harga' => 2000000,
                'tanggal_beli' => now()->subYears(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Gedung',
                'kategori' => 'Bangunan',
                'harga' => 30000000,
                'tanggal_beli' => now()->subYears(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
