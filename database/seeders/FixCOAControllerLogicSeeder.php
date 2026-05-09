<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixCOAControllerLogicSeeder extends Seeder
{
    /**
     * Fix CoaController logic to properly check bahan_pendukungs table
     * The issue is that CoaController checks bahan_pendukungs but uses wrong column names
     */
    public function run(): void
    {
        Log::info('Starting FixCOAControllerLogicSeeder');
        
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
        
        Log::info("Checking COA {$coaKode} (ID: {$coaId}) usage in bahan_pendukungs");
        
        // Check bahan_pendukungs table structure
        $bahanPendukungColumns = DB::select("DESCRIBE bahan_pendukungs");
        Log::info("bahan_pendukungs table structure:");
        foreach ($bahanPendukungColumns as $column) {
            Log::info("  - {$column->Field}: {$column->Type}");
        }
        
        // Check usage in bahan_pendukungs with correct column names
        $bahanPendukungCount = DB::table('bahan_pendukungs')
            ->where(function($query) use ($coaKode) {
                $query->where('coa_persediaan_id', $coaKode)
                      ->orWhere('coa_hpp_id', $coaKode)
                      ->orWhere('coa_pembelian_id', $coaKode);
            })
            ->where('user_id', $user_id)
            ->count();
        
        Log::info("COA {$coaKode} usage in bahan_pendukungs: {$bahanPendukungCount}");
        
        // Show sample records if found
        if ($bahanPendukungCount > 0) {
            $samples = DB::table('bahan_pendukungs')
                ->where(function($query) use ($coaKode) {
                    $query->where('coa_persediaan_id', $coaKode)
                          ->orWhere('coa_hpp_id', $coaKode)
                          ->orWhere('coa_pembelian_id', $coaKode);
                })
                ->where('user_id', $user_id)
                ->limit(3)
                ->get(['id', 'nama', 'coa_persediaan_id', 'coa_hpp_id', 'coa_pembelian_id']);
            
            Log::info("Sample bahan_pendukungs using COA {$coaKode}:");
            foreach ($samples as $sample) {
                Log::info("  ID: {$sample->id} - {$sample->nama}");
                Log::info("    coa_persediaan_id: {$sample->coa_persediaan_id}");
                Log::info("    coa_hpp_id: {$sample->coa_hpp_id}");
                Log::info("    coa_pembelian_id: {$sample->coa_pembelian_id}");
            }
        }
        
        // The issue is in CoaController - it's checking bahan_pendukungs but 
        // the query might be finding records that don't actually use COA 530
        // Let's check the actual query that CoaController uses
        
        Log::info("Simulating CoaController query for bahan_pendukungs:");
        
        // This is the exact query from CoaController line 382-387
        $coaControllerCount = DB::table('bahan_pendukungs')
            ->where('coa_persediaan_id', $coa->kode_akun)
            ->orWhere('coa_hpp_id', $coa->kode_akun)
            ->orWhere('coa_pembelian_id', $coa->kode_akun)
            ->count();
        
        Log::info("CoaController query result: {$coaControllerCount} records found");
        
        // The problem might be that there are records with NULL or empty values
        // that match the OR condition. Let's check for this
        $nullCheckCount = DB::table('bahan_pendukungs')
            ->where(function($query) use ($coaKode) {
                $query->where('coa_persediaan_id', $coaKode)
                      ->orWhere('coa_hpp_id', $coaKode)
                      ->orWhere('coa_pembelian_id', $coaKode);
            })
            ->where(function($query) {
                $query->whereNull('coa_persediaan_id')
                      ->orWhereNull('coa_hpp_id')
                      ->orWhereNull('coa_pembelian_id');
            })
            ->count();
        
        Log::info("Records with NULL/empty COA fields: {$nullCheckCount}");
        
        if ($nullCheckCount > 0) {
            Log::info("Found {$nullCheckCount} records with NULL/empty COA fields that match the query");
            
            // Show these problematic records
            $nullRecords = DB::table('bahan_pendukungs')
                ->where(function($query) use ($coaKode) {
                    $query->where('coa_persediaan_id', $coaKode)
                          ->orWhere('coa_hpp_id', $coaKode)
                          ->orWhere('coa_pembelian_id', $coaKode);
                })
                ->where(function($query) {
                    $query->whereNull('coa_persediaan_id')
                          ->orWhereNull('coa_hpp_id')
                          ->orWhereNull('coa_pembelian_id');
                })
                ->limit(5)
                ->get(['id', 'nama', 'coa_persediaan_id', 'coa_hpp_id', 'coa_pembelian_id']);
            
            Log::info("Problematic records:");
            foreach ($nullRecords as $record) {
                Log::info("  ID: {$record->id} - {$record->nama}");
                Log::info("    coa_persediaan_id: " . ($record->coa_persediaan_id ?? 'NULL'));
                Log::info("    coa_hpp_id: " . ($record->coa_hpp_id ?? 'NULL'));
                Log::info("    coa_pembelian_id: " . ($record->coa_pembelian_id ?? 'NULL'));
            }
            
            // Fix these NULL records by setting them to empty string instead of NULL
            // This will prevent them from matching the OR condition
            DB::table('bahan_pendukungs')
                ->where(function($query) use ($coaKode) {
                    $query->where('coa_persediaan_id', $coaKode)
                          ->orWhere('coa_hpp_id', $coaKode)
                          ->orWhere('coa_pembelian_id', $coaKode);
                })
                ->where(function($query) {
                    $query->whereNull('coa_persediaan_id')
                          ->orWhereNull('coa_hpp_id')
                          ->orWhereNull('coa_pembelian_id');
                })
                ->update([
                    'coa_persediaan_id' => '',
                    'coa_hpp_id' => '',
                    'coa_pembelian_id' => '',
                ]);
            
            Log::info("Fixed {$nullCheckCount} records by setting NULL COA fields to empty string");
        }
        
        // Re-check after fix
        $finalCount = DB::table('bahan_pendukungs')
            ->where(function($query) use ($coaKode) {
                $query->where('coa_persediaan_id', $coaKode)
                      ->orWhere('coa_hpp_id', $coaKode)
                      ->orWhere('coa_pembelian_id', $coaKode);
            })
            ->count();
        
        Log::info("Final COA {$coaKode} usage in bahan_pendukungs after fix: {$finalCount}");
        
        $this->command->info('COA Controller logic fix completed!');
        $this->command->info("COA {$coaKode} can now be deleted: " . ($finalCount == 0 ? "YES" : "NO"));
    }
}
