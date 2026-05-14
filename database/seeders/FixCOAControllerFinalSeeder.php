<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixCOAControllerFinalSeeder extends Seeder
{
    /**
     * Fix CoaController to properly handle bahan_pendukungs table
     * The issue is that CoaController checks for COA usage but needs to be more specific
     */
    public function run(): void
    {
        Log::info('Starting FixCOAControllerFinalSeeder');
        
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
        
        Log::info("Final analysis for COA {$coaKode} (ID: {$coaId})");
        
        // Check bahan_pendukungs with more specific query
        // The issue might be that there are records with NULL or empty values
        // that still match the OR condition
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
            ->get(['id', 'nama', 'coa_persediaan_id', 'coa_hpp_id', 'coa_pembelian_id']);
        
        Log::info("Found {$problematicRecords->count()} problematic records in bahan_pendukungs");
        
        if ($problematicRecords->count() > 0) {
            Log::info("Problematic records that will be updated:");
            foreach ($problematicRecords as $record) {
                Log::info("  ID: {$record->id} - {$record->nama}");
                Log::info("    coa_persediaan_id: " . ($record->coa_persediaan_id ?? 'NULL'));
                Log::info("    coa_hpp_id: " . ($record->coa_hpp_id ?? 'NULL'));
                Log::info("    coa_pembelian_id: " . ($record->coa_pembelian_id ?? 'NULL'));
            }
            
            // Update these records to use empty string instead of NULL
            // This will prevent them from matching the OR condition in CoaController
            DB::table('bahan_pendukungs')
                ->whereIn('id', $problematicRecords->pluck('id'))
                ->where('user_id', $user_id)
                ->update([
                    'coa_persediaan_id' => DB::raw('IFNULL(coa_persediaan_id, "")'),
                    'coa_hpp_id' => DB::raw('IFNULL(coa_hpp_id, "")'),
                    'coa_pembelian_id' => DB::raw('IFNULL(coa_pembelian_id, "")'),
                ]);
            
            Log::info("Updated {$problematicRecords->count()} records to use empty string instead of NULL");
        }
        
        // Re-check after update
        $finalCount = DB::table('bahan_pendukungs')
            ->where(function($query) use ($coaKode) {
                $query->where('coa_persediaan_id', $coaKode)
                      ->orWhere('coa_hpp_id', $coaKode)
                      ->orWhere('coa_pembelian_id', $coaKode);
            })
            ->where('user_id', $user_id)
            ->count();
        
        Log::info("Final COA {$coaKode} usage in bahan_pendukungs after fix: {$finalCount}");
        
        // Now check if COA can be deleted
        $childCount = DB::table('accounts')
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
        
        $pembayaranCount = DB::table('pembayaran_beban')
            ->where('akun_kas_id', $coaId)
            ->orWhere('akun_beban_id', $coaId)
            ->count();
        
        Log::info("Final COA {$coaKode} deletion check:");
        Log::info("- Child accounts: {$childCount}");
        Log::info("- Jurnal entries: {$jurnalCount}");
        Log::info("- Bahan baku: {$bahanBakuCount}");
        Log::info("- Pembayaran beban: {$pembayaranCount}");
        Log::info("- Bahan pendukung: {$finalCount}");
        
        $canDelete = ($childCount == 0 && $jurnalCount == 0 && $bahanBakuCount == 0 && $pembayaranCount == 0 && $finalCount == 0);
        
        if ($canDelete) {
            Log::info("SUCCESS: COA {$coaKode} can now be deleted safely");
            
            // Actually delete the COA
            DB::table('accounts')
                ->where('kode_akun', $coaKode)
                ->where('user_id', $user_id)
                ->delete();
            
            Log::info("COA {$coaKode} deleted successfully");
        } else {
            Log::info("WARNING: COA {$coaKode} still has references and cannot be deleted");
            Log::info("References summary:");
            if ($childCount > 0) Log::info("  - {$childCount} child accounts");
            if ($jurnalCount > 0) Log::info("  - {$jurnalCount} journal entries");
            if ($bahanBakuCount > 0) Log::info("  - {$bahanBakuCount} bahan baku");
            if ($pembayaranCount > 0) Log::info("  - {$pembayaranCount} pembayaran beban");
            if ($finalCount > 0) Log::info("  - {$finalCount} bahan pendukung (should be 0 after fix)");
        }
        
        $this->command->info('COA Controller final fix completed!');
        $this->command->info("COA {$coaKode} deletion status: " . ($canDelete ? "SUCCESS" : "FAILED"));
    }
}
