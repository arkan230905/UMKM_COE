<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JasukeCoaSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1; // Pastikan ID ini ada di tabel perusahaan
        $now = now();

        // Pastikan perusahaan dengan ID 1 ada
        $companyExists = DB::table('perusahaan')->where('id', $companyId)->exists();
        if (!$companyExists) {
            $this->command->warn('⚠️  Perusahaan dengan ID 1 tidak ditemukan. Seeder dilewati.');
            return;
        }

        $coas = [
            ['kode' => '11',   'nama' => 'Aset', 'tipe' => 'Aset'],
            ['kode' => '114',  'nama' => 'Pers. Bahan Baku', 'tipe' => 'Aset'],
            ['kode' => '115',  'nama' => 'Pers. Bahan Pendukung', 'tipe' => 'Aset'],
            ['kode' => '116',  'nama' => 'Pers. Barang Jadi', 'tipe' => 'Aset'],
            ['kode' => '51',   'nama' => 'BBB - Biaya Bahan Baku', 'tipe' => 'Biaya'],
            ['kode' => '52',   'nama' => 'BTKL', 'tipe' => 'Biaya'],
            ['kode' => '53',   'nama' => 'BOP', 'tipe' => 'Biaya'],
            // Tambahkan akun spesifik SIMACOST lainnya di sini
        ];

        foreach ($coas as $coa) {
            DB::table('coas')->updateOrInsert(
                ['kode_akun' => $coa['kode'], 'company_id' => $companyId],
                [
                    'nama_akun'     => $coa['nama'],
                    'tipe_akun'     => $coa['tipe'],
                    'kategori_akun' => $coa['tipe'],
                    'saldo_normal'  => 'debit',
                    'user_id'       => null, // Multi-tenant: bisa diisi dengan user_id jika perlu
                    'updated_at'    => $now,
                    'created_at'    => $now,
                ]
            );
        }
        $this->command->info('✅ JasukeCoaSeeder: Data berhasil disinkronkan.');
    }
}