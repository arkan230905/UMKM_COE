<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixPerusahaanUserIdSeeder extends Seeder
{
    /**
     * Fix existing perusahaan records with NULL user_id
     * Link UMKM COE to user_id 4
     */
    public function run(): void
    {
        Log::info('Starting FixPerusahaanUserIdSeeder');
        
        // Update UMKM COE perusahaan to user_id 4
        $updated = DB::table('perusahaan')
            ->where('nama', 'UMKM COE')
            ->whereNull('user_id')
            ->update(['user_id' => 4]);
        
        Log::info("Updated {$updated} perusahaan records for UMKM COE");
        
        // Check for other perusahaan with NULL user_id
        $nullUserIds = DB::table('perusahaan')
            ->whereNull('user_id')
            ->get();
            
        if ($nullUserIds->count() > 0) {
            Log::warning('Found ' . $nullUserIds->count() . ' perusahaan records with NULL user_id');
            foreach ($nullUserIds as $perusahaan) {
                Log::warning('Perusahaan ID ' . $perusahaan->id . ' (' . $perusahaan->nama . ') has NULL user_id');
            }
        }
        
        // Verify the fix
        $umkmCoe = DB::table('perusahaan')
            ->where('nama', 'UMKM COE')
            ->first();
            
        if ($umkmCoe && $umkmCoe->user_id == 4) {
            Log::info('SUCCESS: UMKM COE now linked to user_id 4');
        } else {
            Log::error('FAILED: UMKM COE not properly linked to user_id 4');
        }
        
        $this->command->info('Perusahaan user_id fix completed!');
        $this->command->info("Updated {$updated} records");
    }
}
