<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JasukeCoaSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1; // Sesuaikan dengan ID dari CompanySeeder

        $coas = [
            ['kode_akun' => '11',   'nama_akun' => 'Aset', 'tipe' => 'Aset', 'normal' => 'debit'],
            ['kode_akun' => '114',  'nama_akun' => 'Pers. Bahan Baku', 'tipe' => 'Aset', 'normal' => 'debit'],
            ['kode_akun' => '115',  'nama_akun' => 'Pers. Bahan Pendukung', 'tipe' => 'Aset', 'normal' => 'debit'],
            ['kode_akun' => '116',  'nama_akun' => 'Pers. Barang Jadi', 'tipe' => 'Aset', 'normal' => 'debit'],
            ['kode_akun' => '51',   'nama_akun' => 'BBB - Biaya Bahan Baku', 'tipe' => 'Biaya', 'normal' => 'debit'],
            ['kode_akun' => '52',   'nama_akun' => 'BTKL', 'tipe' => 'Biaya', 'normal' => 'debit'],
            ['kode_akun' => '53',   'nama_akun' => 'BOP', 'tipe' => 'Biaya', 'normal' => 'debit'],
            // Tambahkan akun lainnya di sini...
        ];

        foreach ($coas as $coa) {
            DB::table('coas')->updateOrInsert(
                // Kunci pengecekan: Kode Akun unik per Perusahaan
                ['kode_akun' => $coa['kode_akun'], 'company_id' => $companyId],
                // Data yang diupdate/diinput
                [
                    'nama_akun'     => $coa['nama_akun'],
                    'tipe_akun'     => $coa['tipe'],
                    'kategori_akun' => $coa['tipe'],
                    'saldo_normal'  => $coa['normal'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]
            );
        }

        $this->command->info('✅ JasukeCoaSeeder berhasil diproses tanpa duplikat.');
    }
}