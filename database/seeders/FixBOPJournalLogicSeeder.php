<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixBOPJournalLogicSeeder extends Seeder
{
    /**
     * Fix BOP journal logic - change from "Transfer WIP ke Barang Jadi" to "Alokasi BOP ke Barang WIP BOP"
     * Delete incorrect transfer entries and create correct BOP allocation entries
     */
    public function run(): void
    {
        Log::info('Starting FixBOPJournalLogicSeeder');
        
        $user_id = 7; // UMKM COE user_id
        $produksiId = 1; // Jasuke production ID
        
        // Delete all incorrect "Transfer WIP BOP" entries
        $deletedCount = DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_transfer')
            ->where('keterangan', 'like', 'Transfer WIP BOP%')
            ->delete();
        
        Log::info("Deleted {$deletedCount} incorrect 'Transfer WIP BOP' entries");
        
        // Get BOP proses with their COA data
        $bopProses = DB::table('bop_proses')
            ->where('user_id', $user_id)
            ->where('is_active', true)
            ->get();
        
        Log::info("Found {$bopProses->count()} active BOP proses");
        
        $totalCreated = 0;
        
        // Create correct BOP allocation entries
        foreach ($bopProses as $bop) {
            // Get komponen BOP data
            $komponenBop = json_decode($bop->komponen_bop, true) ?? [];
            
            if (empty($komponenBop)) {
                continue;
            }
            
            $totalBopPerProduk = $bop->total_bop_per_produk ?? 0;
            $totalBopAmount = $totalBopPerProduk * 1000; // Assuming 1000 units
            
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
                        // Create balanced BOP allocation entries
                        DB::table('jurnal_umum')->insert([
                            [
                                'user_id' => $user_id,
                                'coa_id' => $coaDebitId,
                                'tanggal' => '2026-05-09',
                                'keterangan' => "Alokasi BOP - {$bop->nama_bop_proses} ({$componentName}) ke Barang WIP BOP",
                                'debit' => $componentAmount,
                                'kredit' => 0,
                                'referensi' => $produksiId,
                                'tipe_referensi' => 'produksi_bop',
                                'created_by' => $user_id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ],
                            [
                                'user_id' => $user_id,
                                'coa_id' => $coaKreditId,
                                'tanggal' => '2026-05-09',
                                'keterangan' => "Alokasi BOP - {$bop->nama_bop_proses} ({$componentName}) ke Barang WIP BOP",
                                'debit' => 0,
                                'kredit' => $componentAmount,
                                'referensi' => $produksiId,
                                'tipe_referensi' => 'produksi_bop',
                                'created_by' => $user_id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        ]);
                        
                        $totalCreated += 2;
                        Log::info("Created BOP allocation entries for {$componentName} - Debit: {$coaDebit}, Kredit: {$coaKredit}, Amount: {$componentAmount}");
                    }
                }
            }
        }
        
        // Verify balance after creation
        $totalDebit = DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_bop')
            ->sum('debit');
            
        $totalKredit = DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_bop')
            ->sum('kredit');
        
        Log::info("BOP Allocation Balance Summary:");
        Log::info("- Total Debit: Rp " . number_format($totalDebit, 2, ',', '.'));
        Log::info("- Total Kredit: Rp " . number_format($totalKredit, 2, ',', '.'));
        Log::info("- Balance: Rp " . number_format($totalDebit - $totalKredit, 2, ',', '.'));
        Log::info("- Created Entries: {$totalCreated}");
        
        if ($totalDebit == $totalKredit) {
            Log::info('SUCCESS: BOP allocation entries are balanced!');
        } else {
            Log::warning('WARNING: BOP allocation entries still not balanced');
        }
        
        $this->command->info('BOP journal logic fix completed!');
        $this->command->info("Deleted: {$deletedCount} incorrect entries");
        $this->command->info("Created: {$totalCreated} balanced entries");
        $this->command->info("Final balance: " . ($totalDebit - $totalKredit));
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
