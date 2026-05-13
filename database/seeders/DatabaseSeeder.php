<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,     // 1. Isi Perusahaan dulu
            CoaTemplateSeeder::class, // 2. Isi Template COA
            JasukeCoaSeeder::class,   // 3. Baru isi COA spesifik Jasuke
        ]);
    }
}