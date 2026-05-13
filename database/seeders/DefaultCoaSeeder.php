<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultCoaSeeder extends Seeder
{
    public function run(int $userId): void
    {
        // Ambil company_id milik user tersebut (asumsi user punya company_id)
        $user = DB::table('users')->where('id', $userId)->first();
        $companyId = $user->company_id ?? 1;

        $now = now();
        $coas = [
            ['kode_akun' => '11',   'nama_akun' => 'Aset', 'tipe' => 'Aset', 'normal' => 'debit'],
            ['kode_akun' => '115',  'nama_akun' => 'Pers. Bahan Pendukung', 'tipe' => 'Aset', 'normal' => 'debit'],
            // ... (Daftar akun lainnya dari file asli Anda)
        ];

        foreach ($coas as $coa) {
            DB::table('coas')->updateOrInsert(
                [
                    'kode_akun'  => $coa['kode_akun'], 
                    'company_id' => $companyId
                ],
                [
                    'user_id'            => $userId,
                    'nama_akun'          => $coa['nama_akun'],
                    'tipe_akun'          => $coa['tipe_akun'],
                    'kategori_akun'      => $coa['tipe_akun'],
                    'saldo_normal'       => $coa['saldo_normal'],
                    'saldo_awal'         => 0,
                    'tanggal_saldo_awal' => $now,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]
            );
        }
    }
}