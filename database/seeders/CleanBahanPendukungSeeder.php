<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanBahanPendukungSeeder extends Seeder
{
    /**
     * Clean bahan_pendukungs table from NULL/empty COA fields
     * This will prevent false positive matches in CoaController
     */
    public function run(): void
    {
        Log::info('Starting CleanBahanPendukungSeeder');
        
        $user_id = 7;
        $coaKode = '530';
        
        Log::info("Cleaning bahan_pendukungs table for COA {$coaKode}");
        
        // Find records with NULL or empty COA fields that match the OR condition
        $problematicRecords = DB::table('bahan_pendukungs')
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
            ->where('user_id', $user_id)
            ->get();
        
        Log::info("Found {$problematicRecords->count()} problematic records");
        
        if ($problematicRecords->count() > 0) {
            // Show problematic records before fixing
            Log::info("Problematic records that will be fixed:");
            foreach ($problematicRecords as $record) {
                Log::info("  ID: {$record->id} - {$record->nama}");
                Log::info("    coa_persediaan_id: " . ($record->coa_persediaan_id ?? 'NULL'));
                Log::info("    coa_hpp_id: " . ($record->coa_hpp_id ?? 'NULL'));
                Log::info("    coa_pembelian_id: " . ($record->coa_pembelian_id ?? 'NULL'));
            }
            
            // Update problematic records by setting NULL fields to empty string
            $updatedCount = DB::table('bahan_pendukungs')
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
                ->where('user_id', $user_id)
                ->update([
                    'coa_persediaan_id' => '',
                    'coa_hpp_id' => '',
                    'coa_pembelian_id' => '',
                ]);
            
            Log::info("Updated {$updatedCount} records by setting NULL COA fields to empty string");
        }
        
        // Re-check after cleaning
        $finalCount = DB::table('bahan_pendukungs')
            ->where(function($query) use ($coaKode) {
                $query->where('coa_persediaan_id', $coaKode)
                      ->orWhere('coa_hpp_id', $coaKode)
                      ->orWhere('coa_pembelian_id', $coaKode);
            })
            ->count();
        
        Log::info("Final COA {$coaKode} usage in bahan_pendukungs after cleaning: {$finalCount}");
        
        $this->command->info('Bahan pendukung cleaning completed!');
        $this->command->info("COA {$coaKode} can now be deleted: " . ($finalCount == 0 ? "YES" : "NO"));
    }
}
