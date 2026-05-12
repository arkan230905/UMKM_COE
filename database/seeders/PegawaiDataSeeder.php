<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;

class PegawaiDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pegawais = [
            [
                'nama' => 'Ahmad Suryanto',
                'email' => 'ahmad@gmail.com',
                'no_telepon' => '089561985919',
                'alamat' => 'Jambi',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Perbumbuan',
                'jenis_pegawai' => 'BTKL',
                'bank' => 'BRI',
                'nomor_rekening' => '2345234242',
                'nama_rekening' => 'Ahmad Suryanto',
            ],
            [
                'nama' => 'Budi Susanto',
                'email' => 'budi@gmail.com',
                'no_telepon' => '081234567890',
                'alamat' => 'Pangalengan',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Penggorengan',
                'jenis_pegawai' => 'BTKL',
                'bank' => 'BRI',
                'nomor_rekening' => '2345678901343',
                'nama_rekening' => 'Budi Susanto',
            ],
            [
                'nama' => 'Dedi Gunawan',
                'email' => 'dedi@gmail.com',
                'no_telepon' => '084565644650',
                'alamat' => 'Jalan Kopo',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Bagian Gudang',
                'jenis_pegawai' => 'BTKTL',
                'bank' => 'SEABANK',
                'nomor_rekening' => '9765578657',
                'nama_rekening' => 'Dedi Gunawan',
            ],
            [
                'nama' => 'Rina Wijaya',
                'email' => 'rina@gmail.com',
                'no_telepon' => '087645678901',
                'alamat' => 'Jalan Sukapura',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Pengemasan',
                'jenis_pegawai' => 'BTKL',
                'bank' => 'BRI',
                'nomor_rekening' => '234567456343',
                'nama_rekening' => 'Rina Wijaya',
            ],
        ];

        foreach ($pegawais as $pegawaiData) {
            // Cek apakah pegawai sudah berdasarkan email
            $existing = Pegawai::where('email', $pegawaiData['email'])->first();
            
            if ($existing) {
                echo "⏭️  Pegawai sudah ada: {$pegawaiData['nama']} ({$pegawaiData['email']})\n";
                continue;
            }

            // Kode pegawai akan auto-generate dari model boot method
            Pegawai::create($pegawaiData);
            echo "✅ Pegawai dibuat: {$pegawaiData['nama']}\n";
        }

        echo "\n✅ Seeding data pegawai selesai.\n";
    }
}
