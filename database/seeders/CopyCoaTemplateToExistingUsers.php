<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CopyCoaTemplateToExistingUsers extends Seeder
{
    /**
     * Copy COA templates (user_id = NULL) to all existing users
     * yang belum punya COA tersebut
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Get all COA templates (user_id = NULL)
        $templates = DB::table('coas')
            ->whereNull('user_id')
            ->orderBy('kode_akun')
            ->get();

        echo "📦 Found " . $templates->count() . " COA templates\n";

        // Get all users yang punya minimal 1 COA (berarti sudah setup)
        $usersWithCoas = DB::table('coas')
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        echo "👥 Found " . $usersWithCoas->count() . " users with COAs\n\n";

        $totalAdded = 0;

        foreach ($usersWithCoas as $userId) {
            echo "Processing user_id: {$userId}\n";
            
            $addedForUser = 0;

            foreach ($templates as $template) {
                // Check if user already has this COA code
                $exists = DB::table('coas')
                    ->where('user_id', $userId)
                    ->where('kode_akun', $template->kode_akun)
                    ->exists();

                if ($exists) {
                    continue; // Skip if already exists
                }

                // Copy template to this user
                DB::table('coas')->insert([
                    'user_id' => $userId,
                    'company_id' => $template->company_id,
                    'kode_akun' => $template->kode_akun,
                    'nama_akun' => $template->nama_akun,
                    'tipe_akun' => $template->tipe_akun,
                    'kategori_akun' => $template->kategori_akun,
                    'is_akun_header' => $template->is_akun_header,
                    'kode_induk' => $template->kode_induk,
                    'saldo_normal' => $template->saldo_normal,
                    'saldo_awal' => 0.00, // Reset saldo for new user
                    'tanggal_saldo_awal' => null,
                    'posted_saldo_awal' => 0,
                    'keterangan' => $template->keterangan,
                    'nomor_rekening' => null,
                    'atas_nama' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $addedForUser++;
                $totalAdded++;
            }

            if ($addedForUser > 0) {
                echo "  ✅ Added {$addedForUser} COAs to user {$userId}\n";
            } else {
                echo "  ℹ️  User {$userId} already has all template COAs\n";
            }

            // Show total COAs for this user
            $userCoaCount = DB::table('coas')
                ->where('user_id', $userId)
                ->count();
            echo "  📊 Total COAs for user {$userId}: {$userCoaCount}\n\n";
        }

        echo str_repeat('=', 60) . "\n";
        echo "✅ Seeder completed!\n";
        echo "📊 Total COAs added across all users: {$totalAdded}\n";
        echo "👥 Users processed: " . $usersWithCoas->count() . "\n";
    }
}
