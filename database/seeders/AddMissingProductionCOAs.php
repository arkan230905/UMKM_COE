<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class AddMissingProductionCOAs extends Seeder
{
    public function run()
    {
        $this->command->info('Adding missing production COAs for all users...');

        // Get all users who have COAs but missing production COAs
        $users = DB::table('coas')
            ->select('user_id')
            ->distinct()
            ->pluck('user_id');

        foreach ($users as $userId) {
            $this->addMissingCOAsForUser($userId);
        }

        $this->command->info('Done!');
    }

    private function addMissingCOAsForUser($userId)
    {
        $this->command->line("Processing user ID: {$userId}");

        // Define required COAs for production
        $requiredCOAs = [
            [
                'kode_akun' => '1142',
                'nama_akun' => 'Persediaan Bahan Pendukung',
                'tipe_akun' => 'Aset',
                'kategori_akun' => 'Aset Lancar',
                'saldo_normal' => 'debit',
                'kode_induk' => '114',
            ],
            [
                'kode_akun' => '1171',
                'nama_akun' => 'Pers. Barang Dalam Proses - BBB',
                'tipe_akun' => 'Aset',
                'kategori_akun' => 'Aset Lancar',
                'saldo_normal' => 'debit',
                'kode_induk' => '117',
            ],
            [
                'kode_akun' => '1172',
                'nama_akun' => 'Pers. Barang Dalam Proses - BTKL',
                'tipe_akun' => 'Aset',
                'kategori_akun' => 'Aset Lancar',
                'saldo_normal' => 'debit',
                'kode_induk' => '117',
            ],
            [
                'kode_akun' => '1173',
                'nama_akun' => 'Pers. Barang Dalam Proses - BOP',
                'tipe_akun' => 'Aset',
                'kategori_akun' => 'Aset Lancar',
                'saldo_normal' => 'debit',
                'kode_induk' => '117',
            ],
        ];

        foreach ($requiredCOAs as $coaData) {
            // Check if COA already exists
            $exists = Coa::where('kode_akun', $coaData['kode_akun'])
                ->where('user_id', $userId)
                ->exists();

            if (!$exists) {
                Coa::create([
                    'user_id' => $userId,
                    'kode_akun' => $coaData['kode_akun'],
                    'nama_akun' => $coaData['nama_akun'],
                    'tipe_akun' => $coaData['tipe_akun'],
                    'kategori_akun' => $coaData['kategori_akun'],
                    'saldo_normal' => $coaData['saldo_normal'],
                    'kode_induk' => $coaData['kode_induk'],
                    'saldo_awal' => 0,
                    'tanggal_saldo_awal' => now()->format('Y-m-d'),
                    'posted_saldo_awal' => 0,
                    'is_akun_header' => 0,
                ]);

                $this->command->info("  ✅ Created: {$coaData['kode_akun']} - {$coaData['nama_akun']}");
            } else {
                $this->command->comment("  ⏭️  Exists: {$coaData['kode_akun']} - {$coaData['nama_akun']}");
            }
        }
    }
}
