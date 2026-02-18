<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanBaku;
use App\Models\Satuan;

class BahanBakuLengkapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get satuan IDs
        $liter = Satuan::where('nama', 'Liter')->first();
        $kg = Satuan::where('nama', 'Kilogram')->first();
        $gram = Satuan::where('nama', 'Gram')->first();
        $ml = Satuan::where('nama', 'Mililiter')->first();
        $tabung = Satuan::firstOrCreate(['nama' => 'Tabung', 'kode' => 'TABUNG', 'kategori' => 'jumlah']);
        $galon = Satuan::firstOrCreate(['nama' => 'Galon', 'kode' => 'GALON', 'kategori' => 'jumlah']);
        $bungkus = Satuan::where('nama', 'Bungkus')->first();
        $sdt = Satuan::where('nama', 'Sendok Teh')->first();
        $sdm = Satuan::where('nama', 'Sendok Makan')->first();
        $ons = Satuan::where('nama', 'Ons')->first();
        $siung = Satuan::where('nama', 'Siung')->first();
        $pcs = Satuan::where('nama', 'Pieces')->first();
        $watt = Satuan::firstOrCreate(['nama' => 'Watt', 'kode' => 'WATT', 'kategori' => 'jumlah']);

        $bahanBakuData = [
            // 1. Air
            [
                'nama_bahan' => 'Air',
                'kode_bahan' => 'BB-AIR',
                'satuan_id' => $liter->id,
                'harga_satuan' => 1000,
                'stok' => 50,
                'stok_minimum' => 5,
                'deskripsi' => 'Air Mineral',
                'sub_satuan_1_id' => $kg->id,
                'sub_satuan_1_konversi' => 1,
                'sub_satuan_1_nilai' => 1000,
                'sub_satuan_2_id' => $ml->id,
                'sub_satuan_2_konversi' => 1000,
                'sub_satuan_2_nilai' => 1,
                'sub_satuan_3_id' => $galon->id,
                'sub_satuan_3_konversi' => 0.067,
                'sub_satuan_3_nilai' => 15000,
            ],
            // 2. Minyak Goreng
            [
                'nama_bahan' => 'Minyak Goreng',
                'kode_bahan' => 'BB-MINYAK',
                'satuan_id' => $liter->id,
                'harga_satuan' => 14000,
                'stok' => 50,
                'stok_minimum' => 1,
                'deskripsi' => 'Minyak Goreng',
                'sub_satuan_1_id' => $kg->id,
                'sub_satuan_1_konversi' => 1,
                'sub_satuan_1_nilai' => 14000,
                'sub_satuan_2_id' => $ml->id,
                'sub_satuan_2_konversi' => 1000,
                'sub_satuan_2_nilai' => 14,
                'sub_satuan_3_id' => $gram->id,
                'sub_satuan_3_konversi' => 1000,
                'sub_satuan_3_nilai' => 14,
            ],
            // 3. Gas 30 Kg
            [
                'nama_bahan' => 'Gas 30 Kg',
                'kode_bahan' => 'BB-GAS',
                'satuan_id' => $tabung->id,
                'harga_satuan' => 240000,
                'stok' => 50,
                'stok_minimum' => 1,
                'deskripsi' => 'Gas',
                'sub_satuan_1_id' => $kg->id,
                'sub_satuan_1_konversi' => 30,
                'sub_satuan_1_nilai' => 8000,
                'sub_satuan_2_id' => $gram->id,
                'sub_satuan_2_konversi' => 30000,
                'sub_satuan_2_nilai' => 8,
                'sub_satuan_3_id' => $gram->id,
                'sub_satuan_3_konversi' => 30000,
                'sub_satuan_3_nilai' => 8,
            ],
            // 4. Ketumbar Bubuk
            [
                'nama_bahan' => 'Ketumbar Bubuk',
                'kode_bahan' => 'BB-KETUMBAR',
                'satuan_id' => $bungkus->id,
                'harga_satuan' => 15000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'sub_satuan_1_id' => $sdt->id,
                'sub_satuan_1_konversi' => 5,
                'sub_satuan_1_nilai' => 3000,
                'sub_satuan_2_id' => $kg->id,
                'sub_satuan_2_konversi' => 0.001,
                'sub_satuan_2_nilai' => 1500000,
                'sub_satuan_3_id' => $sdm->id,
                'sub_satuan_3_konversi' => 1.5,
                'sub_satuan_3_nilai' => 9000,
            ],
            // 5. Cabe Merah
            [
                'nama_bahan' => 'Cabe Merah',
                'kode_bahan' => 'BB-CABE-MERAH',
                'satuan_id' => $kg->id,
                'harga_satuan' => 100000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'sub_satuan_1_id' => $gram->id,
                'sub_satuan_1_konversi' => 1000,
                'sub_satuan_1_nilai' => 100,
                'sub_satuan_2_id' => $ons->id,
                'sub_satuan_2_konversi' => 10,
                'sub_satuan_2_nilai' => 10000,
                'sub_satuan_3_id' => $ons->id,
                'sub_satuan_3_konversi' => 10,
                'sub_satuan_3_nilai' => 10000,
            ],
            // 6. Cabe Hijau
            [
                'nama_bahan' => 'Cabe Hijau',
                'kode_bahan' => 'BB-CABE-HIJAU',
                'satuan_id' => $kg->id,
                'harga_satuan' => 120000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'sub_satuan_1_id' => $gram->id,
                'sub_satuan_1_konversi' => 1000,
                'sub_satuan_1_nilai' => 120,
                'sub_satuan_2_id' => $ons->id,
                'sub_satuan_2_konversi' => 10,
                'sub_satuan_2_nilai' => 12000,
                'sub_satuan_3_id' => $ons->id,
                'sub_satuan_3_konversi' => 10,
                'sub_satuan_3_nilai' => 12000,
            ],
            // 7. Lada Hitam
            [
                'nama_bahan' => 'Lada Hitam',
                'kode_bahan' => 'BB-LADA',
                'satuan_id' => $bungkus->id,
                'harga_satuan' => 15000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'sub_satuan_1_id' => $sdt->id,
                'sub_satuan_1_konversi' => 5,
                'sub_satuan_1_nilai' => 3000,
                'sub_satuan_2_id' => $kg->id,
                'sub_satuan_2_konversi' => 0.01,
                'sub_satuan_2_nilai' => 1500000,
                'sub_satuan_3_id' => $sdm->id,
                'sub_satuan_3_konversi' => 1.5,
                'sub_satuan_3_nilai' => 9000,
            ],
            // 8. Bawang Putih
            [
                'nama_bahan' => 'Bawang Putih',
                'kode_bahan' => 'BB-BAWANG-PUTIH',
                'satuan_id' => $kg->id,
                'harga_satuan' => 28000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'sub_satuan_1_id' => $gram->id,
                'sub_satuan_1_konversi' => 1000,
                'sub_satuan_1_nilai' => 28,
                'sub_satuan_2_id' => $ons->id,
                'sub_satuan_2_konversi' => 10,
                'sub_satuan_2_nilai' => 2800,
                'sub_satuan_3_id' => $siung->id,
                'sub_satuan_3_konversi' => 200,
                'sub_satuan_3_nilai' => 140,
            ],
            // 9. Tepung Maizena
            [
                'nama_bahan' => 'Tepung Maizena',
                'kode_bahan' => 'BB-MAIZENA',
                'satuan_id' => $bungkus->id,
                'harga_satuan' => 9000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'sub_satuan_1_id' => $gram->id,
                'sub_satuan_1_konversi' => 100,
                'sub_satuan_1_nilai' => 90,
                'sub_satuan_2_id' => $sdm->id,
                'sub_satuan_2_konversi' => 10,
                'sub_satuan_2_nilai' => 900,
                'sub_satuan_3_id' => $sdt->id,
                'sub_satuan_3_konversi' => 33.3,
                'sub_satuan_3_nilai' => 270,
            ],
            // 10. Merica Bubuk
            [
                'nama_bahan' => 'Merica Bubuk',
                'kode_bahan' => 'BB-MERICA',
                'satuan_id' => $bungkus->id,
                'harga_satuan' => 2000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'sub_satuan_1_id' => $gram->id,
                'sub_satuan_1_konversi' => 25,
                'sub_satuan_1_nilai' => 80,
                'sub_satuan_2_id' => $sdm->id,
                'sub_satuan_2_konversi' => 2.5,
                'sub_satuan_2_nilai' => 800,
                'sub_satuan_3_id' => $sdt->id,
                'sub_satuan_3_konversi' => 8.3,
                'sub_satuan_3_nilai' => 240,
            ],
            // 11. Listrik
            [
                'nama_bahan' => 'Listrik',
                'kode_bahan' => 'BB-LISTRIK',
                'satuan_id' => $watt->id,
                'harga_satuan' => 3000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Energi Listrik',
                'sub_satuan_1_id' => $watt->id,
                'sub_satuan_1_konversi' => 1,
                'sub_satuan_1_nilai' => 3000,
                'sub_satuan_2_id' => $watt->id,
                'sub_satuan_2_konversi' => 1,
                'sub_satuan_2_nilai' => 3000,
                'sub_satuan_3_id' => $watt->id,
                'sub_satuan_3_konversi' => 1,
                'sub_satuan_3_nilai' => 3000,
            ],
            // 12. Bawang Merah
            [
                'nama_bahan' => 'Bawang Merah',
                'kode_bahan' => 'BB-BAWANG-MERAH',
                'satuan_id' => $kg->id,
                'harga_satuan' => 25000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'sub_satuan_1_id' => $gram->id,
                'sub_satuan_1_konversi' => 1000,
                'sub_satuan_1_nilai' => 25,
                'sub_satuan_2_id' => $ons->id,
                'sub_satuan_2_konversi' => 10,
                'sub_satuan_2_nilai' => 2500,
                'sub_satuan_3_id' => $siung->id,
                'sub_satuan_3_konversi' => 250,
                'sub_satuan_3_nilai' => 100,
            ],
            // 13. Kemasan
            [
                'nama_bahan' => 'Kemasan',
                'kode_bahan' => 'BB-KEMASAN',
                'satuan_id' => $pcs->id,
                'harga_satuan' => 2000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Kemasan',
                'sub_satuan_1_id' => $pcs->id,
                'sub_satuan_1_konversi' => 1,
                'sub_satuan_1_nilai' => 2000,
                'sub_satuan_2_id' => $pcs->id,
                'sub_satuan_2_konversi' => 1,
                'sub_satuan_2_nilai' => 2000,
                'sub_satuan_3_id' => $pcs->id,
                'sub_satuan_3_konversi' => 1,
                'sub_satuan_3_nilai' => 2000,
            ],
        ];

        foreach ($bahanBakuData as $data) {
            // Set harga rata-rata sama dengan harga satuan
            $data['harga_rata_rata'] = $data['harga_satuan'];
            
            // Set satuan dasar dan faktor konversi
            $data['satuan_dasar'] = 'GRAM';
            $data['faktor_konversi'] = 1000;
            
            // Calculate harga per satuan dasar
            $data['harga_per_satuan_dasar'] = $data['harga_satuan'] / 1000;
            
            BahanBaku::create($data);
        }

        $this->command->info('Data bahan baku lengkap berhasil ditambahkan!');
    }
}
