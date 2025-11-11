<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coa;
use App\Models\Account;

class SyncAccountsFromCoaSeeder extends Seeder
{
    /**
     * Sinkronisasi data accounts dari COA
     * Memastikan semua akun di accounts memiliki nama yang benar dari COA
     */
    public function run(): void
    {
        // 1. Sinkronisasi dari COA
        $coas = Coa::where('is_akun_header', '!=', 1)->get();
        
        $created = 0;
        $updated = 0;
        
        foreach ($coas as $coa) {
            $account = Account::where('code', $coa->kode_akun)->first();
            
            // Tentukan tipe akun
            $type = $this->mapCoaTypeToAccountType($coa->tipe_akun);
            
            if ($account) {
                // Update jika nama atau tipe berbeda
                if ($account->name !== $coa->nama_akun || $account->type !== $type) {
                    $account->update([
                        'name' => $coa->nama_akun,
                        'type' => $type,
                    ]);
                    $updated++;
                }
            } else {
                // Buat akun baru
                Account::create([
                    'code' => $coa->kode_akun,
                    'name' => $coa->nama_akun,
                    'type' => $type,
                ]);
                $created++;
            }
        }
        
        // 2. Update akun yang namanya masih berupa kode (hanya angka)
        $accountsWithCodeAsName = Account::whereRaw('name REGEXP \'^[0-9]+$\'')->get();
        $fixed = 0;
        
        foreach ($accountsWithCodeAsName as $account) {
            // Cari di COA berdasarkan kode
            $coa = Coa::where('kode_akun', $account->code)->first();
            
            if ($coa) {
                $account->update([
                    'name' => $coa->nama_akun,
                    'type' => $this->mapCoaTypeToAccountType($coa->tipe_akun),
                ]);
                $fixed++;
            } else {
                // Jika tidak ada di COA, beri nama default berdasarkan kode
                $defaultName = $this->getDefaultNameByCode($account->code);
                $account->update(['name' => $defaultName]);
                $fixed++;
            }
        }
        
        $this->command->info("Sinkronisasi selesai:");
        $this->command->info("- {$created} akun baru dibuat");
        $this->command->info("- {$updated} akun diupdate");
        $this->command->info("- {$fixed} akun dengan nama kode diperbaiki");
    }
    
    private function getDefaultNameByCode(string $code): string
    {
        // Mapping default untuk kode yang umum
        $mapping = [
            '101' => 'Kas Kecil',
            '102' => 'Kas di Bank',
            '103' => 'Piutang Usaha',
            '121' => 'Persediaan Bahan Baku',
            '122' => 'Persediaan Barang Dalam Proses (WIP)',
            '123' => 'Persediaan Barang Jadi',
            '124' => 'Akumulasi Penyusutan',
            '201' => 'Hutang Usaha',
            '211' => 'Hutang Gaji (BTKL)',
            '212' => 'Hutang BOP',
            '401' => 'Penjualan Produk',
            '501' => 'Harga Pokok Penjualan (HPP)',
            '504' => 'Beban Penyusutan',
            '505' => 'Beban Denda dan Bunga',
            '506' => 'Penyesuaian HPP (Diskon)',
            // Kode 4 digit
            '1101' => 'Kas Kecil',
            '1102' => 'Kas di Bank',
            '1103' => 'Piutang Usaha',
            '1104' => 'Persediaan Bahan Baku',
            '1105' => 'Persediaan Barang Dalam Proses (WIP)',
            '1106' => 'Persediaan Barang Dalam Proses',
            '1107' => 'Persediaan Barang Jadi',
            '2101' => 'Hutang Usaha',
            '2103' => 'Hutang Gaji (BTKL)',
            '2104' => 'Hutang BOP',
            '4101' => 'Penjualan Produk',
            '5001' => 'Harga Pokok Penjualan (HPP)',
            '5103' => 'Beban Penyusutan',
            '5104' => 'Beban Denda dan Bunga',
            '5105' => 'Penyesuaian HPP (Diskon Pembelian)',
        ];
        
        return $mapping[$code] ?? "Akun {$code}";
    }
    
    private function mapCoaTypeToAccountType(string $tipe): string
    {
        $t = strtolower(trim($tipe));
        return match ($t) {
            'asset', 'assets', 'aktiva' => 'asset',
            'liability', 'liabilities', 'utang', 'kewajiban' => 'liability',
            'equity', 'modal' => 'equity',
            'revenue', 'pendapatan' => 'revenue',
            'expense', 'beban' => 'expense',
            default => 'asset',
        };
    }
}
