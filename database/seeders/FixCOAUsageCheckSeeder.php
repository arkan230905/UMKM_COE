<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixCOAUsageCheckSeeder extends Seeder
{
    /**
     * Check and fix COA 530 usage issue
     * Find which tables are using COA 530 and fix orphaned entries
     */
    public function run(): void
    {
        Log::info('Starting FixCOAUsageCheckSeeder');
        
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
        
        Log::info("Checking usage of COA {$coaKode} (ID: {$coaId})");
        
        // Check usage in bahan_bakus
        $bahanBakuCount = DB::table('bahan_bakus')
            ->where('coa_persediaan_id', $coaKode)
            ->orWhere('coa_hpp_id', $coaKode)
            ->orWhere('coa_pembelian_id', $coaKode)
            ->count();
        
        Log::info("COA {$coaKode} usage in bahan_bakus: {$bahanBakuCount}");
        
        // Check usage in jurnal_umum
        $jurnalCount = DB::table('jurnal_umum')
            ->where('coa_id', $coaId)
            ->count();
        
        Log::info("COA {$coaKode} usage in jurnal_umum: {$jurnalCount}");
        
        // Check usage in bop_proses (JSON field)
        $bopProsesList = DB::table('bop_proses')
            ->where('user_id', $user_id)
            ->get();
        
        $foundInBOP = false;
        foreach ($bopProsesList as $proses) {
            $komponenBop = json_decode($proses->komponen_bop, true) ?? [];
            
            if (is_array($komponenBop)) {
                foreach ($komponenBop as $komponen) {
                    if ((isset($komponen['coa_debit']) && $komponen['coa_debit'] == $coaKode) ||
                        (isset($komponen['coa_kredit']) && $komponen['coa_kredit'] == $coaKode)) {
                        Log::info("COA {$coaKode} found in BOP proses: {$proses->nama_bop_proses} - {$komponen['component']}");
                        $foundInBOP = true;
                    }
                }
            }
        }
        
        Log::info("COA {$coaKode} usage in BOP proses: " . ($foundInBOP ? "YES" : "NO"));
        
        // Show journal entries using COA 530
        if ($jurnalCount > 0) {
            $journalEntries = DB::table('jurnal_umum')
                ->where('coa_id', $coaId)
                ->limit(10)
                ->get(['id', 'keterangan', 'debit', 'kredit', 'tipe_referensi']);
            
            Log::info("Journal entries using COA {$coaKode}:");
            foreach ($journalEntries as $entry) {
                Log::info("  ID: {$entry->id} - {$entry->keterangan} - {$entry->tipe_referensi} - Debit: {$entry->debit} - Kredit: {$entry->kredit}");
            }
        }
        
        // If COA 530 is not used in BOP proses but has journal entries, 
        // it means there are orphaned journal entries that need to be cleaned up
        if (!$foundInBOP && $jurnalCount > 0) {
            Log::info("COA {$coaKode} has {$jurnalCount} journal entries but not used in BOP proses");
            Log::info("This suggests there are orphaned journal entries that need cleanup");
            
            // Option 1: Clean up orphaned journal entries
            // Uncomment the following lines to delete orphaned journal entries
            /*
            DB::table('jurnal_umum')
                ->where('coa_id', $coaId)
                ->where('user_id', $user_id)
                ->delete();
            
            Log::info("Deleted {$jurnalCount} orphaned journal entries for COA {$coaKode}");
            */
        }
        
        Log::info("COA Usage Check Summary:");
        Log::info("- COA Kode: {$coaKode}");
        Log::info("- Bahan Baku: {$bahanBakuCount}");
        Log::info("- Jurnal Umum: {$jurnalCount}");
        Log::info("- BOP Proses: " . ($foundInBOP ? "YES" : "NO"));
        Log::info("- Can Delete: " . (($bahanBakuCount == 0 && $jurnalCount == 0 && !$foundInBOP) ? "YES" : "NO"));
        
        $this->command->info('COA usage check completed!');
        $this->command->info("COA {$coaKode} can be deleted: " . (($bahanBakuCount == 0 && $jurnalCount == 0 && !$foundInBOP) ? "YES" : "NO"));
    }
}
