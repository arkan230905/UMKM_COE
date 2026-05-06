<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class RequiredProductionCoasSeeder extends Seeder
{
    /**
     * Seed required COAs for production process
     * This ensures all necessary accounts exist before production can be processed
     */
    public function run(): void
    {
        // Get all users
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            $this->seedProductionCoasForUser($user->id);
        }
    }
    
    private function seedProductionCoasForUser($userId)
    {
        echo "Seeding production COAs for user ID {$userId}...\n";
        
        $requiredCoas = [
            // Work in Process (WIP) Accounts
            [
                'kode_akun' => '1171',
                'nama_akun' => 'Pers. Barang Dalam Proses - BBB',
                'tipe_akun' => 'Aset',
                'kategori_akun' => 'Aset',
                'saldo_normal' => 'debit',
                'keterangan' => 'Work in Process - Bahan Baku',
            ],
            [
                'kode_akun' => '1172',
                'nama_akun' => 'Pers. Barang Dalam Proses - BTKL',
                'tipe_akun' => 'Aset',
                'kategori_akun' => 'Aset',
                'saldo_normal' => 'debit',
                'keterangan' => 'Work in Process - Biaya Tenaga Kerja Langsung',
            ],
            [
                'kode_akun' => '1173',
                'nama_akun' => 'Pers. Barang Dalam Proses - BOP',
                'tipe_akun' => 'Aset',
                'kategori_akun' => 'Aset',
                'saldo_normal' => 'debit',
                'keterangan' => 'Work in Process - Biaya Overhead Pabrik',
            ],
            
            // Liability Accounts
            [
                'kode_akun' => '211',
                'nama_akun' => 'Hutang Gaji',
                'tipe_akun' => 'Kewajiban',
                'kategori_akun' => 'Kewajiban',
                'saldo_normal' => 'kredit',
                'keterangan' => 'Hutang gaji karyawan produksi',
            ],
            
            // BOP Expense Accounts (if not exist)
            [
                'kode_akun' => '550',
                'nama_akun' => 'BOP - Listrik',
                'tipe_akun' => 'Biaya',
                'kategori_akun' => 'Biaya',
                'saldo_normal' => 'debit',
                'keterangan' => 'Biaya listrik untuk produksi',
            ],
        ];
        
        foreach ($requiredCoas as $coaData) {
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
                    'saldo_awal' => 0,
                    'tanggal_saldo_awal' => now()->format('Y-m-d'),
                    'keterangan' => $coaData['keterangan'],
                    'is_akun_header' => 0,
                ]);
                
                echo "  ✅ Created: {$coaData['kode_akun']} - {$coaData['nama_akun']}\n";
            } else {
                echo "  ⏭️  Skipped: {$coaData['kode_akun']} - {$coaData['nama_akun']} (already exists)\n";
            }
        }
        
        echo "\n";
    }
}
