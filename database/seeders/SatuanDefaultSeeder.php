<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatuanDefaultSeeder extends Seeder
{
    /**
     * Buat 16 Satuan default untuk user baru yang register.
     * Dipanggil dari CreateDefaultUserData listener.
     */
    public function run(int $userId): void
    {
        // Jangan buat ulang jika sudah ada
        if (DB::table('satuans')->where('user_id', $userId)->exists()) {
            return;
        }

        $now = now();

        // Default Satuan data
        $satuans = [
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
            ['kode' => 'CUP', 'nama' => 'Cup'],
            ['kode' => 'GL', 'nama' => 'Galon'],
            ['kode' => 'TBG', 'nama' => 'Tabung'],
            ['kode' => 'SNG', 'nama' => 'Siung'],
            ['kode' => 'KLG', 'nama' => 'Kaleng'],
        ];

        $rows = [];
        foreach ($satuans as $satuan) {
            $rows[] = [
                'user_id'    => $userId,
                'kode'       => $satuan['kode'],
                'nama'       => $satuan['nama'],
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('satuans')->insert($rows);
    }
}
