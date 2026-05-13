<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // Menggunakan updateOrInsert agar tidak duplicate saat dijalankan ulang
        DB::table('companies')->updateOrInsert(
            ['id' => 1],
            [
                'nama_perusahaan' => 'Jasuke Manufaktur Utama',
                'alamat'          => 'Jl. Industri Manufaktur No. 1',
                'email'           => 'admin@jasuke.com',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        );
    }
}