<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanPendukung;

class CustomBahanPendukungSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        BahanPendukung::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $bahanPendukungData = [
            [
                'id' => 13,
                'kode_bahan' => 'BPD-0001',
                'nama_bahan' => 'Air',
                'deskripsi' => 'Bahan Pendukung Air',
                'satuan_id' => 3, // ML
                'sub_satuan_1_id' => 8, // SDT
                'sub_satuan_1_konversi' => 1.0000,
                'sub_satuan_1_nilai' => 1.0000,
                'sub_satuan_2_id' => 9, // SDM
                'sub_satuan_2_konversi' => 1.0000,
                'sub_satuan_2_nilai' => 1000.0000,
                'sub_satuan_3_id' => 15, // SNG
                'sub_satuan_3_konversi' => 1.0000,
                'sub_satuan_3_nilai' => 0.0670,
                'coa_pembelian_id' => '531',
                'coa_persediaan_id' => '1150',
                'coa_hpp_id' => '531',
                'harga_satuan' => 1000.00,
                'stok' => 200.0000,
                'stok_minimum' => 5.0000,
                'kategori' => 'lainnya',
                'kategori_id' => 4, // Air
                'is_active' => 1,
                'created_at' => '2026-04-01 02:19:54',
                'updated_at' => '2026-04-07 17:18:47'
            ],
            [
                'id' => 14,
                'kode_bahan' => 'BPD-0002',
                'nama_bahan' => 'Minyak Goreng',
                'deskripsi' => 'Minyak Goreng',
                'satuan_id' => 3, // ML
                'sub_satuan_1_id' => 8, // SDT
                'sub_satuan_1_konversi' => 1.0000,
                'sub_satuan_1_nilai' => 1.0000,
                'sub_satuan_2_id' => 9, // SDM
                'sub_satuan_2_konversi' => 1.0000,
                'sub_satuan_2_nilai' => 1000.0000,
                'sub_satuan_3_id' => 10, // PCS
                'sub_satuan_3_konversi' => 1.0000,
                'sub_satuan_3_nilai' => 1000.0000,
                'coa_pembelian_id' => '532',
                'coa_persediaan_id' => '1151',
                'coa_hpp_id' => '532',
                'harga_satuan' => 14000.00,
                'stok' => 200.0000,
                'stok_minimum' => 5.0000,
                'kategori' => 'lainnya',
                'kategori_id' => 3, // Minyak
                'is_active' => 1,
                'created_at' => '2026-04-01 02:22:09',
                'updated_at' => '2026-04-07 18:06:24'
            ],
            [
                'id' => 15,
                'kode_bahan' => 'BPD-0003',
                'nama_bahan' => 'Gas 30 Kg',
                'deskripsi' => 'Gas',
                'satuan_id' => 14, // TBG
                'sub_satuan_1_id' => 8, // SDT
                'sub_satuan_1_konversi' => 1.0000,
                'sub_satuan_1_nilai' => 30.0000,
                'sub_satuan_2_id' => 10, // PCS
                'sub_satuan_2_konversi' => 1.0000,
                'sub_satuan_2_nilai' => 30000.0000,
                'sub_satuan_3_id' => 10, // PCS
                'sub_satuan_3_konversi' => 1.0000,
                'sub_satuan_3_nilai' => 30000.0000,
                'coa_pembelian_id' => '533',
                'coa_persediaan_id' => '1152',
                'coa_hpp_id' => '533',
                'harga_satuan' => 240000.00,
                'stok' => 200.0000,
                'stok_minimum' => 5.0000,
                'kategori' => 'lainnya',
                'kategori_id' => 1, // Gas
                'is_active' => 1,
                'created_at' => '2026-04-01 02:24:34',
                'updated_at' => '2026-04-07 17:21:44'
            ],
            [
                'id' => 16,
                'kode_bahan' => 'BPD-0004',
                'nama_bahan' => 'Tepung Terigu',
                'deskripsi' => 'Perbumbuan',
                'satuan_id' => 4, // G
                'sub_satuan_1_id' => 24,
                'sub_satuan_1_konversi' => 1.0000,
                'sub_satuan_1_nilai' => 12500.0000,
                'sub_satuan_2_id' => 20,
                'sub_satuan_2_konversi' => 1.0000,
                'sub_satuan_2_nilai' => 500.0000,
                'sub_satuan_3_id' => 25,
                'sub_satuan_3_konversi' => 1.0000,
                'sub_satuan_3_nilai' => 4.7000,
                'coa_pembelian_id' => '534',
                'coa_persediaan_id' => '1153',
                'coa_hpp_id' => '534',
                'harga_satuan' => 50000.00,
                'stok' => 200.0000,
                'stok_minimum' => 5.0000,
                'kategori' => 'lainnya',
                'kategori_id' => 2, // Bumbu
                'is_active' => 1,
                'created_at' => '2026-04-01 02:27:00',
                'updated_at' => '2026-04-07 17:57:21'
            ],
            [
                'id' => 17,
                'kode_bahan' => 'BPD-0005',
                'nama_bahan' => 'Tepung Maizena',
                'deskripsi' => null,
                'satuan_id' => 4, // G
                'sub_satuan_1_id' => 24,
                'sub_satuan_1_konversi' => 1.0000,
                'sub_satuan_1_nilai' => 12500.0000,
                'sub_satuan_2_id' => 20,
                'sub_satuan_2_konversi' => 1.0000,
                'sub_satuan_2_nilai' => 500.0000,
                'sub_satuan_3_id' => 25,
                'sub_satuan_3_konversi' => 1.0000,
                'sub_satuan_3_nilai' => 4.7000,
                'coa_pembelian_id' => '535',
                'coa_persediaan_id' => '1154',
                'coa_hpp_id' => '535',
                'harga_satuan' => 50000.00,
                'stok' => 200.0000,
                'stok_minimum' => 5.0000,
                'kategori' => 'lainnya',
                'kategori_id' => 2, // Bumbu
                'is_active' => 1,
                'created_at' => '2026-04-01 02:38:31',
                'updated_at' => '2026-04-07 17:57:21'
            ],
            [
                'id' => 18,
                'kode_bahan' => 'BPD-0006',
                'nama_bahan' => 'Lada',
                'deskripsi' => 'Perbumbuan',
                'satuan_id' => 4, // G
                'sub_satuan_1_id' => 9, // SDM
                'sub_satuan_1_konversi' => 1.0000,
                'sub_satuan_1_nilai' => 5.0000,
                'sub_satuan_2_id' => 8, // SDT
                'sub_satuan_2_konversi' => 1.0000,
                'sub_satuan_2_nilai' => 0.0100,
                'sub_satuan_3_id' => 15, // SNG
                'sub_satuan_3_konversi' => 1.0000,
                'sub_satuan_3_nilai' => 1.5000,
                'coa_pembelian_id' => '536',
                'coa_persediaan_id' => '1155',
                'coa_hpp_id' => '536',
                'harga_satuan' => 15000.00,
                'stok' => 200.0000,
                'stok_minimum' => 5.0000,
                'kategori' => 'lainnya',
                'kategori_id' => 2, // Bumbu
                'is_active' => 1,
                'created_at' => '2026-04-01 02:39:52',
                'updated_at' => '2026-04-07 17:54:40'
            ],
            [
                'id' => 19,
                'kode_bahan' => 'BPD-0007',
                'nama_bahan' => 'Bubuk Kaldu Ayam',
                'deskripsi' => 'Perbumbuan',
                'satuan_id' => 18,
                'sub_satuan_1_id' => 20,
                'sub_satuan_1_konversi' => 1.0000,
                'sub_satuan_1_nilai' => 1000.0000,
                'sub_satuan_2_id' => 25,
                'sub_satuan_2_konversi' => 1.0000,
                'sub_satuan_2_nilai' => 70.0000,
                'sub_satuan_3_id' => 24,
                'sub_satuan_3_konversi' => 1.0000,
                'sub_satuan_3_nilai' => 200.0000,
                'coa_pembelian_id' => '537',
                'coa_persediaan_id' => '1156',
                'coa_hpp_id' => '537',
                'harga_satuan' => 40000.00,
                'stok' => 200.0000,
                'stok_minimum' => 5.0000,
                'kategori' => 'lainnya',
                'kategori_id' => 2, // Bumbu
                'is_active' => 1,
                'created_at' => '2026-04-01 02:44:13',
                'updated_at' => '2026-04-07 17:21:00'
            ],
            [
                'id' => 20,
                'kode_bahan' => 'BPD-0008',
                'nama_bahan' => 'Listrik',
                'deskripsi' => 'Listrik',
                'satuan_id' => 12, // WATT
                'sub_satuan_1_id' => 12, // WATT
                'sub_satuan_1_konversi' => 1.0000,
                'sub_satuan_1_nilai' => 1.0000,
                'sub_satuan_2_id' => 12, // WATT
                'sub_satuan_2_konversi' => 1.0000,
                'sub_satuan_2_nilai' => 1.0000,
                'sub_satuan_3_id' => 12, // WATT
                'sub_satuan_3_konversi' => 1.0000,
                'sub_satuan_3_nilai' => 1.0000,
                'coa_pembelian_id' => '538',
                'coa_persediaan_id' => '1157',
                'coa_hpp_id' => '538',
                'harga_satuan' => 3000.00,
                'stok' => 200.0000,
                'stok_minimum' => 5.0000,
                'kategori' => 'lainnya',
                'kategori_id' => 5, // Listrik
                'is_active' => 1,
                'created_at' => '2026-04-01 02:45:16',
                'updated_at' => '2026-04-07 17:21:44'
            ],
            [
                'id' => 21,
                'kode_bahan' => 'BPD-0009',
                'nama_bahan' => 'Bubuk Bawang Putih',
                'deskripsi' => 'Perbumbuan',
                'satuan_id' => 18,
                'sub_satuan_1_id' => 20,
                'sub_satuan_1_konversi' => 1.0000,
                'sub_satuan_1_nilai' => 1000.0000,
                'sub_satuan_2_id' => 25,
                'sub_satuan_2_konversi' => 1.0000,
                'sub_satuan_2_nilai' => 70.0000,
                'sub_satuan_3_id' => 24,
                'sub_satuan_3_konversi' => 1.0000,
                'sub_satuan_3_nilai' => 200.0000,
                'coa_pembelian_id' => '539',
                'coa_persediaan_id' => '1158',
                'coa_hpp_id' => '539',
                'harga_satuan' => 62000.00,
                'stok' => 200.2000,
                'stok_minimum' => 5.0000,
                'kategori' => 'lainnya',
                'kategori_id' => 2, // Bumbu
                'is_active' => 1,
                'created_at' => '2026-04-01 02:46:53',
                'updated_at' => '2026-04-07 18:06:24'
            ],
            [
                'id' => 22,
                'kode_bahan' => 'BPD-0010',
                'nama_bahan' => 'Kemasan',
                'deskripsi' => null,
                'satuan_id' => 11, // BNGKS
                'sub_satuan_1_id' => 11, // BNGKS
                'sub_satuan_1_konversi' => 1.0000,
                'sub_satuan_1_nilai' => 1.0000,
                'sub_satuan_2_id' => 11, // BNGKS
                'sub_satuan_2_konversi' => 1.0000,
                'sub_satuan_2_nilai' => 1.0000,
                'sub_satuan_3_id' => 11, // BNGKS
                'sub_satuan_3_konversi' => 1.0000,
                'sub_satuan_3_nilai' => 1.0000,
                'coa_pembelian_id' => '5399',
                'coa_persediaan_id' => '1159',
                'coa_hpp_id' => '5399',
                'harga_satuan' => 2000.00,
                'stok' => 200.0000,
                'stok_minimum' => 5.0000,
                'kategori' => 'lainnya',
                'kategori_id' => 7, // Lainnya
                'is_active' => 1,
                'created_at' => '2026-04-01 02:56:55',
                'updated_at' => '2026-04-07 18:06:24'
            ],
            [
                'id' => 23,
                'kode_bahan' => 'BPD-0011',
                'nama_bahan' => 'Cabe Merah',
                'deskripsi' => 'Perbumbuan',
                'satuan_id' => 8, // SDT
                'sub_satuan_1_id' => 10, // PCS
                'sub_satuan_1_konversi' => 1.0000,
                'sub_satuan_1_nilai' => 1000.0000,
                'sub_satuan_2_id' => 6, // PTG
                'sub_satuan_2_konversi' => 1.0000,
                'sub_satuan_2_nilai' => 10.0000,
                'sub_satuan_3_id' => 5, // LTR
                'sub_satuan_3_konversi' => 1.0000,
                'sub_satuan_3_nilai' => 1.0000,
                'coa_pembelian_id' => '53991',
                'coa_persediaan_id' => '11591',
                'coa_hpp_id' => '53991',
                'harga_satuan' => 120000.00,
                'stok' => 200.0000,
                'stok_minimum' => 5.0000,
                'kategori' => 'lainnya',
                'kategori_id' => 2, // Bumbu
                'is_active' => 1,
                'created_at' => '2026-04-01 03:02:12',
                'updated_at' => '2026-04-07 17:21:44'
            ]
        ];

        foreach ($bahanPendukungData as $bahan) {
            BahanPendukung::updateOrCreate(
                ['id' => $bahan['id']],
                $bahan
            );
        }

        $this->command->info('Custom Bahan Pendukung seeder completed successfully!');
        $this->command->info('Total bahan pendukung created: ' . count($bahanPendukungData));
    }
}
