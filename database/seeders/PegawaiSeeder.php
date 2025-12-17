<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $pegawais = [
            // BTKL (Biaya Tenaga Kerja Langsung) - 3 Pegawai
            [
                'nama' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'no_telp' => '081234567890',
                'alamat' => 'Jl. Merdeka No. 10, Jakarta',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Operator Produksi',
                'jenis_pegawai' => 'BTKL',
                'tarif_per_jam' => 50000,
                'tunjangan' => 500000,
                'asuransi' => 200000,
                'bank' => 'BCA',
                'nomor_rekening' => '1234567890',
                'nama_rekening' => 'Budi Santoso',
            ],
            [
                'nama' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@example.com',
                'no_telp' => '081234567891',
                'alamat' => 'Jl. Sudirman No. 45, Jakarta',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Operator Mesin',
                'jenis_pegawai' => 'BTKL',
                'tarif_per_jam' => 45000,
                'tunjangan' => 400000,
                'asuransi' => 150000,
                'bank' => 'BCA',
                'nomor_rekening' => '1234567891',
                'nama_rekening' => 'Siti Nurhaliza',
            ],
            [
                'nama' => 'Ahmad Wijaya',
                'email' => 'ahmad.wijaya@example.com',
                'no_telp' => '081234567892',
                'alamat' => 'Jl. Gatot Subroto No. 67, Jakarta',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Helper Produksi',
                'jenis_pegawai' => 'BTKL',
                'tarif_per_jam' => 35000,
                'tunjangan' => 300000,
                'asuransi' => 100000,
                'bank' => 'Mandiri',
                'nomor_rekening' => '1234567892',
                'nama_rekening' => 'Ahmad Wijaya',
            ],
            // BTKTL (Biaya Tenaga Kerja Tidak Langsung) - 3 Pegawai
            [
                'nama' => 'Ani Wijayanti',
                'email' => 'ani.wijayanti@example.com',
                'no_telp' => '081234567893',
                'alamat' => 'Jl. Thamrin No. 12, Jakarta',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Staff Admin',
                'jenis_pegawai' => 'BTKTL',
                'gaji_pokok' => 5000000,
                'tunjangan' => 1000000,
                'asuransi' => 300000,
                'bank' => 'BCA',
                'nomor_rekening' => '1234567893',
                'nama_rekening' => 'Ani Wijayanti',
            ],
            [
                'nama' => 'Rudi Hermawan',
                'email' => 'rudi.hermawan@example.com',
                'no_telp' => '081234567894',
                'alamat' => 'Jl. Pahlawan No. 5, Tangerang',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Kepala Gudang',
                'jenis_pegawai' => 'BTKTL',
                'gaji_pokok' => 6000000,
                'tunjangan' => 1500000,
                'asuransi' => 400000,
                'bank' => 'Mandiri',
                'nomor_rekening' => '1234567894',
                'nama_rekening' => 'Rudi Hermawan',
            ],
            [
                'nama' => 'Eka Putri Lestari',
                'email' => 'eka.putri@example.com',
                'no_telp' => '081234567895',
                'alamat' => 'Jl. Melati No. 3, Depok',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Finance Officer',
                'jenis_pegawai' => 'BTKTL',
                'gaji_pokok' => 7000000,
                'tunjangan' => 2000000,
                'asuransi' => 500000,
                'bank' => 'BCA',
                'nomor_rekening' => '1234567895',
                'nama_rekening' => 'Eka Putri Lestari',
            ]
        ];

        foreach ($pegawais as $pegawai) {
            Pegawai::create($pegawai);
        }

        $this->command->info('✓ 6 pegawai berhasil dibuat (3 BTKL + 3 BTKTL)');
    }
}
