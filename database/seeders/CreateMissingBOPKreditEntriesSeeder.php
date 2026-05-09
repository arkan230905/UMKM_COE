<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateMissingBOPKreditEntriesSeeder extends Seeder
{
    /**
     * Create missing BOP kredit entries for Transfer WIP ke Barang Jadi
     * Each BOP kredit entry should have corresponding credit entry
     */
    public function run(): void
    {
        Log::info('Starting CreateMissingBOPKreditEntriesSeeder');
        
        $user_id = 7; // UMKM COE user_id
        $produksiId = 1; // Jasuke production ID
        
        // Get existing BOP kredit entries (debit only)
        $existingBOPKredits = DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_transfer')
            ->where('keterangan', 'like', 'Transfer WIP BOP%')
            ->where('debit', '>', 0)
            ->where('kredit', 0)
            ->get();
        
        Log::info("Found {$existingBOPKredits->count()} BOP debit entries that need corresponding credit entries");
        
        $totalCreated = 0;
        
        foreach ($existingBOPKredits as $debitEntry) {
            // Extract COA info from keterangan
            $keterangan = $debitEntry->keterangan;
            $coaId = $debitEntry->coa_id;
            $amount = $debitEntry->debit;
            $tanggal = $debitEntry->tanggal;
            
            // Create corresponding credit entry
            $creditEntry = [
                'user_id' => $user_id,
                'coa_id' => $coaId,
                'tanggal' => $tanggal,
                'keterangan' => $keterangan . ' (KREDIT)',
                'debit' => 0,
                'kredit' => $amount,
                'referensi' => $produksiId,
                'tipe_referensi' => 'produksi_transfer',
                'created_by' => $user_id,
                'created_at' => $debitEntry->created_at,
                'updated_at' => $debitEntry->updated_at,
            ];
            
            DB::table('jurnal_umum')->insert($creditEntry);
            $totalCreated++;
            
            Log::info("Created BOP credit entry: {$keterangan} - Amount: {$amount}");
        }
        
        // Verify balance after creation
        $totalDebit = DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_transfer')
            ->where('keterangan', 'like', 'Transfer WIP BOP%')
            ->sum('debit');
            
        $totalKredit = DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_transfer')
            ->where('keterangan', 'like', 'Transfer WIP BOP%')
            ->sum('kredit');
        
        Log::info("BOP Transfer Balance Summary:");
        Log::info("- Total Debit: Rp " . number_format($totalDebit, 2, ',', '.'));
        Log::info("- Total Kredit: Rp " . number_format($totalKredit, 2, ',', '.'));
        Log::info("- Balance: Rp " . number_format($totalDebit - $totalKredit, 2, ',', '.'));
        Log::info("- Created Credit Entries: {$totalCreated}");
        
        if ($totalDebit == $totalKredit) {
            Log::info('SUCCESS: BOP transfer entries are now balanced!');
        } else {
            Log::warning('WARNING: BOP transfer entries still not balanced');
        }
        
        $this->command->info('Missing BOP kredit entries creation completed!');
        $this->command->info("Created: {$totalCreated} credit entries");
        $this->command->info("Final balance: " . ($totalDebit - $totalKredit));
    }
}
