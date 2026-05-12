<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixJurnalUmumBalanceSeeder extends Seeder
{
    /**
     * Fix jurnal_umum table - create balanced journal entries for BOP transfers
     * Each BOP component should have debit and credit in the same entry
     */
    public function run(): void
    {
        Log::info('Starting FixJurnalUmumBalanceSeeder');
        
        // Get all BOP transfer entries that need balancing
        $bopTransfers = DB::table('jurnal_umum')
            ->where('tipe_referensi', 'produksi_transfer')
            ->where('keterangan', 'like', 'Transfer WIP BOP%')
            ->where('user_id', 7) // UMKM COE user_id
            ->get();
        
        $fixedCount = 0;
        
        foreach ($bopTransfers as $entry) {
            // Skip if already balanced (both debit and credit > 0)
            if ($entry->debit > 0 && $entry->kredit > 0) {
                continue;
            }
            
            // Create balanced entry by combining debit and credit
            if ($entry->debit > 0) {
                // This is a debit entry - create corresponding credit
                $newCreditEntry = [
                    'user_id' => $entry->user_id,
                    'coa_id' => $entry->coa_id,
                    'tanggal' => $entry->tanggal,
                    'keterangan' => $entry->keterangan . ' (KREDIT)',
                    'debit' => 0,
                    'kredit' => $entry->debit,
                    'referensi' => $entry->referensi,
                    'tipe_referensi' => $entry->tipe_referensi,
                    'created_by' => $entry->created_by,
                    'created_at' => $entry->created_at,
                    'updated_at' => $entry->updated_at,
                ];
                
                DB::table('jurnal_umum')->insert($newCreditEntry);
                $fixedCount++;
            } elseif ($entry->kredit > 0) {
                // This is a credit entry - create corresponding debit
                $newDebitEntry = [
                    'user_id' => $entry->user_id,
                    'coa_id' => $entry->coa_id,
                    'tanggal' => $entry->tanggal,
                    'keterangan' => $entry->keterangan . ' (DEBIT)',
                    'debit' => $entry->kredit,
                    'kredit' => 0,
                    'referensi' => $entry->referensi,
                    'tipe_referensi' => $entry->tipe_referensi,
                    'created_by' => $entry->created_by,
                    'created_at' => $entry->created_at,
                    'updated_at' => $entry->updated_at,
                ];
                
                DB::table('jurnal_umum')->insert($newDebitEntry);
                $fixedCount++;
            }
        }
        
        // Delete the original unbalanced entries
        $deletedCount = DB::table('jurnal_umum')
            ->where('tipe_referensi', 'produksi_transfer')
            ->where('keterangan', 'like', 'Transfer WIP BOP%')
            ->where('user_id', 7)
            ->where(function($query) {
                $query->where('debit', 0)->orWhere('kredit', 0);
            })
            ->delete();
        
        Log::info("Fixed {$fixedCount} journal entries for BOP transfers");
        Log::info("Deleted {$deletedCount} unbalanced journal entries");
        Log::info('Jurnal umum balancing completed!');
        
        $this->command->info('Jurnal umum balance fix completed!');
        $this->command->info("Fixed: {$fixedCount} entries");
        $this->command->info("Deleted: {$deletedCount} unbalanced entries");
    }
}
