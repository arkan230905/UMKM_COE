<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\Coa;

class EnsureKasBankAccountsSeeder extends Seeder
{
    public function run()
    {
        // Pastikan semua akun kas/bank ada di tabel accounts
        $kasBankCodes = ['1101', '101', '1102', '102', '1103'];
        
        foreach ($kasBankCodes as $code) {
            // Cek apakah sudah ada
            $exists = Account::where('code', $code)->exists();
            
            if (!$exists) {
                // Ambil dari COA
                $coa = Coa::where('kode_akun', $code)->first();
                
                if ($coa) {
                    Account::create([
                        'code' => $code,
                        'name' => $coa->nama_akun,
                        'type' => 'asset',
                    ]);
                    
                    $this->command->info("✓ Created account: {$code} - {$coa->nama_akun}");
                } else {
                    // Buat dengan nama default
                    $defaultNames = [
                        '1101' => 'Kas Kecil',
                        '101' => 'Kas',
                        '1102' => 'Kas di Bank',
                        '102' => 'Bank',
                        '1103' => 'Piutang Usaha',
                    ];
                    
                    Account::create([
                        'code' => $code,
                        'name' => $defaultNames[$code] ?? 'Akun ' . $code,
                        'type' => 'asset',
                    ]);
                    
                    $this->command->warn("⚠ Created account without COA: {$code}");
                }
            } else {
                $this->command->info("✓ Account already exists: {$code}");
            }
        }
        
        $this->command->info("\n✅ All Kas/Bank accounts ensured!");
    }
}
