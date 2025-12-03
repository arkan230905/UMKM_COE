<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Satuan;
use Illuminate\Support\Facades\DB;

class UpdateSatuanFaktorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key check
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Update data satuan yang sudah ada
        $satuans = [
            ['kode' => 'kg', 'nama' => 'Kilogram', 'faktor' => 1000],
            ['kode' => 'g', 'nama' => 'Gram', 'faktor' => 1],
            ['kode' => 'l', 'nama' => 'Liter', 'faktor' => 1000],
            ['kode' => 'ml', 'nama' => 'Mililiter', 'faktor' => 1],
            ['kode' => 'pcs', 'nama' => 'Buah', 'faktor' => 1],
            ['kode' => 'ons', 'nama' => 'Ons', 'faktor' => 100],
            ['kode' => 'bungkus', 'nama' => 'Bungkus', 'faktor' => 1],
        ];

        foreach ($satuans as $satuan) {
            // Cek apakah satuan sudah ada
            $existing = Satuan::where('kode', $satuan['kode'])->first();
            
            if ($existing) {
                // Update existing
                $existing->update([
                    'nama' => $satuan['nama'],
                    'faktor' => $satuan['faktor']
                ]);
            } else {
                // Buat baru jika belum ada
                Satuan::create($satuan);
            }
        }
        
        // Aktifkan kembali foreign key check
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
