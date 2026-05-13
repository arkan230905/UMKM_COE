<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // Menggunakan updateOrInsert agar tidak duplicate saat dijalankan ulang
        DB::table('perusahaan')->updateOrInsert(
            ['id' => 1],
            [
                'nama'       => 'Jasuke Manufaktur Utama',
                'alamat'     => 'Jl. Industri Manufaktur No. 1',
                'email'      => 'admin@jasuke.com',
                'telepon'    => '021-12345678',
                'kode'       => 'JASUKE',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}