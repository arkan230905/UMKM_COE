<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanBaku;
use App\Models\Satuan;

class AyamBahanBakuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get satuan IDs
        $kg = Satuan::where('nama', 'Kilogram')->first();
        $ekor = Satuan::where('nama', 'Ekor')->first();
        $gram = Satuan::where('nama', 'Gram')->first();
        $potong = Satuan::where('nama', 'Potong')->first();
        $ons = Satuan::where('nama', 'Ons')->first();

        // 1. Ayam Potong
        BahanBaku::create([
            'nama_bahan' => 'Ayam Potong',
            'kode_bahan' => 'BB-AYAM-POTONG',
            'satuan_id' => $kg->id,
            'satuan_dasar' => 'GRAM',
            'faktor_konversi' => 1000,
            'harga_satuan' => 32000.00,
            'harga_per_satuan_dasar' => 32.00, // Rp 32 per gram
            'harga_rata_rata' => 32000.00,
            'stok' => 50.00,
            'stok_minimum' => 10.00,
            'deskripsi' => 'Ayam potong berkualitas untuk produksi',
            
            // Sub Satuan 1: Gram (1 Kg = 1.000 Gram, Rp 32/gram)
            'sub_satuan_1_id' => $gram->id,
            'sub_satuan_1_konversi' => 1000,
            'sub_satuan_1_nilai' => 32.00,
            
            // Sub Satuan 2: Potong (1 Kg = 4 Potong, Rp 8.000/potong)
            'sub_satuan_2_id' => $potong->id,
            'sub_satuan_2_konversi' => 4,
            'sub_satuan_2_nilai' => 8000.00,
            
            // Sub Satuan 3: Ons (1 Kg = 10 Ons, Rp 3.200/ons)
            'sub_satuan_3_id' => $ons->id,
            'sub_satuan_3_konversi' => 10,
            'sub_satuan_3_nilai' => 3200.00,
        ]);

        // 2. Ayam Kampung
        BahanBaku::create([
            'nama_bahan' => 'Ayam Kampung',
            'kode_bahan' => 'BB-AYAM-KAMPUNG',
            'satuan_id' => $ekor->id,
            'satuan_dasar' => 'GRAM',
            'faktor_konversi' => 1500, // 1 ekor = 1.500 gram
            'harga_satuan' => 45000.00,
            'harga_per_satuan_dasar' => 30.00, // Rp 30 per gram
            'harga_rata_rata' => 45000.00,
            'stok' => 30.00,
            'stok_minimum' => 5.00,
            'deskripsi' => 'Ayam kampung berkualitas untuk produksi',
            
            // Sub Satuan 1: Potong (1 Ekor = 6 Potong, Rp 7.500/potong)
            'sub_satuan_1_id' => $potong->id,
            'sub_satuan_1_konversi' => 6,
            'sub_satuan_1_nilai' => 7500.00,
            
            // Sub Satuan 2: Kilogram (1 Ekor = 1,5 Kg, Rp 30.000/kg)
            'sub_satuan_2_id' => $kg->id,
            'sub_satuan_2_konversi' => 1.5,
            'sub_satuan_2_nilai' => 30000.00,
            
            // Sub Satuan 3: Gram (1 Ekor = 1.500 Gram, Rp 30/gram)
            'sub_satuan_3_id' => $gram->id,
            'sub_satuan_3_konversi' => 1500,
            'sub_satuan_3_nilai' => 30.00,
        ]);

        $this->command->info('Data Ayam Potong dan Ayam Kampung berhasil ditambahkan!');
    }
}
