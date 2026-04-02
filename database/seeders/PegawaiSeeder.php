<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $pegawais = [
            // BTKL (Karyawan Tetap)
            [
                'kode_pegawai' => 'EMP' . substr(time(), -3),
                'nama' => 'Muhammad Arkan Abiyyu',
                'email' => 'arkan.abi' . time() . '1@example.com',
                'no_telepon' => '081234567890',
                'alamat' => 'Jl. Merdeka No. 10, Jakarta',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Manajer Produksi',
                'kategori' => 'BTKL',
                'gaji' => 15000000,
                'gaji_pokok' => 10000000,
                'tunjangan' => 5000000,
                'tarif_per_jam' => 50000,
                'jenis_pegawai' => 'btkl',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_pegawai' => 'EMP' . substr(time(), -3) . '2',
                'nama' => 'Githa Permata',
                'email' => 'githa.permata' . time() . '2@example.com',
                'no_telepon' => '081234567891',
                'alamat' => 'Jl. Sudirman No. 45, Jakarta',
                'jenis_kelamin' => 'P',
                'jabatan' => 'HRD Manager',
                'kategori' => 'BTKL',
                'gaji' => 12000000,
                'gaji_pokok' => 10000000,
                'tunjangan' => 2000000,
                'tarif_per_jam' => 45000,
                'jenis_pegawai' => 'btkl',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_pegawai' => 'EMP' . substr(time(), -3) . '3',
                'nama' => 'Nayla Putri',
                'email' => 'nayla.putri' . time() . '3@example.com',
                'no_telepon' => '081234567892',
                'alamat' => 'Jl. Gatot Subroto No. 67, Jakarta',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Supervisor Produksi',
                'kategori' => 'BTKL',
                'gaji' => 10000000,
                'gaji_pokok' => 8500000,
                'tunjangan' => 1500000,
                'tarif_per_jam' => 40000,
                'jenis_pegawai' => 'btkl',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_pegawai' => 'EMP' . substr(time(), -3) . '4',
                'nama' => 'Chindi Lestari',
                'email' => 'chindi.les' . time() . '4@example.com',
                'no_telepon' => '081234567893',
                'alamat' => 'Jl. Thamrin No. 12, Jakarta',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Kepala Gudang',
                'kategori' => 'BTKL',
                'gaji' => 9500000,
                'gaji_pokok' => 8000000,
                'tunjangan' => 1500000,
                'tarif_per_jam' => 38000,
                'jenis_pegawai' => 'btkl',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // BTKTL (Karyawan Kontrak)
            [
                'kode_pegawai' => 'EMP' . substr(time(), -3) . '5',
                'nama' => 'Rizki Maulana',
                'email' => 'rizki.maulana' . time() . '5@example.com',
                'no_telepon' => '081234567894',
                'alamat' => 'Jl. Pahlawan No. 5, Tangerang',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Operator Mesin',
                'kategori' => 'BTKTL',
                'gaji' => 4500000,
                'gaji_pokok' => 4000000,
                'tunjangan' => 500000,
                'tarif_per_jam' => 30000,
                'jenis_pegawai' => 'btktl',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_pegawai' => 'EMP' . substr(time(), -3) . '6',
                'nama' => 'Siti Aisyah',
                'email' => 'siti.aisyah' . time() . '6@example.com',
                'no_telepon' => '081234567895',
                'alamat' => 'Jl. Melati No. 3, Depok',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Quality Control',
                'kategori' => 'BTKTL',
                'gaji' => 4300000,
                'gaji_pokok' => 3800000,
                'tunjangan' => 500000,
                'tarif_per_jam' => 28000,
                'jenis_pegawai' => 'btktl',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($pegawais as $pegawai) {
            Pegawai::create($pegawai);
        }
    }
}
