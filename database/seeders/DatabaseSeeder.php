<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Buat Data Perusahaan Dulu (Wajib karena COA butuh company_id)
            CompanySeeder::class, 
            
            // 2. Isi Template Master jika diperlukan untuk perbandingan
            CoaTemplateSeeder::class, 
            
            // 3. Isi COA Spesifik Jasuke yang sudah kita definisikan di atas
            JasukeCoaSeeder::class, 
        ]);
    }
}