<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixBOPJournalEntriesSeeder extends Seeder
{
    /**
     * Fix BOP journal entries - update COA kredit from '210' (Hutang) to correct BOP COA
     * Re-create BOP transfer entries with proper COA from BOP setup
     */
    public function run(): void
    {
        Log::info('Starting FixBOPJournalEntriesSeeder');
        
        $user_id = 7; // UMKM COE user_id
        $produksiId = 1; // Jasuke production ID
        
        // Get BOP proses with their COA data
        $bopProses = DB::table('bop_proses')
            ->where('user_id', $user_id)
            ->where('is_active', true)
            ->get();
        
        Log::info("Found {$bopProses->count()} active BOP proses");
        
        // Delete existing BOP transfer entries (both debit and credit)
        $deletedCount = DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_transfer')
            ->where('keterangan', 'like', 'Transfer WIP BOP%')
            ->delete();
        
        Log::info("Deleted {$deletedCount} existing BOP transfer entries");
        
        $totalCreated = 0;
        
        // Create new BOP transfer entries with correct COA
        foreach ($bopProses as $bop) {
            // Get komponen BOP data
            $komponenBop = json_decode($bop->komponen_bop, true) ?? [];
            
            if (empty($komponenBop)) {
                continue;
            }
            
            $totalBopPerProduk = $bop->total_bop_per_produk ?? 0;
            $totalBopAmount = $totalBopPerProduk * 1000; // Assuming 1000 units for this example
            
            $totalRate = array_sum(array_column($komponenBop, 'rate_per_hour'));
            
            foreach ($komponenBop as $komponen) {
                $componentName = $komponen['component'] ?? 'BOP';
                $ratePerHour = $komponen['rate_per_hour'] ?? 0;
                $coaDebit = $komponen['coa_debit'] ?? '1173';
                $coaKredit = $komponen['coa_kredit'] ?? '510';
                $description = $komponen['description'] ?? '';
                
                // Calculate proportional amount
                $componentAmount = $totalRate > 0 ? ($ratePerHour / $totalRate) * $totalBopAmount : 0;
                
                if ($componentAmount > 0) {
                    // Get COA IDs
                    $coaDebitId = $this->getCoaIdByKode($coaDebit, $user_id);
                    $coaKreditId = $this->getCoaIdByKode($coaKredit, $user_id);
                    
                    if ($coaDebitId && $coaKreditId) {
                        // Create balanced journal entry
                        DB::table('jurnal_umum')->insert([
                            [
                                'user_id' => $user_id,
                                'coa_id' => $coaDebitId,
                                'tanggal' => '2026-05-09',
                                'keterangan' => "Transfer WIP BOP - {$bop->nama_bop_proses} ({$componentName}) ke Barang Jadi",
                                'debit' => $componentAmount,
                                'kredit' => 0,
                                'referensi' => $produksiId,
                                'tipe_referensi' => 'produksi_transfer',
                                'created_by' => $user_id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ],
                            [
                                'user_id' => $user_id,
                                'coa_id' => $coaKreditId,
                                'tanggal' => '2026-05-09',
                                'keterangan' => "Transfer WIP BOP - {$bop->nama_bop_proses} ({$componentName}) ke Barang Jadi",
                                'debit' => 0,
                                'kredit' => $componentAmount,
                                'referensi' => $produksiId,
                                'tipe_referensi' => 'produksi_transfer',
                                'created_by' => $user_id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        ]);
                        
                        $totalCreated += 2;
                        Log::info("Created BOP transfer entries for {$componentName} - Debit: {$coaDebit}, Kredit: {$coaKredit}, Amount: {$componentAmount}");
                    }
                }
            }
        }
        
        Log::info("BOP journal entries fix completed!");
        Log::info("Total created entries: {$totalCreated}");
        
        $this->command->info('BOP journal entries fix completed!');
        $this->command->info("Created: {$totalCreated} balanced entries");
    }
    
    /**
     * Get COA ID by kode_akun for specific user
     */
    private function getCoaIdByKode($kodeAkun, $user_id)
    {
        $coa = DB::table('accounts')
            ->where('kode_akun', $kodeAkun)
            ->where('user_id', $user_id)
            ->first();
        
        return $coa ? $coa->id : null;
    }
}
