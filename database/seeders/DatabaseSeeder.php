<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Setup awal: Company untuk multi-tenant
        $this->call([
            CompanySeeder::class,     // Create companies for multi-tenant
        ]);

        // Setup awal: Multi-tenant COA Template dan Satuan (untuk setiap perusahaan)
        $this->call([
            CoaDefaultSeeder::class,  // COA Default untuk multi-tenant (50 akun)
            SatuanDefaultSeeder::class, // Satuan Default untuk multi-tenant (16 satuan)
            JasukeCoaSeeder::class,    // COA Jasuke lengkap (94 akun) - backup
            SatuanSeeder::class,       // Satuan (global, tidak per company) - backup
            JabatanSeeder::class,      // Jabatan/Kualifikasi Tenaga Kerja
            InitialSetupSeeder::class, // Jenis Aset, Kategori Aset
        ]);

        // Pastikan user admin dibuat terlebih dahulu
        $this->call([
            UserSeeder::class,
        ]);

        // Buat user tambahan jika diperlukan
        if (!User::where('email', 'adminumkm@gmail.com')->exists()) {
            User::create([
                'name' => 'Admin UMKM',
                'email' => 'adminumkm@gmail.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        // OPTIONAL: Seeder berikut hanya untuk development/testing
        // Uncomment jika ingin data sample
        /*
        $this->call([
            AccountsTableSeeder::class,
            PegawaiSeeder::class,
            PegawaiDataSeeder::class,
            BopSeeder::class,
            PresensiSeeder::class,
            PresensiDataSeeder::class,
        ]);
        */
    }
}
