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
        $this->command->info('🗑️ Removing COA Ayam from database...');
        
        // List of COA codes that are specific to Ayam (chicken) business
        $ayamCoaCodes = [
            // Bahan Baku Ayam
            '1141', '1142', '1143', '1144', '1145', '1146', '1147', '1148', '1149',
            
            // Bahan Pendukung Ayam specific (keep generic ones like Air, Susu, Keju)
            '1154', // Ayam Lainnya
            '1156', // Kemasan Cup
            '1158', // Bawang Putih
            
            // Produk Jadi Ayam Crispy Macdi
            '1181', '1182', '1183', '1184', '1185', '1186', '1187', '1188', '1189',
            
            // Produk Jadi Ayam Goreng Bundo
            '1191', '1192', '1193', '1194', '1195', '1196', '1197', '1198', '1199',
        ];
        
        $deletedCount = 0;
        
        foreach ($ayamCoaCodes as $code) {
            $deleted = DB::table('coas')
                ->where('kode_akun', $code)
                ->delete();
            
            if ($deleted > 0) {
                $deletedCount += $deleted;
                $this->command->info("  ✅ Deleted COA {$code}");
            }
        }
        
        $this->command->info("✅ Total COA Ayam deleted: {$deletedCount}");
        
        // Verify remaining COAs
        $remaining = DB::table('coas')->count();
        $this->command->info("📊 Remaining COAs in database: {$remaining}");
    }
}
