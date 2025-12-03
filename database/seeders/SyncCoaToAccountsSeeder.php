<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Coa;

class SyncCoaToAccountsSeeder extends Seeder
{
    /**
     * Sync data dari COAs ke Accounts untuk keperluan journal entries
     */
    public function run(): void
    {
        // Ambil semua COA yang bukan header
        $coas = Coa::where('is_akun_header', false)->get();

        foreach ($coas as $coa) {
            // Cek apakah sudah ada di accounts
            $exists = DB::table('accounts')
                ->where('code', $coa->kode_akun)
                ->exists();

            if (!$exists) {
                // Tentukan type berdasarkan kategori
                $type = $this->getAccountType($coa);

                // Insert ke accounts
                DB::table('accounts')->insert([
                    'code' => $coa->kode_akun,
                    'name' => $coa->nama_akun,
                    'type' => $type,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $this->command->info("Synced: {$coa->kode_akun} - {$coa->nama_akun}");
            }
        }

        $this->command->info('COA to Accounts sync completed!');
    }

    /**
     * Determine account type based on COA category
     */
    private function getAccountType($coa)
    {
        $kategori = strtolower($coa->kategori ?? '');
        $kodeAkun = $coa->kode_akun;

        // Berdasarkan kode akun (digit pertama)
        $firstDigit = substr($kodeAkun, 0, 1);

        switch ($firstDigit) {
            case '1':
                return 'asset';
            case '2':
                return 'liability';
            case '3':
                return 'equity';
            case '4':
                return 'revenue';
            case '5':
            case '6':
            case '7':
                return 'expense';
            default:
                // Fallback berdasarkan kategori
                if (str_contains($kategori, 'aset') || str_contains($kategori, 'kas') || str_contains($kategori, 'bank') || str_contains($kategori, 'piutang') || str_contains($kategori, 'persediaan')) {
                    return 'asset';
                } elseif (str_contains($kategori, 'utang') || str_contains($kategori, 'kewajiban')) {
                    return 'liability';
                } elseif (str_contains($kategori, 'modal') || str_contains($kategori, 'ekuitas')) {
                    return 'equity';
                } elseif (str_contains($kategori, 'pendapatan') || str_contains($kategori, 'penjualan')) {
                    return 'revenue';
                } elseif (str_contains($kategori, 'beban') || str_contains($kategori, 'biaya')) {
                    return 'expense';
                }
                return 'asset'; // Default
        }
    }
}
