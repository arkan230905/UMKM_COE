<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddBankAccountsToAllUsers extends Seeder
{
    /**
     * Seeder untuk menambahkan 3 bank accounts (BCA, Mandiri, BRI) 
     * ke semua user yang punya COA Kas Bank (111)
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $bankAccounts = [
            '1111' => 'Bank BCA',
            '1112' => 'Bank Mandiri',
            '1113' => 'Bank BRI',
        ];

        // Find all users yang punya COA 111 (Kas Bank header)
        $usersWithKasBank = DB::table('coas')
            ->where('kode_akun', '111')
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique();

        echo "🔍 Found " . $usersWithKasBank->count() . " users with Kas Bank (111) header\n";
        echo "🏦 Will add: BCA (1111), Mandiri (1112), BRI (1113)\n\n";

        $totalAdded = 0;

        foreach ($usersWithKasBank as $userId) {
            echo "👤 Processing user_id: {$userId}\n";

            foreach ($bankAccounts as $kodeAkun => $namaBank) {
                // Check if user already has this bank
                $hasBank = DB::table('coas')
                    ->where('user_id', $userId)
                    ->where('kode_akun', $kodeAkun)
                    ->exists();

                if ($hasBank) {
                    echo "  ✅ User {$userId} already has {$namaBank} ({$kodeAkun})\n";
                    continue;
                }

                echo "  ➕ Adding {$namaBank} ({$kodeAkun}) to user {$userId}\n";

                DB::table('coas')->insert([
                    'user_id' => $userId,
                    'company_id' => null,
                    'kode_akun' => $kodeAkun,
                    'nama_akun' => $namaBank,
                    'tipe_akun' => 'Aset',
                    'kategori_akun' => '-',
                    'is_akun_header' => 0,
                    'kode_induk' => null,
                    'saldo_normal' => 'debit',
                    'saldo_awal' => 0.00,
                    'tanggal_saldo_awal' => null,
                    'posted_saldo_awal' => 0,
                    'keterangan' => null,
                    'nomor_rekening' => null,
                    'atas_nama' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $totalAdded++;
            }
            
            echo "\n";
        }

        // Also add to template (user_id = NULL) if not exists
        echo "📦 Checking template COAs (user_id = NULL)\n";
        foreach ($bankAccounts as $kodeAkun => $namaBank) {
            $templateHasBank = DB::table('coas')
                ->whereNull('user_id')
                ->where('kode_akun', $kodeAkun)
                ->exists();

            if (!$templateHasBank) {
                DB::table('coas')->insert([
                    'user_id' => null,
                    'company_id' => null,
                    'kode_akun' => $kodeAkun,
                    'nama_akun' => $namaBank,
                    'tipe_akun' => 'Aset',
                    'kategori_akun' => '-',
                    'is_akun_header' => 0,
                    'kode_induk' => null,
                    'saldo_normal' => 'debit',
                    'saldo_awal' => 0.00,
                    'tanggal_saldo_awal' => null,
                    'posted_saldo_awal' => 0,
                    'keterangan' => null,
                    'nomor_rekening' => null,
                    'atas_nama' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                echo "  ✅ Added {$namaBank} ({$kodeAkun}) to template\n";
                $totalAdded++;
            } else {
                echo "  ✅ Template already has {$namaBank} ({$kodeAkun})\n";
            }
        }

        echo "\n" . str_repeat('=', 60) . "\n";
        echo "✅ Seeder completed!\n";
        echo "📊 Total bank accounts added: {$totalAdded}\n";
        echo "👥 Users processed: " . $usersWithKasBank->count() . "\n";
        echo "🏦 Bank structure complete: BCA (1111), Mandiri (1112), BRI (1113)\n";
    }
}
