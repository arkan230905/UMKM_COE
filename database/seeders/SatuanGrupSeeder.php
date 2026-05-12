<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SatuanGrup;

class SatuanGrupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $satuanGrupTemplate = [
            ['kode' => 'ONS', 'nama' => 'Ons', 'keterangan' => 'Satuan berat ons'],
            ['kode' => 'KG', 'nama' => 'Kilogram', 'keterangan' => 'Satuan berat kilogram'],
            ['kode' => 'ML', 'nama' => 'Mililiter', 'keterangan' => 'Satuan volume mililiter'],
            ['kode' => 'G', 'nama' => 'Gram', 'keterangan' => 'Satuan berat gram'],
            ['kode' => 'LTR', 'nama' => 'Liter', 'keterangan' => 'Satuan volume liter'],
            ['kode' => 'PTG', 'nama' => 'Potong', 'keterangan' => 'Satuan potong'],
            ['kode' => 'EKOR', 'nama' => 'Ekor', 'keterangan' => 'Satuan ekor untuk hewan'],
            ['kode' => 'SDT', 'nama' => 'Sendok Teh', 'keterangan' => 'Satuan volume sendok teh'],
            ['kode' => 'SDM', 'nama' => 'Sendok Makan', 'keterangan' => 'Satuan volume sendok makan'],
            ['kode' => 'PCS', 'nama' => 'Pieces', 'keterangan' => 'Satuan pieces/pieces'],
            ['kode' => 'BNGKS', 'nama' => 'Bungkus', 'keterangan' => 'Satuan bungkus'],
            ['kode' => 'WATT', 'nama' => 'Watt', 'keterangan' => 'Satuan daya watt'],
            ['kode' => 'GL', 'nama' => 'Galon', 'keterangan' => 'Satuan volume galon'],
            ['kode' => 'TBG', 'nama' => 'Tabung', 'keterangan' => 'Satuan tabung'],
            ['kode' => 'SNG', 'nama' => 'Siung', 'keterangan' => 'Satuan siung untuk bumbu'],
        ];

        foreach ($satuanGrupTemplate as $grup) {
            SatuanGrup::updateOrCreate(
                ['kode' => $grup['kode']],
                [
                    'nama' => $grup['nama'],
                    'keterangan' => $grup['keterangan'],
                ]
            );
        }

        $this->command->info('Satuan Grup Template berhasil di-seed! Total: ' . count($satuanGrupTemplate) . ' satuan grup.');
    }
}
