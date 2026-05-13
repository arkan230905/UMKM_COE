<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteCOA530Seeder extends Seeder
{
    /**
     * Hapus COA 530 dari database user 7
     * Agar sesuai dengan DefaultCoaSeeder yang tidak ada COA 530
     */
    public function run(): void
    {
        Log::info('Starting DeleteCOA530Seeder');
        
        $user_id = 7;
        $coaKode = '530';
        
        // Hapus COA 530 dari database
        $deletedCount = DB::table('accounts')
            ->where('kode_akun', $coaKode)
            ->where('user_id', $user_id)
            ->delete();
        
        Log::info("Deleted {$deletedCount} COA records with kode {$coaKode} for user {$user_id}");
        
        // Verifikasi penghapusan
        $existsAfter = DB::table('accounts')
            ->where('kode_akun', $coaKode)
            ->where('user_id', $user_id)
            ->exists();
        
        if (!$existsAfter) {
            Log::info("SUCCESS: COA {$coaKode} no longer exists for user {$user_id}");
        } else {
            Log::info("WARNING: COA {$coaKode} still exists for user {$user_id}");
        }
        
        // Tampilkan COA BOP yang tersisa untuk user ini
        $bopCOAs = DB::table('accounts')
            ->where('user_id', $user_id)
            ->where('kode_akun', 'like', '53%')
            ->orderBy('kode_akun')
            ->get(['kode_akun', 'nama_akun']);
        
        Log::info("Remaining BOP COAs for user {$user_id}:");
        foreach ($bopCOAs as $coa) {
            Log::info("  - {$coa->kode_akun}: {$coa->nama_akun}");
        }
        
        $this->command->info('COA 530 deletion completed!');
        $this->command->info("COA {$coaKode} deleted: " . ($deletedCount > 0 ? "YES" : "NO"));
        $this->command->info("Remaining BOP COAs: {$bopCOAs->count()} accounts");
    }
}
