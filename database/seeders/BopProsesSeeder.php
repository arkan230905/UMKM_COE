<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProsesProduksi;
use App\Models\KomponenBop;
use App\Models\ProsesBop;
use App\Models\Jabatan;

class BopProsesSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Pastikan ada jabatan untuk proses
        $jabatan = Jabatan::first();
        if (!$jabatan) {
            $jabatan = Jabatan::create([
                'kode_jabatan' => 'OP001',
                'nama' => 'Operator Produksi',
                'gaji_pokok' => 3000000,
                'tarif_per_jam' => 50000,
                'tunjangan' => 500000,
                'asuransi' => 200000,
                'deskripsi' => 'Operator untuk proses produksi'
            ]);
        }

        // 1. PROSES PENGGORENGAN
        $penggorengan = ProsesProduksi::updateOrCreate(
            ['nama_proses' => 'Penggorengan'],
            [
                'deskripsi' => 'Proses penggorengan produk',
                'tarif_btkl' => 50000,
                'satuan_btkl' => 'jam',
                'kapasitas_per_jam' => 50,
                'jabatan_id' => $jabatan->id
            ]
        );

        // Komponen BOP untuk Penggorengan
        $komponenPenggorengan = [
            ['nama' => 'Listrik Mesin', 'biaya_per_produk' => 208, 'keterangan' => 'Pemanas Minyak'],
            ['nama' => 'Gas / BBM', 'biaya_per_produk' => 114, 'keterangan' => 'Penjalanan Penggorengan'],
            ['nama' => 'Maintenace', 'biaya_per_produk' => 21, 'keterangan' => 'Mesin Goreng'],
            ['nama' => 'Penyusutan Mesin', 'biaya_per_produk' => 17, 'keterangan' => 'Rutin'],
            ['nama' => 'Air & Kebersihan', 'biaya_per_produk' => 21, 'keterangan' => 'Cuci alat']
        ];

        foreach ($komponenPenggorengan as $komponen) {
            $komponenBop = KomponenBop::updateOrCreate(
                ['nama_komponen' => $komponen['nama']],
                [
                    'satuan' => 'produk',
                    'tarif_per_satuan' => $komponen['biaya_per_produk'],
                    'is_active' => true
                ]
            );

            ProsesBop::updateOrCreate(
                [
                    'proses_produksi_id' => $penggorengan->id,
                    'komponen_bop_id' => $komponenBop->id
                ],
                [
                    'kuantitas_default' => 1
                ]
            );
        }

        // 2. PROSES PERBUMBUAN
        $perbumbuan = ProsesProduksi::updateOrCreate(
            ['nama_proses' => 'Perbumbuan'],
            [
                'deskripsi' => 'Proses perbumbuan produk',
                'tarif_btkl' => 48000,
                'satuan_btkl' => 'jam',
                'kapasitas_per_jam' => 200,
                'jabatan_id' => $jabatan->id
            ]
        );

        // Komponen BOP untuk Perbumbuan
        $komponenPerbumbuan = [
            ['nama' => 'Listrik Mixer', 'biaya_per_produk' => 42, 'keterangan' => 'Mesin Ringan'],
            ['nama' => 'Penyusutan Alat', 'biaya_per_produk' => 7, 'keterangan' => 'Drum / Mixer'],
            ['nama' => 'Maintenace Perbumbuan', 'biaya_per_produk' => 21, 'keterangan' => 'Rutin'],
            ['nama' => 'Kebersihan Perbumbuan', 'biaya_per_produk' => 21, 'keterangan' => 'Rutin']
        ];

        foreach ($komponenPerbumbuan as $komponen) {
            $komponenBop = KomponenBop::updateOrCreate(
                ['nama_komponen' => $komponen['nama']],
                [
                    'satuan' => 'produk',
                    'tarif_per_satuan' => $komponen['biaya_per_produk'],
                    'is_active' => true
                ]
            );

            ProsesBop::updateOrCreate(
                [
                    'proses_produksi_id' => $perbumbuan->id,
                    'komponen_bop_id' => $komponenBop->id
                ],
                [
                    'kuantitas_default' => 1
                ]
            );
        }

        // 3. PROSES PENGEMASAN
        $pengemasan = ProsesProduksi::updateOrCreate(
            ['nama_proses' => 'Pengemasan'],
            [
                'deskripsi' => 'Proses pengemasan produk',
                'tarif_btkl' => 45000,
                'satuan_btkl' => 'jam',
                'kapasitas_per_jam' => 50,
                'jabatan_id' => $jabatan->id
            ]
        );

        // Komponen BOP untuk Pengemasan
        $komponenPengemasan = [
            ['nama' => 'Listrik Pengemasan', 'biaya_per_produk' => 6, 'keterangan' => 'Listrik Mesin'],
            ['nama' => 'Penyusutan Alat Packing', 'biaya_per_produk' => 3, 'keterangan' => 'Alat Packing'],
            ['nama' => 'Kemasan', 'biaya_per_produk' => 200, 'keterangan' => 'Penunjang'],
            ['nama' => 'Kebersihan Area', 'biaya_per_produk' => 10, 'keterangan' => 'Area']
        ];

        foreach ($komponenPengemasan as $komponen) {
            $komponenBop = KomponenBop::updateOrCreate(
                ['nama_komponen' => $komponen['nama']],
                [
                    'satuan' => 'produk',
                    'tarif_per_satuan' => $komponen['biaya_per_produk'],
                    'is_active' => true
                ]
            );

            ProsesBop::updateOrCreate(
                [
                    'proses_produksi_id' => $pengemasan->id,
                    'komponen_bop_id' => $komponenBop->id
                ],
                [
                    'kuantitas_default' => 1
                ]
            );
        }

        $this->command->info('BOP Proses data berhasil dibuat:');
        $this->command->info('- Penggorengan: BTKL Rp 50.000/jam, Kapasitas 50 pcs/jam, Total BOP Rp 382/produk');
        $this->command->info('- Perbumbuan: BTKL Rp 48.000/jam, Kapasitas 200 pcs/jam, Total BOP Rp 90/jam');
        $this->command->info('- Pengemasan: BTKL Rp 45.000/jam, Kapasitas 50 pcs/jam, Total BOP Rp 220/jam');
        $this->command->info('Total Biaya Per Produk: Rp 2.832 (Penggorengan: Rp 1.382 + Perbumbuan: Rp 330.28 + Pengemasan: Rp 1.120)');
    }
}