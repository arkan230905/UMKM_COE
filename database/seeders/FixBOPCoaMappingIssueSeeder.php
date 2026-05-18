<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixBOPCoaMappingIssueSeeder extends Seeder
{
    /**
     * Fix BOP COA mapping issue where Keju component uses BOP - Susu (531) instead of BOP - Keju (533)
     * This seeder corrects the journal entries and ensures proper COA mapping
     */
    public function run(): void
    {
        Log::info('Starting FixBOPCoaMappingIssueSeeder');
        
        $user_id = 7; // Adjust this to your user ID
        
        // Step 1: Find all journal entries with incorrect BOP - Susu for Keju components
        $incorrectEntries = DB::table('jurnal_umum')
            ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
            ->where('jurnal_umum.user_id', $user_id)
            ->where('coas.kode_akun', '531') // BOP - Susu
            ->where('jurnal_umum.keterangan', 'LIKE', '%Keju%')
            ->where('jurnal_umum.tipe_referensi', 'produksi_bop')
            ->select('jurnal_umum.*', 'coas.nama_akun as current_coa_name')
            ->get();
        
        if ($incorrectEntries->count() > 0) {
            Log::info("Found {$incorrectEntries->count()} incorrect journal entries using BOP - Susu for Keju components");
            
            // Get COA ID for BOP - Keju (533)
            $kejuCoaId = $this->getCoaIdByKode('533', $user_id);
            
            if (!$kejuCoaId) {
                // Create BOP - Keju COA if it doesn't exist
                $kejuCoaId = DB::table('coas')->insertGetId([
                    'user_id' => $user_id,
                    'kode_akun' => '533',
                    'nama_akun' => 'BOP - Keju',
                    'tipe_akun' => 'Biaya',
                    'saldo_normal' => 'debit',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Log::info("Created new COA 533: BOP - Keju with ID: {$kejuCoaId}");
            }
            
            // Step 2: Update incorrect journal entries to use BOP - Keju
            foreach ($incorrectEntries as $entry) {
                DB::table('jurnal_umum')
                    ->where('id', $entry->id)
                    ->update([
                        'coa_id' => $kejuCoaId,
                        'keterangan' => str_replace('BOP - Susu', 'BOP - Keju', $entry->keterangan),
                        'updated_at' => now(),
                    ]);
                
                Log::info("Updated journal entry ID {$entry->id}: Changed from COA 531 (BOP - Susu) to COA 533 (BOP - Keju)");
                Log::info("  - Amount: Rp " . number_format($entry->kredit, 0, ',', '.'));
                Log::info("  - Old description: {$entry->keterangan}");
                Log::info("  - New description: " . str_replace('BOP - Susu', 'BOP - Keju', $entry->keterangan));
            }
        } else {
            Log::info('No incorrect journal entries found - BOP COA mapping is already correct');
        }
        
        // Step 3: Verify the fix by checking current journal entries
        $this->verifyBopCoaMapping($user_id);
        
        $this->command->info('BOP COA mapping issue has been fixed successfully!');
        $this->command->info("Updated {$incorrectEntries->count()} journal entries");
    }
    
    /**
     * Verify BOP COA mapping is correct
     */
    private function verifyBopCoaMapping($user_id)
    {
        Log::info('Verifying BOP COA mapping...');
        
        // Check Susu components
        $susuEntries = DB::table('jurnal_umum')
            ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
            ->where('jurnal_umum.user_id', $user_id)
            ->where('coas.kode_akun', '531') // BOP - Susu
            ->where('jurnal_umum.keterangan', 'LIKE', '%Susu%')
            ->where('jurnal_umum.tipe_referensi', 'produksi_bop')
            ->count();
        
        // Check Keju components
        $kejuEntries = DB::table('jurnal_umum')
            ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
            ->where('jurnal_umum.user_id', $user_id)
            ->where('coas.kode_akun', '533') // BOP - Keju
            ->where('jurnal_umum.keterangan', 'LIKE', '%Keju%')
            ->where('jurnal_umum.tipe_referensi', 'produksi_bop')
            ->count();
        
        // Check for any remaining incorrect mappings
        $incorrectSusuForKeju = DB::table('jurnal_umum')
            ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
            ->where('jurnal_umum.user_id', $user_id)
            ->where('coas.kode_akun', '531') // BOP - Susu
            ->where('jurnal_umum.keterangan', 'LIKE', '%Keju%')
            ->where('jurnal_umum.tipe_referensi', 'produksi_bop')
            ->count();
        
        $incorrectKejuForSusu = DB::table('jurnal_umum')
            ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
            ->where('jurnal_umum.user_id', $user_id)
            ->where('coas.kode_akun', '533') // BOP - Keju
            ->where('jurnal_umum.keterangan', 'LIKE', '%Susu%')
            ->where('jurnal_umum.tipe_referensi', 'produksi_bop')
            ->count();
        
        Log::info("Verification Results:");
        Log::info("- Susu components using COA 531 (BOP - Susu): {$susuEntries} entries ✓");
        Log::info("- Keju components using COA 533 (BOP - Keju): {$kejuEntries} entries ✓");
        Log::info("- Incorrect: Keju using COA 531 (BOP - Susu): {$incorrectSusuForKeju} entries " . ($incorrectSusuForKeju == 0 ? '✓' : '❌'));
        Log::info("- Incorrect: Susu using COA 533 (BOP - Keju): {$incorrectKejuForSusu} entries " . ($incorrectKejuForSusu == 0 ? '✓' : '❌'));
        
        if ($incorrectSusuForKeju == 0 && $incorrectKejuForSusu == 0) {
            Log::info('✅ BOP COA mapping verification PASSED - All mappings are correct!');
        } else {
            Log::warning('❌ BOP COA mapping verification FAILED - Some incorrect mappings still exist');
        }
    }
    
    /**
     * Get COA ID by kode_akun for specific user
     */
    private function getCoaIdByKode($kodeAkun, $user_id)
    {
        $coa = DB::table('coas')
            ->where('kode_akun', $kodeAkun)
            ->where('user_id', $user_id)
            ->first();
        
        return $coa ? $coa->id : null;
    }
}