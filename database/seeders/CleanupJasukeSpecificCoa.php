<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanupJasukeSpecificCoa extends Seeder
{
    /**
     * Remove specific Jasuke COA (Original, Coklat) 
     * Keep only generic "Pers. Barang Jadi Jasuke" (1181)
     */
    public function run()
    {
        $this->command->info('🗑️ Cleaning up specific Jasuke COA variants...');
        
        // COA to remove: Jasuke Original and Jasuke Coklat
        $toRemove = [
            '1181' => 'Pers. Barang Jadi Jasuke Original',
            '1182' => 'Pers. Barang Jadi Jasuke Coklat',
        ];
        
        $deletedCount = 0;
        
        foreach ($toRemove as $code => $name) {
            $deleted = DB::table('coas')
                ->where('kode_akun', $code)
                ->where('nama_akun', 'LIKE', '%' . $name . '%')
                ->delete();
            
            if ($deleted > 0) {
                $deletedCount += $deleted;
                $this->command->info("  ✅ Deleted COA {$code} - {$name}");
            }
        }
        
        // Now ensure generic "Pers. Barang Jadi Jasuke" exists
        $generic = DB::table('coas')
            ->where('kode_akun', '1181')
            ->where('nama_akun', 'Pers. Barang Jadi Jasuke')
            ->exists();
        
        if (!$generic) {
            $this->command->info('  ℹ️ Generic "Pers. Barang Jadi Jasuke (1181)" not found');
            $this->command->info('  ➕ Adding generic Jasuke COA...');
            
            DB::table('coas')->insert([
                'user_id' => null,
                'kode_induk' => '118',
                'kode_akun' => '1181',
                'nama_akun' => 'Pers. Barang Jadi Jasuke',
                'tipe_akun' => 'Aset',
                'kategori_akun' => '-',
                'is_akun_header' => 0,
                'saldo_normal' => 'debit',
                'saldo_awal' => 0.00,
                'tanggal_saldo_awal' => null,
                'posted_saldo_awal' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('  ✅ Added generic Jasuke COA (1181)');
        } else {
            $this->command->info('  ✅ Generic "Pers. Barang Jadi Jasuke (1181)" already exists');
        }
        
        $this->command->info("✅ Total specific Jasuke COA deleted: {$deletedCount}");
        $this->command->info("✅ Cleanup completed!");
    }
}
