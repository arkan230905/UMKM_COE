<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanPendukung;
use App\Models\Satuan;

class BahanPendukungSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil satuan yang ada
        $kg = Satuan::where('kode', 'KG')->first();
        $liter = Satuan::where('kode', 'L')->orWhere('kode', 'LITER')->first();
        $gram = Satuan::where('kode', 'G')->orWhere('kode', 'GR')->first();
        $kwh = Satuan::where('kode', 'KWH')->first();
        
        // Jika satuan tidak ada, gunakan ID 1 sebagai default
        $kgId = $kg ? $kg->id : 1;
        $literId = $liter ? $liter->id : 1;
        $gramId = $gram ? $gram->id : 1;
        $kwhId = $kwh ? $kwh->id : 1;

        $bahanPendukungs = [
            [
                'nama_bahan' => 'Gas LPG',
                'kategori' => 'gas',
                'satuan_id' => $kgId,
                'harga_satuan' => 15000,
                'stok' => 50,
                'stok_minimum' => 10,
                'deskripsi' => 'Gas LPG untuk memasak',
            ],
            [
                'nama_bahan' => 'Minyak Goreng',
                'kategori' => 'minyak',
                'satuan_id' => $literId,
                'harga_satuan' => 18000,
                'stok' => 20,
                'stok_minimum' => 5,
                'deskripsi' => 'Minyak goreng untuk menggoreng',
            ],
            [
                'nama_bahan' => 'Garam',
                'kategori' => 'bumbu',
                'satuan_id' => $gramId,
                'harga_satuan' => 5,
                'stok' => 5000,
                'stok_minimum' => 1000,
                'deskripsi' => 'Garam dapur',
            ],
            [
                'nama_bahan' => 'Merica Bubuk',
                'kategori' => 'bumbu',
                'satuan_id' => $gramId,
                'harga_satuan' => 80,
                'stok' => 500,
                'stok_minimum' => 100,
                'deskripsi' => 'Merica bubuk untuk bumbu',
            ],
            [
                'nama_bahan' => 'Penyedap Rasa',
                'kategori' => 'bumbu',
                'satuan_id' => $gramId,
                'harga_satuan' => 20,
                'stok' => 2000,
                'stok_minimum' => 500,
                'deskripsi' => 'Penyedap rasa masakan',
            ],
            [
                'nama_bahan' => 'Sabun Cuci Piring',
                'kategori' => 'pembersih',
                'satuan_id' => $literId,
                'harga_satuan' => 12000,
                'stok' => 10,
                'stok_minimum' => 3,
                'deskripsi' => 'Sabun untuk mencuci piring',
            ],
            [
                'nama_bahan' => 'Deterjen',
                'kategori' => 'pembersih',
                'satuan_id' => $kgId,
                'harga_satuan' => 25000,
                'stok' => 5,
                'stok_minimum' => 2,
                'deskripsi' => 'Deterjen untuk mencuci',
            ],
            [
                'nama_bahan' => 'Air Bersih',
                'kategori' => 'air',
                'satuan_id' => $literId,
                'harga_satuan' => 50,
                'stok' => 1000,
                'stok_minimum' => 200,
                'deskripsi' => 'Air bersih untuk memasak',
            ],
            [
                'nama_bahan' => 'Listrik',
                'kategori' => 'listrik',
                'satuan_id' => $kwhId,
                'harga_satuan' => 1500,
                'stok' => 0,
                'stok_minimum' => 0,
                'deskripsi' => 'Biaya listrik per kWh',
            ],
        ];

        foreach ($bahanPendukungs as $bahan) {
            BahanPendukung::create($bahan);
        }

        $this->command->info('✅ Berhasil menambahkan ' . count($bahanPendukungs) . ' bahan pendukung');
    }
}
