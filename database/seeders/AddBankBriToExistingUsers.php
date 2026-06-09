<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddBankBriToExistingUsers extends Seeder
{
    /**
     * Seeder untuk menambahkan Bank BRI (1113) ke user yang sudah punya Bank BCA/Mandiri
     * tapi belum punya Bank BRI
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Find all users yang punya COA 111 (Kas Bank header) atau 1111/1112 (BCA/Mandiri)
        $usersWithBanks = DB::table('coas')
            ->whereIn('kode_akun', ['111', '1111', '1112'])
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique();

        echo "🔍 Found " . $usersWithBanks->count() . " users with bank accounts\n";

        $totalAdded = 0;

        foreach ($usersWithBanks as $userId) {
            // Check if user already has Bank BRI (1113)
            $hasBankBri = DB::table('coas')
                ->where('user_id', $userId)
                ->where('kode_akun', '1113')
                ->exists();

            if ($hasBankBri) {
                echo "  ✅ User {$userId} already has Bank BRI\n";
                continue;
            }

            echo "  ➕ Adding Bank BRI to user {$userId}\n";

            DB::table('coas')->insert([
                'user_id' => $userId,
                'company_id' => null,
                'kode_akun' => '1113',
                'nama_akun' => 'Bank BRI',
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

            echo "    ✅ Added COA 1113 - Bank BRI\n";
            $totalAdded++;
        }

        // Also add to template (user_id = NULL) if not exists
        $templateHasBri = DB::table('coas')
            ->whereNull('user_id')
            ->where('kode_akun', '1113')
            ->exists();

        if (!$templateHasBri) {
            DB::table('coas')->insert([
                'user_id' => null,
                'company_id' => null,
                'kode_akun' => '1113',
                'nama_akun' => 'Bank BRI',
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
            echo "  ✅ Added Bank BRI to template (user_id = NULL)\n";
            $totalAdded++;
        }

        echo "\n" . str_repeat('=', 60) . "\n";
        echo "✅ Fix completed!\n";
        echo "📊 Total Bank BRI accounts added: {$totalAdded}\n";
        echo "👥 Users processed: " . $usersWithBanks->count() . "\n";
        echo "🏦 Bank structure: BCA (1111), Mandiri (1112), BRI (1113)\n";
    }
}
