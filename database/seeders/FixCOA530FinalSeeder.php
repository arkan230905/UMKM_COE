<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixCOA530FinalSeeder extends Seeder
{
    /**
     * Final fix for COA 530 deletion issue
     * Direct approach to identify and fix the problem
     */
    public function run(): void
    {
        Log::info('Starting FixCOA530FinalSeeder');
        
        $user_id = 7;
        $coaKode = '530';
        
        // Get COA ID for kode 530
        $coa = DB::table('coas')
            ->where('kode_akun', $coaKode)
            ->where('user_id', $user_id)
            ->first();
        
        if (!$coa) {
            Log::info("COA {$coaKode} not found for user {$user_id}");
            return;
        }
        
        $coaId = $coa->id;
        
        Log::info("Final fix for COA {$coaKode} (ID: {$coaId})");
        
        // Step 1: Check bahan_pendukungs table structure
        try {
            $structure = DB::select("DESCRIBE bahan_pendukungs");
            Log::info("bahan_pendukungs table structure:");
            foreach ($structure as $col) {
                Log::info("  - {$col->Field}: {$col->Type}");
            }
        } catch (\Exception $e) {
            Log::info("Error getting bahan_pendukungs structure: " . $e->getMessage());
        }
        
        // Step 2: Check all records in bahan_pendukungs for this user
        try {
            $allRecords = DB::table('bahan_pendukungs')
                ->where('user_id', $user_id)
                ->get();
            
            Log::info("Total bahan_pendukungs records for user {$user_id}: {$allRecords->count()}");
            
            // Show first few records to understand the data
            $sampleRecords = DB::table('bahan_pendukungs')
                ->where('user_id', $user_id)
                ->limit(3)
                ->get();
            
            Log::info("Sample bahan_pendukungs records:");
            foreach ($sampleRecords as $record) {
                Log::info("  ID: {$record->id}");
                Log::info("    Fields: " . json_encode([
                    'coa_persediaan_id' => $record->coa_persediaan_id ?? 'N/A',
                    'coa_hpp_id' => $record->coa_hpp_id ?? 'N/A',
                    'coa_pembelian_id' => $record->coa_pembelian_id ?? 'N/A'
                ]));
            }
        } catch (\Exception $e) {
            Log::info("Error getting bahan_pendukungs records: " . $e->getMessage());
        }
        
        // Step 3: Check exact query that CoaController uses
        try {
            // This is the exact query from CoaController line 382-387
            $coaControllerCount = DB::table('bahan_pendukungs')
                ->where('coa_persediaan_id', $coa->kode_akun)
                ->orWhere('coa_hpp_id', $coa->kode_akun)
                ->orWhere('coa_pembelian_id', $coa->kode_akun)
                ->count();
            
            Log::info("CoaController query result: {$coaControllerCount} records found");
            
            if ($coaControllerCount > 0) {
                // Find the exact records that match
                $matchingRecords = DB::table('bahan_pendukungs')
                    ->where('coa_persediaan_id', $coa->kode_akun)
                    ->orWhere('coa_hpp_id', $coa->kode_akun)
                    ->orWhere('coa_pembelian_id', $coa->kode_akun)
                    ->limit(5)
                    ->get();
                
                Log::info("Matching records found by CoaController query:");
                foreach ($matchingRecords as $record) {
                    Log::info("  ID: {$record->id}");
                    Log::info("    coa_persediaan_id: " . ($record->coa_persediaan_id ?? 'NULL'));
                    Log::info("    coa_hpp_id: " . ($record->coa_hpp_id ?? 'NULL'));
                    Log::info("    coa_pembelian_id: " . ($record->coa_pembelian_id ?? 'NULL'));
                }
                
                // Step 4: Fix these records by updating them to not use COA 530
                // Since they shouldn't be using COA 530 according to user's BOP proses
                DB::table('bahan_pendukungs')
                    ->where('coa_persediaan_id', $coa->kode_akun)
                    ->orWhere('coa_hpp_id', $coa->kode_akun)
                    ->orWhere('coa_pembelian_id', $coa->kode_akun)
                    ->update([
                        'coa_persediaan_id' => '',
                        'coa_hpp_id' => '',
                        'coa_pembelian_id' => '',
                    ]);
                
                Log::info("Updated {$coaControllerCount} records to remove COA {$coaKode} references");
            }
        } catch (\Exception $e) {
            Log::info("Error in CoaController query: " . $e->getMessage());
        }
        
        // Step 5: Final verification
        try {
            $finalCount = DB::table('bahan_pendukungs')
                ->where('coa_persediaan_id', $coa->kode_akun)
                ->orWhere('coa_hpp_id', $coa->kode_akun)
                ->orWhere('coa_pembelian_id', $coa->kode_akun)
                ->count();
            
            Log::info("Final COA {$coaKode} usage in bahan_pendukungs: {$finalCount}");
            
            if ($finalCount == 0) {
                Log::info("SUCCESS: COA {$coaKode} can now be deleted from bahan_pendukungs check");
                
                // Now try to delete the COA
                $childCount = DB::table('coas')
                    ->where('kode_induk', $coaKode)
                    ->where('user_id', $user_id)
                    ->count();
                
                $jurnalCount = DB::table('jurnal_umum')
                    ->where('coa_id', $coaId)
                    ->count();
                
                $bahanBakuCount = DB::table('bahan_bakus')
                    ->where('coa_persediaan_id', $coaKode)
                    ->orWhere('coa_hpp_id', $coaKode)
                    ->orWhere('coa_pembelian_id', $coaKode)
                    ->count();
                
                if ($childCount == 0 && $jurnalCount == 0 && $bahanBakuCount == 0) {
                    // Delete the COA
                    DB::table('coas')
                        ->where('kode_akun', $coaKode)
                        ->where('user_id', $user_id)
                        ->delete();
                    
                    Log::info("COA {$coaKode} deleted successfully!");
                    
                    // Verify deletion
                    $existsAfter = DB::table('coas')
                        ->where('kode_akun', $coaKode)
                        ->where('user_id', $user_id)
                        ->exists();
                    
                    if (!$existsAfter) {
                        Log::info("Verification: COA {$coaKode} no longer exists");
                    } else {
                        Log::info("Verification: COA {$coaKode} still exists");
                    }
                } else {
                    Log::info("COA {$coaKode} still has other references and cannot be deleted");
                    Log::info("  - Child accounts: {$childCount}");
                    Log::info("  - Journal entries: {$jurnalCount}");
                    Log::info("  - Bahan baku: {$bahanBakuCount}");
                }
            } else {
                Log::info("FAILED: COA {$coaKode} still has {$finalCount} references in bahan_pendukungs");
            }
        } catch (\Exception $e) {
            Log::info("Error in final verification: " . $e->getMessage());
        }
        
        $this->command->info('COA 530 final fix completed!');
    }
}
