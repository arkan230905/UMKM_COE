<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultCoaSeeder extends Seeder
{
    public function run(int $userId, int $companyId = null): void
    {
        $now = now();
        $coas = [
            ['kode' => '11',   'nama' => 'Aset', 'tipe' => 'Aset'],
            ['kode' => '115',  'nama' => 'Pers. Bahan Pendukung', 'tipe' => 'Aset'],
            // ... masukkan daftar 51 akun Anda di sini
        ];

        foreach ($coas as $coa) {
            DB::table('coas')->updateOrInsert(
                [
                    'company_id' => $companyId, 
                    'kode_akun'  => $coa['kode']
                ],
                [
                    'user_id'       => $userId,
                    'nama_akun'     => $coa['nama'],
                    'tipe_akun'     => $coa['tipe'],
                    'kategori_akun' => $coa['tipe'],
                    'saldo_normal'  => 'debit',
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]
            );
        }
    }
}