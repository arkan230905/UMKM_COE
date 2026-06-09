<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FixMissingWipCoasForUsers extends Seeder
{
    /**
     * Seeder untuk menambahkan COA WIP yang hilang (1171, 1172, 1173)
     * untuk user yang punya header 117 tapi tidak punya child-nya
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Find all users yang punya COA 117 (header)
        $usersWithHeader117 = DB::table('coas')
            ->where('kode_akun', '117')
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique();

        echo "🔍 Found " . $usersWithHeader117->count() . " users with COA 117 header\n";

        $totalAdded = 0;

        foreach ($usersWithHeader117 as $userId) {
            echo "\n👤 Checking user_id: {$userId}\n";
            
            // Check which WIP COAs are missing for this user
            $existingWipCoas = DB::table('coas')
                ->where('user_id', $userId)
                ->whereIn('kode_akun', ['1171', '1172', '1173'])
                ->pluck('kode_akun')
                ->toArray();

            $missingCodes = array_diff(['1171', '1172', '1173'], $existingWipCoas);

            if (count($missingCodes) === 0) {
                echo "  ✅ User {$userId} already has all WIP COAs\n";
                continue;
            }

            echo "  ⚠️  Missing COA codes: " . implode(', ', $missingCodes) . "\n";

            // Add missing COAs
            $wipCoaTemplates = [
                '1171' => 'Pers. Barang Dalam Proses - BBB (WIP BBB)',
                '1172' => 'Pers. Barang Dalam Proses - BTKL (WIP BTKL)',
                '1173' => 'Pers. Barang Dalam Proses - BOP (WIP BOP)',
            ];

            foreach ($missingCodes as $code) {
                DB::table('coas')->insert([
                    'user_id' => $userId,
                    'company_id' => null,
                    'kode_akun' => $code,
                    'nama_akun' => $wipCoaTemplates[$code],
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
                
                echo "    ✅ Added COA {$code} - {$wipCoaTemplates[$code]}\n";
                $totalAdded++;
            }
        }

        echo "\n" . str_repeat('=', 60) . "\n";
        echo "✅ Fix completed!\n";
        echo "📊 Total COAs added: {$totalAdded}\n";
        echo "👥 Users processed: " . $usersWithHeader117->count() . "\n";
    }
}
