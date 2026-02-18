<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanPendukung;
use App\Models\BahanBaku;
use App\Models\Satuan;

class BahanPendukungLengkapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data yang salah dari bahan_baku (kecuali Ayam Potong dan Ayam Kampung)
        BahanBaku::whereNotIn('kode_bahan', ['BB-AYAM-POTONG', 'BB-AYAM-KAMPUNG'])->delete();
        
        $this->command->info('Data bahan baku yang salah telah dihapus.');
        
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

        $bahanPendukungData = [
            // 1. Air
            [
                'nama_bahan' => 'Air',
                'kode_bahan' => 'BP-AIR',
                'satuan_id' => $liter->id,
                'harga_satuan' => 1000,
                'stok' => 50,
                'stok_minimum' => 5,
                'deskripsi' => 'Air Mineral',
                'kategori' => 'air',
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
                'kode_bahan' => 'BP-MINYAK',
                'satuan_id' => $liter->id,
                'harga_satuan' => 14000,
                'stok' => 50,
                'stok_minimum' => 1,
                'deskripsi' => 'Minyak Goreng',
                'kategori' => 'minyak',
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
                'kode_bahan' => 'BP-GAS',
                'satuan_id' => $tabung->id,
                'harga_satuan' => 240000,
                'stok' => 50,
                'stok_minimum' => 1,
                'deskripsi' => 'Gas',
                'kategori' => 'gas',
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
                'kode_bahan' => 'BP-KETUMBAR',
                'satuan_id' => $bungkus->id,
                'harga_satuan' => 15000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'kategori' => 'bumbu',
                'sub_satuan_1_id' => $sdt->id,
                'sub_satuan_1_konversi' => 5,
                'sub_satuan_1_nilai' => 3000,
                'sub_satuan_2_id' => $kg->id,
                'sub_satuan_2_konversi' => 0.01,
                'sub_satuan_2_nilai' => 1500000,
                'sub_satuan_3_id' => $sdm->id,
                'sub_satuan_3_konversi' => 1.5,
                'sub_satuan_3_nilai' => 10000,
            ],
            // 5. Cabe Merah
            [
                'nama_bahan' => 'Cabe Merah',
                'kode_bahan' => 'BP-CABE-MERAH',
                'satuan_id' => $kg->id,
                'harga_satuan' => 100000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'kategori' => 'bumbu',
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
                'kode_bahan' => 'BP-CABE-HIJAU',
                'satuan_id' => $kg->id,
                'harga_satuan' => 120000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'kategori' => 'bumbu',
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
                'kode_bahan' => 'BP-LADA',
                'satuan_id' => $bungkus->id,
                'harga_satuan' => 15000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'kategori' => 'bumbu',
                'sub_satuan_1_id' => $sdt->id,
                'sub_satuan_1_konversi' => 5,
                'sub_satuan_1_nilai' => 3000,
                'sub_satuan_2_id' => $kg->id,
                'sub_satuan_2_konversi' => 0.01,
                'sub_satuan_2_nilai' => 1500000,
                'sub_satuan_3_id' => $sdm->id,
                'sub_satuan_3_konversi' => 1.5,
                'sub_satuan_3_nilai' => 10000,
            ],
            // 8. Bawang Putih
            [
                'nama_bahan' => 'Bawang Putih',
                'kode_bahan' => 'BP-BAWANG-PUTIH',
                'satuan_id' => $kg->id,
                'harga_satuan' => 28000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'kategori' => 'bumbu',
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
                'kode_bahan' => 'BP-MAIZENA',
                'satuan_id' => $bungkus->id,
                'harga_satuan' => 9000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'kategori' => 'bumbu',
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
                'kode_bahan' => 'BP-MERICA',
                'satuan_id' => $bungkus->id,
                'harga_satuan' => 2000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'kategori' => 'bumbu',
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
                'kode_bahan' => 'BP-LISTRIK',
                'satuan_id' => $watt->id,
                'harga_satuan' => 3000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Energi Listrik',
                'kategori' => 'listrik',
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
                'kode_bahan' => 'BP-BAWANG-MERAH',
                'satuan_id' => $kg->id,
                'harga_satuan' => 25000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Perbumbuan',
                'kategori' => 'bumbu',
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
                'kode_bahan' => 'BP-KEMASAN',
                'satuan_id' => $pcs->id,
                'harga_satuan' => 2000,
                'stok' => 50,
                'stok_minimum' => 0,
                'deskripsi' => 'Kemasan',
                'kategori' => 'lainnya',
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

        foreach ($bahanPendukungData as $data) {
            BahanPendukung::create($data);
        }

        $this->command->info('Data bahan pendukung lengkap berhasil ditambahkan!');
    }
}
