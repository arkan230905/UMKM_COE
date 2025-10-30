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
                'nomor_induk_pegawai' => 'EMP' . str_pad(1, 3, '0', STR_PAD_LEFT),
                'nama' => 'Muhammad Arkan Abiyyu',
                'email' => 'arkan.abi@example.com',
                'no_telp' => '081234567890',
                'alamat' => 'Jl. Merdeka No. 10, Jakarta',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Manajer Produksi',
                'kategori_tenaga_kerja' => 'BTKL',
                'gaji' => 15000000,
                'gaji_pokok' => 10000000,
                'tunjangan' => 5000000,
                'tarif_per_jam' => 50000,
                'tanggal_masuk' => '2024-01-01',
                'status_aktif' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nomor_induk_pegawai' => 'EMP' . str_pad(2, 3, '0', STR_PAD_LEFT),
                'nama' => 'Githa Permata',
                'email' => 'githa.permata@example.com',
                'no_telp' => '081234567891',
                'alamat' => 'Jl. Sudirman No. 45, Jakarta',
                'jenis_kelamin' => 'P',
                'jabatan' => 'HRD Manager',
                'kategori_tenaga_kerja' => 'BTKL',
                'gaji' => 12000000,
                'gaji_pokok' => 10000000,
                'tunjangan' => 2000000,
                'tarif_per_jam' => 45000,
                'tanggal_masuk' => '2024-01-15',
                'status_aktif' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nomor_induk_pegawai' => 'EMP' . str_pad(3, 3, '0', STR_PAD_LEFT),
                'nama' => 'Nayla Putri',
                'email' => 'nayla.putri@example.com',
                'no_telp' => '081234567892',
                'alamat' => 'Jl. Gatot Subroto No. 67, Jakarta',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Supervisor Produksi',
                'kategori_tenaga_kerja' => 'BTKL',
                'gaji' => 10000000,
                'gaji_pokok' => 8500000,
                'tunjangan' => 1500000,
                'tarif_per_jam' => 40000,
                'tanggal_masuk' => '2024-02-01',
                'status_aktif' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nomor_induk_pegawai' => 'EMP' . str_pad(4, 3, '0', STR_PAD_LEFT),
                'nama' => 'Chindi Lestari',
                'email' => 'chindi.les@example.com',
                'no_telp' => '081234567893',
                'alamat' => 'Jl. Thamrin No. 12, Jakarta',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Kepala Gudang',
                'kategori_tenaga_kerja' => 'BTKL',
                'gaji' => 9500000,
                'gaji_pokok' => 8000000,
                'tunjangan' => 1500000,
                'tarif_per_jam' => 38000,
                'tanggal_masuk' => '2024-02-15',
                'status_aktif' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // BTKTL (Karyawan Kontrak)
            [
                'nomor_induk_pegawai' => 'EMP' . str_pad(5, 3, '0', STR_PAD_LEFT),
                'nama' => 'Rizki Maulana',
                'email' => 'rizki.maulana@example.com',
                'no_telp' => '081234567894',
                'alamat' => 'Jl. Pahlawan No. 5, Tangerang',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Operator Mesin',
                'kategori_tenaga_kerja' => 'BTKTL',
                'gaji' => 4500000,
                'gaji_pokok' => 4000000,
                'tunjangan' => 500000,
                'tarif_per_jam' => 30000,
                'tanggal_masuk' => '2024-03-01',
                'status_aktif' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nomor_induk_pegawai' => 'EMP' . str_pad(6, 3, '0', STR_PAD_LEFT),
                'nama' => 'Siti Aisyah',
                'email' => 'siti.aisyah@example.com',
                'no_telp' => '081234567895',
                'alamat' => 'Jl. Melati No. 3, Depok',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Quality Control',
                'kategori_tenaga_kerja' => 'BTKTL',
                'gaji' => 4300000,
                'gaji_pokok' => 3800000,
                'tunjangan' => 500000,
                'tarif_per_jam' => 28000,
                'tanggal_masuk' => '2024-03-15',
                'status_aktif' => true,
                'created_by' => 1,
                'updated_by' => 1,
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
