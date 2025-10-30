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
        // Pastikan user admin dibuat terlebih dahulu
        $this->call([
            UserSeeder::class,
        ]);

        // Kemudian jalankan seeder lainnya
        $this->call([
            AccountsTableSeeder::class,
            CoaSeeder::class,
            PegawaiSeeder::class,
            BopSeeder::class,
            PresensiSeeder::class, // Add PresensiSeeder after PegawaiSeeder
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
    }
}
