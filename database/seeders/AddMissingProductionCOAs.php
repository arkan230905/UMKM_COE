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
                'jenis' => 'Aset Lancar',
                'kategori' => 'Persediaan',
                'posisi_normal' => 'debit',
                'parent_id' => null,
            ],
            [
                'kode_akun' => '1171',
                'nama_akun' => 'Pers. Barang Dalam Proses - BBB',
                'jenis' => 'Aset Lancar',
                'kategori' => 'Persediaan',
                'posisi_normal' => 'debit',
                'parent_id' => null,
            ],
            [
                'kode_akun' => '1172',
                'nama_akun' => 'Pers. Barang Dalam Proses - BTKL',
                'jenis' => 'Aset Lancar',
                'kategori' => 'Persediaan',
                'posisi_normal' => 'debit',
                'parent_id' => null,
            ],
            [
                'kode_akun' => '1173',
                'nama_akun' => 'Pers. Barang Dalam Proses - BOP',
                'jenis' => 'Aset Lancar',
                'kategori' => 'Persediaan',
                'posisi_normal' => 'debit',
                'parent_id' => null,
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
                    'jenis' => $coaData['jenis'],
                    'kategori' => $coaData['kategori'],
                    'posisi_normal' => $coaData['posisi_normal'],
                    'parent_id' => $coaData['parent_id'],
                    'saldo_awal' => 0,
                ]);

                $this->command->info("  ✅ Created: {$coaData['kode_akun']} - {$coaData['nama_akun']}");
            } else {
                $this->command->comment("  ⏭️  Exists: {$coaData['kode_akun']} - {$coaData['nama_akun']}");
            }
        }
    }
}
