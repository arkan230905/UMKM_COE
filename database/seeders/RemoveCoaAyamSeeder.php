<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RemoveCoaAyamSeeder extends Seeder
{
    /**
     * Remove COA Ayam yang tidak seharusnya ada untuk user non-Ayam business
     * 
     * COA yang akan dihapus:
     * - Semua Bahan Baku Ayam (114x)
     * - Semua Bahan Pendukung Ayam (115x specific)
     * - Semua Produk Jadi Ayam (118x)
     */
    public function run()
    {
        $this->command->info('🗑️ Removing ALL COA containing "Ayam" keyword...');
        
        // DELETE all COA records that contain "Ayam" in nama_akun
        $deleted = DB::table('coas')
            ->where('nama_akun', 'LIKE', '%Ayam%')
            ->orWhere('nama_akun', 'LIKE', '%ayam%')
            ->delete();
        
        $this->command->info("✅ Total COA with 'Ayam' deleted: {$deleted}");
        
        // Verify remaining COAs
        $remaining = DB::table('coas')->count();
        $this->command->info("📊 Remaining COAs in database: {$remaining}");
        
        // Check if any "Ayam" COA still exists
        $stillExists = DB::table('coas')
            ->where('nama_akun', 'LIKE', '%Ayam%')
            ->orWhere('nama_akun', 'LIKE', '%ayam%')
            ->count();
        
        if ($stillExists > 0) {
            $this->command->warn("⚠️ WARNING: Still found {$stillExists} COA with 'Ayam' keyword!");
        } else {
            $this->command->info("✅ All COA with 'Ayam' keyword successfully removed!");
        }
    }
}
