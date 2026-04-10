<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Satuan;

class CustomSatuanSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Satuan::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $satuanData = [
            ['kode' => 'ONS', 'nama' => 'Ons'],
            ['kode' => 'KG', 'nama' => 'Kilogram'],
            ['kode' => 'ML', 'nama' => 'Mililiter'],
            ['kode' => 'G', 'nama' => 'Gram'],
            ['kode' => 'LTR', 'nama' => 'Liter'],
            ['kode' => 'PTG', 'nama' => 'Potong'],
            ['kode' => 'EKOR', 'nama' => 'Ekor'],
            ['kode' => 'SDT', 'nama' => 'Sendok Teh'],
            ['kode' => 'SDM', 'nama' => 'Sendok Makan'],
            ['kode' => 'PCS', 'nama' => 'Pieces'],
            ['kode' => 'BNGKS', 'nama' => 'Bungkus'],
            ['kode' => 'WATT', 'nama' => 'Watt'],
            ['kode' => 'GL', 'nama' => 'Galon'],
            ['kode' => 'TBG', 'nama' => 'Tabung'],
            ['kode' => 'SNG', 'nama' => 'Siung'],
        ];

        foreach ($satuanData as $satuan) {
            Satuan::updateOrCreate(
                ['kode' => $satuan['kode']],
                [
                    'nama' => $satuan['nama'],
                ]
            );
        }

        $this->command->info('Custom Satuan seeder completed successfully!');
        $this->command->info('Total units created: ' . count($satuanData));
    }
}
