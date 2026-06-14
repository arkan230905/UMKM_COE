<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddBopGasCoaSeeder extends Seeder
{
    /**
     * Tambahkan akun BOP - Gas (kode 560) untuk semua user yang sudah ada.
     */
    public function run(): void
    {
        $now = now();

        $bopGas = [
            'kode_akun'    => '560',
            'nama_akun'    => 'BOP - Gas',
            'tipe_akun'    => 'Biaya',
            'saldo_normal' => 'debit',
        ];

        $users = DB::table('users')->get();
        $inserted = 0;
        $skipped  = 0;

        foreach ($users as $user) {
            // Cek apakah user sudah punya kode ini
            $exists = DB::table('coas')
                ->where('user_id', $user->id)
                ->where('kode_akun', $bopGas['kode_akun'])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            DB::table('coas')->insert([
                'user_id'            => $user->id,
                'kode_akun'          => $bopGas['kode_akun'],
                'nama_akun'          => $bopGas['nama_akun'],
                'tipe_akun'          => $bopGas['tipe_akun'],
                'kategori_akun'      => $bopGas['tipe_akun'],
                'saldo_normal'       => $bopGas['saldo_normal'],
                'saldo_awal'         => 0,
                'tanggal_saldo_awal' => $now,
                'posted_saldo_awal'  => 0,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

            $inserted++;
        }

        $this->command->info("BOP - Gas (kode 560) berhasil ditambahkan: {$inserted} user baru, {$skipped} user dilewati (sudah ada).");
    }
}
