<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddBebanLainnyaCoaSeeder extends Seeder
{
    /**
     * Add Beban Lainnya COA accounts for existing users
     */
    public function run(): void
    {
        $now = now();
        
        // Get all existing users
        $users = DB::table('users')->get();
        
        $bebanLainnyaCoas = [
            ['kode_akun' => '590', 'nama_akun' => 'Beban Administrasi Bank', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '591', 'nama_akun' => 'Beban Pajak', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '592', 'nama_akun' => 'Beban Denda', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '593', 'nama_akun' => 'Beban Kerugian', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '594', 'nama_akun' => 'Beban Lain-lain', 'tipe_akun' => 'Biaya', 'saldo_normal' => 'debit'],
        ];
        
        foreach ($users as $user) {
            // Check if user already has these COA accounts
            $existingCodes = DB::table('accounts')
                ->where('user_id', $user->id)
                ->whereIn('kode_akun', array_column($bebanLainnyaCoas, 'kode_akun'))
                ->pluck('kode_akun')
                ->toArray();
            
            // Add missing COA accounts
            foreach ($bebanLainnyaCoas as $coa) {
                if (!in_array($coa['kode_akun'], $existingCodes)) {
                    DB::table('accounts')->insert([
                        'user_id'            => $user->id,
                        'kode_akun'          => $coa['kode_akun'],
                        'nama_akun'          => $coa['nama_akun'],
                        'tipe_akun'          => $coa['tipe_akun'],
                        'kategori_akun'      => $coa['tipe_akun'],
                        'saldo_normal'       => $coa['saldo_normal'],
                        'saldo_awal'         => 0,
                        'tanggal_saldo_awal' => $now,
                        'posted_saldo_awal'  => 0,
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ]);
                }
            }
        }
        
        $this->command->info('Beban Lainnya COA accounts added successfully!');
    }
}
