<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PresensiUser;
use Illuminate\Support\Facades\Hash;

class PresensiUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample presensi users
        $users = [
            [
                'nama_lengkap' => 'Ahmad Rizki',
                'nik' => 'EMP001',
                'jabatan' => 'Staff Produksi',
                'email' => 'ahmad.rizki@company.com',
                'kode_perusahaan' => 'UMKM2026',
                'is_active' => true,
            ],
            [
                'nama_lengkap' => 'Siti Nurhaliza',
                'nik' => 'EMP002',
                'jabatan' => 'Staff Administrasi',
                'email' => 'siti.nurhaliza@company.com',
                'kode_perusahaan' => 'UMKM2026',
                'is_active' => true,
            ],
            [
                'nama_lengkap' => 'Budi Santoso',
                'nik' => 'EMP003',
                'jabatan' => 'Supervisor Produksi',
                'email' => 'budi.santoso@company.com',
                'kode_perusahaan' => 'UMKM2026',
                'is_active' => true,
            ],
            [
                'nama_lengkap' => 'Dewi Lestari',
                'nik' => 'EMP004',
                'jabatan' => 'Quality Control',
                'email' => 'dewi.lestari@company.com',
                'kode_perusahaan' => 'UMKM2026',
                'is_active' => true,
            ],
            [
                'nama_lengkap' => 'Eko Prasetyo',
                'nik' => 'EMP005',
                'jabatan' => 'Staff Gudang',
                'email' => 'eko.prasetyo@company.com',
                'kode_perusahaan' => 'UMKM2026',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            PresensiUser::create([
                'nama_lengkap' => $user['nama_lengkap'],
                'nik' => $user['nik'],
                'jabatan' => $user['jabatan'],
                'email' => $user['email'],
                'password' => Hash::make('password123'), // Default password for testing
                'kode_perusahaan' => $user['kode_perusahaan'],
                'is_active' => $user['is_active'],
            ]);
        }

        $this->command->info('Sample presensi users created successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Kode Perusahaan: UMKM2026');
        $this->command->info('NIK: EMP001, EMP002, EMP003, EMP004, EMP005');
        $this->command->info('Password: password123 (if email login is enabled)');
    }
}
