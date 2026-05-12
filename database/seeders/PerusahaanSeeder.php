<?php

namespace Database\Seeders;

use App\Models\Perusahaan;
use Illuminate\Database\Seeder;

class PerusahaanSeeder extends Seeder
{
    public function run(): void
    {
        // Buat perusahaan demo untuk testing
        Perusahaan::firstOrCreate(
            ['kode' => 'DEMO-001'],
            [
                'nama' => 'UMKM Demo Company',
                'alamat' => 'Jl. Demo No. 123, Jakarta Pusat',
                'email' => 'demo@umkm.com',
                'telepon' => '021-1234567',
            ]
        );

        // Buat perusahaan dengan kode pendek untuk user
        Perusahaan::firstOrCreate(
            ['kode' => 'PR-691D49'],  // Dipendekkan dari PR-691D495A183EF menjadi 10 karakter
            [
                'nama' => 'Perusahaan Produksi UMKM',
                'alamat' => 'Jl. Industri No. 45, Bandung',
                'email' => 'info@produksi-umkm.com',
                'telepon' => '022-1234567',
            ]
        );
    }
}