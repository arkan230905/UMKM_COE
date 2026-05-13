<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,     // Isi Perusahaan ID 1
            CoaTemplateSeeder::class, // Isi Template Master
            JasukeCoaSeeder::class,   // Isi COA Spesifik Jasuke
        ]);
    }
}