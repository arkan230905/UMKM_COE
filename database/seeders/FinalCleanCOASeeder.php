<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinalCleanCOASeeder extends Seeder
{
    /**
     * Final cleanup of COA 530 and verification of deletion
     * Ensure all references are properly cleaned up
     */
    public function run(): void
    {
        Log::info('Starting FinalCleanCOASeeder');
        
        $user_id = 7;
        $coaKode = '530';
        
        // Get COA ID for kode 530
        $coa = DB::table('accounts')
            ->where('kode_akun', $coaKode)
            ->where('user_id', $user_id)
            ->first();
        
        if (!$coa) {
            Log::info("COA {$coaKode} not found for user {$user_id}");
            return;
        }
        
        $coaId = $coa->id;
        
        Log::info("Final verification for COA {$coaKode} (ID: {$coaId})");
        
        // Check all tables one more time
        $tables = [
            'jurnal_umum' => 'coa_id',
            'bahan_bakus' => ['coa_persediaan_id', 'coa_hpp_id', 'coa_pembelian_id'],
            'bahan_pendukungs' => ['coa_persediaan_id', 'coa_hpp_id', 'coa_pembelian_id'],
            'pembayaran_beban' => ['akun_kas_id', 'akun_beban_id'],
        ];
        
        $totalReferences = 0;
        foreach ($tables as $tableName => $columns) {
            if (is_array($columns)) {
                foreach ($columns as $column) {
                    $count = DB::table($tableName)
                        ->where($column, $coaKode)
                        ->where('user_id', $user_id)
                        ->count();
                    
                    if ($count > 0) {
                        Log::info("  - Found {$count} references in {$tableName}.{$column} to COA {$coaKode}");
                        $totalReferences += $count;
                    }
                }
            } else {
                $count = DB::table($tableName)
                    ->where($columns, $coaId)
                    ->where('user_id', $user_id)
                    ->count();
                
                if ($count > 0) {
                    Log::info("  - Found {$count} references in {$tableName}.{$columns} to COA {$coaKode}");
                    $totalReferences += $count;
                }
            }
        }
        
        Log::info("Total references to COA {$coaKode}: {$totalReferences}");
        
        if ($totalReferences == 0) {
            Log::info("SUCCESS: COA {$coaKode} can be safely deleted - no references found");
            
            // Check child accounts
            $childCount = DB::table('accounts')
                ->where('kode_induk', $coaKode)
                ->where('user_id', $user_id)
                ->count();
            
            if ($childCount > 0) {
                Log::info("  - But has {$childCount} child accounts");
            } else {
                Log::info("  - No child accounts found");
                
                // Actually delete the COA
                DB::table('accounts')
                    ->where('kode_akun', $coaKode)
                    ->where('user_id', $user_id)
                    ->delete();
                
                Log::info("  - COA {$coaKode} deleted successfully");
                
                // Verify deletion
                $existsAfter = DB::table('accounts')
                    ->where('kode_akun', $coaKode)
                    ->where('user_id', $user_id)
                    ->exists();
                
                if (!$existsAfter) {
                    Log::info("  - Verification: COA {$coaKode} no longer exists");
                } else {
                    Log::info("  - Verification: COA {$coaKode} still exists");
                }
            }
        } else {
            Log::info("WARNING: COA {$coaKode} still has {$totalReferences} references and cannot be deleted");
        }
        
        $this->command->info('Final COA cleanup completed!');
        $this->command->info("COA {$coaKode} deletion status: " . ($totalReferences == 0 ? "DELETED" : "CANNOT DELETE"));
    }
}
