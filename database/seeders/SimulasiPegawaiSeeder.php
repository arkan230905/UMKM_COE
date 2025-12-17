<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimulasiPegawaiSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data pegawai lama
        DB::table('pegawais')->truncate();

        $pegawais = [
            // BTKL (Biaya Tenaga Kerja Langsung) - 3 Pegawai
            [
                'nama' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Operator Produksi',
                'gaji' => 8000000,
                'alamat' => 'Jl. Merdeka No. 10, Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@example.com',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Operator Mesin',
                'gaji' => 7200000,
                'alamat' => 'Jl. Sudirman No. 45, Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Ahmad Wijaya',
                'email' => 'ahmad.wijaya@example.com',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Helper Produksi',
                'gaji' => 5600000,
                'alamat' => 'Jl. Gatot Subroto No. 67, Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // BTKTL (Biaya Tenaga Kerja Tidak Langsung) - 3 Pegawai
            [
                'nama' => 'Ani Wijayanti',
                'email' => 'ani.wijayanti@example.com',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Staff Admin',
                'gaji' => 5000000,
                'alamat' => 'Jl. Thamrin No. 12, Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Rudi Hermawan',
                'email' => 'rudi.hermawan@example.com',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Kepala Gudang',
                'gaji' => 6000000,
                'alamat' => 'Jl. Pahlawan No. 5, Tangerang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Eka Putri Lestari',
                'email' => 'eka.putri@example.com',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Finance Officer',
                'gaji' => 7000000,
                'alamat' => 'Jl. Melati No. 3, Depok',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        // Insert langsung ke database
        foreach ($pegawais as $pegawai) {
            DB::table('pegawais')->insert($pegawai);
        }

        $this->command->info('✓ 6 pegawai berhasil dibuat (3 BTKL + 3 BTKTL)');
    }
}
