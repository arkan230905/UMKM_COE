<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;

class JabatanSeeder extends Seeder
{
    /**
     * Seed data jabatan default untuk user baru
     * Data ini akan otomatis terisi saat user baru mendaftar
     */
    public function run(): void
    {
        $jabatans = [
            // BTKL (Biaya Tenaga Kerja Langsung)
            [
                'kode_jabatan' => 'BT001',
                'nama' => 'Operator Produksi',
                'kategori' => 'btkl',
                'gaji_pokok' => 0,
                'tunjangan' => 0,
                'tunjangan_transport' => 120000,
                'tunjangan_konsumsi' => 375000,
                'asuransi' => 80000,
                'tarif' => 18000,
                'tarif_per_jam' => 18000,
                'deskripsi' => 'Tenaga kerja yang terlibat langsung dalam proses produksi'
            ],
            [
                'kode_jabatan' => 'BT002',
                'nama' => 'Perbumbuan',
                'kategori' => 'btkl',
                'gaji_pokok' => 0,
                'tunjangan' => 0,
                'tunjangan_transport' => 120000,
                'tunjangan_konsumsi' => 375000,
                'asuransi' => 80000,
                'tarif' => 18000,
                'tarif_per_jam' => 18000,
                'deskripsi' => 'Tenaga kerja untuk proses perbumbuan'
            ],
            [
                'kode_jabatan' => 'BT003',
                'nama' => 'Pengemasan',
                'kategori' => 'btkl',
                'gaji_pokok' => 0,
                'tunjangan' => 0,
                'tunjangan_transport' => 100000,
                'tunjangan_konsumsi' => 375000,
                'asuransi' => 0,
                'tarif' => 17000,
                'tarif_per_jam' => 17000,
                'deskripsi' => 'Tenaga kerja untuk proses pengemasan produk'
            ],
            
            // BTKTL (Biaya Tenaga Kerja Tidak Langsung)
            [
                'kode_jabatan' => 'BT004',
                'nama' => 'Supervisor',
                'kategori' => 'btktl',
                'gaji_pokok' => 4000000,
                'tunjangan' => 500000,
                'tunjangan_transport' => 300000,
                'tunjangan_konsumsi' => 300000,
                'asuransi' => 200000,
                'tarif' => 0,
                'tarif_per_jam' => 0,
                'deskripsi' => 'Pengawas dan koordinator produksi'
            ],
            [
                'kode_jabatan' => 'BT005',
                'nama' => 'Admin',
                'kategori' => 'btktl',
                'gaji_pokok' => 3000000,
                'tunjangan' => 500000,
                'tunjangan_transport' => 300000,
                'tunjangan_konsumsi' => 300000,
                'asuransi' => 200000,
                'tarif' => 0,
                'tarif_per_jam' => 0,
                'deskripsi' => 'Staff administrasi dan tata usaha'
            ],
            [
                'kode_jabatan' => 'BT006',
                'nama' => 'Kasir',
                'kategori' => 'btktl',
                'gaji_pokok' => 2700000,
                'tunjangan' => 0,
                'tunjangan_transport' => 300000,
                'tunjangan_konsumsi' => 300000,
                'asuransi' => 150000,
                'tarif' => 0,
                'tarif_per_jam' => 0,
                'deskripsi' => 'Petugas kasir dan transaksi'
            ],
            [
                'kode_jabatan' => 'BT007',
                'nama' => 'Quality Control',
                'kategori' => 'btktl',
                'gaji_pokok' => 3500000,
                'tunjangan' => 300000,
                'tunjangan_transport' => 300000,
                'tunjangan_konsumsi' => 300000,
                'asuransi' => 200000,
                'tarif' => 0,
                'tarif_per_jam' => 0,
                'deskripsi' => 'Petugas kontrol kualitas produk'
            ],
            [
                'kode_jabatan' => 'BT008',
                'nama' => 'Gudang',
                'kategori' => 'btktl',
                'gaji_pokok' => 2800000,
                'tunjangan' => 200000,
                'tunjangan_transport' => 250000,
                'tunjangan_konsumsi' => 300000,
                'asuransi' => 150000,
                'tarif' => 0,
                'tarif_per_jam' => 0,
                'deskripsi' => 'Petugas gudang dan inventory'
            ],
        ];

        foreach ($jabatans as $jabatan) {
            // Cek apakah sudah ada berdasarkan kode_jabatan atau nama
            $existing = Jabatan::where('kode_jabatan', $jabatan['kode_jabatan'])
                ->orWhere('nama', $jabatan['nama'])
                ->first();
            
            if (!$existing) {
                // Jika belum ada, buat baru
                Jabatan::create($jabatan);
            }
        }
    }
}
