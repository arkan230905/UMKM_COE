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
        // Setup awal: COA Template (untuk user baru yang daftar)
        $this->call([
            CoaTemplateSeeder::class,  // COA template yang akan di-copy saat registrasi
            InitialSetupSeeder::class, // Satuan, Jenis Aset, Kategori Aset
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
