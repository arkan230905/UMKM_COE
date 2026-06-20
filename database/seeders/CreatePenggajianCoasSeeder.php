<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coa;

class CreatePenggajianCoasSeeder extends Seeder
{
    public function run(): void
    {
        $accountsToCreate = [
            [
                'kode_akun' => '211',
                'nama_akun' => 'Hutang Gaji Pegawai',
                'tipe_akun' => '2',
                'kategori_akun' => 'Kewajiban',
                'saldo_normal' => 'kredit',
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '212',
                'nama_akun' => 'Hutang Gaji',
                'tipe_akun' => '2',
                'kategori_akun' => 'Kewajiban',
                'saldo_normal' => 'kredit',
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '213',
                'nama_akun' => 'Hutang Asuransi',
                'tipe_akun' => '2',
                'kategori_akun' => 'Kewajiban',
                'saldo_normal' => 'kredit',
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '214',
                'nama_akun' => 'PPN Keluaran',
                'tipe_akun' => '2',
                'kategori_akun' => 'Kewajiban',
                'saldo_normal' => 'kredit',
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '515',
                'nama_akun' => 'Beban Tunjangan',
                'tipe_akun' => '5',
                'kategori_akun' => 'Beban',
                'saldo_normal' => 'debit',
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '516',
                'nama_akun' => 'Beban Asuransi',
                'tipe_akun' => '5',
                'kategori_akun' => 'Beban',
                'saldo_normal' => 'debit',
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '517',
                'nama_akun' => 'Beban Bonus',
                'tipe_akun' => '5',
                'kategori_akun' => 'Beban',
                'saldo_normal' => 'debit',
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => '951',
                'nama_akun' => 'Selisih Pembulatan Penggajian',
                'tipe_akun' => '5',
                'kategori_akun' => 'Beban Lain-lain',
                'saldo_normal' => 'debit',
                'saldo_awal' => 0,
            ],
        ];

        foreach ($accountsToCreate as $account) {
            $existing = Coa::where('kode_akun', $account['kode_akun'])->first();
            if (!$existing) {
                Coa::create($account);
                $this->command->info("✓ COA created: {$account['kode_akun']} - {$account['nama_akun']}");
            } else {
                $this->command->warn("⚠ COA already exists: {$account['kode_akun']}");
            }
        }

        $this->command->info("\n✅ Semua COA untuk penggajian berhasil dibuat!");
    }
}
