<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use App\Models\Jabatan;

class PegawaiSeeder extends Seeder
{
    /**
     * Seed data pegawai default untuk user baru
     * Data ini akan otomatis terisi saat user baru mendaftar
     */
    public function run(): void
    {
        // Ambil jabatan yang sudah ada
        $operatorProduksi = Jabatan::where('kode_jabatan', 'BT001')->first();
        $perbumbuan = Jabatan::where('kode_jabatan', 'BT002')->first();
        $pengemasan = Jabatan::where('kode_jabatan', 'BT003')->first();
        $supervisor = Jabatan::where('kode_jabatan', 'BT004')->first();
        $admin = Jabatan::where('kode_jabatan', 'BT005')->first();

        $pegawais = [
            // Pegawai BTKL (Biaya Tenaga Kerja Langsung)
            [
                'kode_pegawai' => 'PGW0001',
                'nama' => 'Ahmad Suryanto',
                'email' => 'ahmad.suryanto@example.com',
                'no_telepon' => '081234567801',
                'alamat' => 'Jl. Contoh No. 1, Jakarta',
                'jenis_kelamin' => 'L',
                'jabatan_id' => $operatorProduksi ? $operatorProduksi->id : null,
                'jabatan' => 'Operator Produksi',
                'jenis_pegawai' => 'btkl',
                'gaji_pokok' => 0,
                'tarif_per_jam' => $operatorProduksi ? $operatorProduksi->tarif_per_jam : 18000,
                'tunjangan' => 0,
                'asuransi' => $operatorProduksi ? $operatorProduksi->asuransi : 80000,
                'bank' => 'BRI',
                'nomor_rekening' => '1234567890',
                'nama_rekening' => 'Ahmad Suryanto',
            ],
            [
                'kode_pegawai' => 'PGW0002',
                'nama' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'no_telepon' => '081234567802',
                'alamat' => 'Jl. Contoh No. 2, Jakarta',
                'jenis_kelamin' => 'L',
                'jabatan_id' => $perbumbuan ? $perbumbuan->id : null,
                'jabatan' => 'Perbumbuan',
                'jenis_pegawai' => 'btkl',
                'gaji_pokok' => 0,
                'tarif_per_jam' => $perbumbuan ? $perbumbuan->tarif_per_jam : 18000,
                'tunjangan' => 0,
                'asuransi' => $perbumbuan ? $perbumbuan->asuransi : 80000,
                'bank' => 'BCA',
                'nomor_rekening' => '2345678901',
                'nama_rekening' => 'Budi Santoso',
            ],
            [
                'kode_pegawai' => 'PGW0003',
                'nama' => 'Rina Wijaya',
                'email' => 'rina.wijaya@example.com',
                'no_telepon' => '081234567803',
                'alamat' => 'Jl. Contoh No. 3, Jakarta',
                'jenis_kelamin' => 'P',
                'jabatan_id' => $pengemasan ? $pengemasan->id : null,
                'jabatan' => 'Pengemasan',
                'jenis_pegawai' => 'btkl',
                'gaji_pokok' => 0,
                'tarif_per_jam' => $pengemasan ? $pengemasan->tarif_per_jam : 17000,
                'tunjangan' => 0,
                'asuransi' => $pengemasan ? $pengemasan->asuransi : 0,
                'bank' => 'Mandiri',
                'nomor_rekening' => '3456789012',
                'nama_rekening' => 'Rina Wijaya',
            ],
            
            // Pegawai BTKTL (Biaya Tenaga Kerja Tidak Langsung)
            [
                'kode_pegawai' => 'PGW0004',
                'nama' => 'Dewi Lestari',
                'email' => 'dewi.lestari@example.com',
                'no_telepon' => '081234567804',
                'alamat' => 'Jl. Contoh No. 4, Jakarta',
                'jenis_kelamin' => 'P',
                'jabatan_id' => $supervisor ? $supervisor->id : null,
                'jabatan' => 'Supervisor',
                'jenis_pegawai' => 'btktl',
                'gaji_pokok' => $supervisor ? $supervisor->gaji_pokok : 4000000,
                'tarif_per_jam' => 0,
                'tunjangan' => $supervisor ? $supervisor->tunjangan : 500000,
                'asuransi' => $supervisor ? $supervisor->asuransi : 200000,
                'bank' => 'BNI',
                'nomor_rekening' => '4567890123',
                'nama_rekening' => 'Dewi Lestari',
            ],
            [
                'kode_pegawai' => 'PGW0005',
                'nama' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@example.com',
                'no_telepon' => '081234567805',
                'alamat' => 'Jl. Contoh No. 5, Jakarta',
                'jenis_kelamin' => 'P',
                'jabatan_id' => $admin ? $admin->id : null,
                'jabatan' => 'Admin',
                'jenis_pegawai' => 'btktl',
                'gaji_pokok' => $admin ? $admin->gaji_pokok : 3000000,
                'tarif_per_jam' => 0,
                'tunjangan' => $admin ? $admin->tunjangan : 500000,
                'asuransi' => $admin ? $admin->asuransi : 200000,
                'bank' => 'BRI',
                'nomor_rekening' => '5678901234',
                'nama_rekening' => 'Siti Nurhaliza',
            ],
        ];

        foreach ($pegawais as $pegawai) {
            // Cek apakah sudah ada berdasarkan kode_pegawai atau email
            $existing = Pegawai::where('kode_pegawai', $pegawai['kode_pegawai'])
                ->orWhere('email', $pegawai['email'])
                ->first();
            
            if (!$existing) {
                // Jika belum ada, buat baru
                Pegawai::create($pegawai);
            }
        }
    }
}
