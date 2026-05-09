<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompleteJurnalUmumBalanceSeeder extends Seeder
{
    /**
     * Complete fix for jurnal_umum table - balance ALL transactions
     * Fix all unbalanced entries for all users and transaction types
     */
    public function run(): void
    {
        Log::info('Starting CompleteJurnalUmumBalanceSeeder');
        
        $totalFixed = 0;
        $totalDeleted = 0;
        
        // Get all unbalanced entries
        $unbalancedEntries = DB::table('jurnal_umum')
            ->where(function($query) {
                $query->where('debit', 0)->orWhere('kredit', 0);
            })
            ->get();
        
        Log::info("Found {$unbalancedEntries->count()} unbalanced journal entries");
        
        foreach ($unbalancedEntries as $entry) {
            // Skip if both debit and credit are 0 (should be deleted)
            if ($entry->debit == 0 && $entry->kredit == 0) {
                DB::table('jurnal_umum')->where('id', $entry->id)->delete();
                $totalDeleted++;
                continue;
            }
            
            // Create balanced entry
            if ($entry->debit > 0 && $entry->kredit == 0) {
                // This is a debit-only entry - create corresponding credit
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
                $totalFixed++;
                
                // Delete original unbalanced entry
                DB::table('jurnal_umum')->where('id', $entry->id)->delete();
                $totalDeleted++;
                
            } elseif ($entry->kredit > 0 && $entry->debit == 0) {
                // This is a credit-only entry - create corresponding debit
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
                $totalFixed++;
                
                // Delete original unbalanced entry
                DB::table('jurnal_umum')->where('id', $entry->id)->delete();
                $totalDeleted++;
            }
        }
        
        // Verify balance after fix
        $remainingUnbalanced = DB::table('jurnal_umum')
            ->where(function($query) {
                $query->where('debit', 0)->orWhere('kredit', 0);
            })
            ->count();
        
        $totalDebit = DB::table('jurnal_umum')->sum('debit');
        $totalKredit = DB::table('jurnal_umum')->sum('kredit');
        
        Log::info("Jurnal Balance Summary:");
        Log::info("- Total Debit: Rp " . number_format($totalDebit, 2, ',', '.'));
        Log::info("- Total Kredit: Rp " . number_format($totalKredit, 2, ',', '.'));
        Log::info("- Balance: Rp " . number_format($totalDebit - $totalKredit, 2, ',', '.'));
        Log::info("- Remaining Unbalanced: {$remainingUnbalanced}");
        Log::info("- Fixed Entries: {$totalFixed}");
        Log::info("- Deleted Entries: {$totalDeleted}");
        
        if ($remainingUnbalanced == 0 && $totalDebit == $totalKredit) {
            Log::info('SUCCESS: Jurnal umum is now perfectly balanced!');
        } else {
            Log::warning('WARNING: Jurnal umum still has balance issues');
        }
        
        $this->command->info('Complete jurnal umum balance fix completed!');
        $this->command->info("Fixed: {$totalFixed} entries");
        $this->command->info("Deleted: {$totalDeleted} unbalanced entries");
        $this->command->info("Final balance: " . ($totalDebit - $totalKredit));
    }
}
