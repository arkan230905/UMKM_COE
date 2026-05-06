<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalEntry;
use App\Models\JurnalUmum;
use App\Models\Penjualan;

class FixSaleUserIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:sale-user-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix null user_id in existing sale journal entries';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("=== FIXING NULL USER_ID IN SALE JOURNAL ENTRIES ===");
        
        // Get all sale entries with null user_id
        $saleEntries = JournalEntry::where('ref_type', 'sale')
            ->whereNull('user_id')
            ->get();
            
        $this->info("Found {$saleEntries->count()} sale entries with null user_id");
        
        $fixedCount = 0;
        $errorCount = 0;
        
        foreach ($saleEntries as $entry) {
            try {
                // Get the penjualan to find the correct user_id
                $penjualan = Penjualan::find($entry->ref_id);
                
                if ($penjualan && $penjualan->user_id) {
                    // Update journal entry
                    $entry->user_id = $penjualan->user_id;
                    $entry->save();
                    
                    // Update corresponding jurnal_umum entries
                    $jurnalUmumEntries = JurnalUmum::where('tipe_referensi', 'sale')
                        ->where('referensi', 'sale#' . $entry->ref_id)
                        ->whereNull('user_id')
                        ->get();
                        
                    foreach ($jurnalUmumEntries as $ju) {
                        $ju->user_id = $penjualan->user_id;
                        $ju->save();
                    }
                    
                    $this->info("✅ Fixed Entry ID {$entry->id} -> User ID {$penjualan->user_id}");
                    $fixedCount++;
                } else {
                    $this->info("❌ Cannot fix Entry ID {$entry->id} - Penjualan not found or has no user_id");
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $this->info("❌ Error fixing Entry ID {$entry->id}: " . $e->getMessage());
                $errorCount++;
            }
        }
        
        $this->info("\n=== SUMMARY ===");
        $this->info("Fixed: {$fixedCount} entries");
        $this->info("Errors: {$errorCount} entries");
        
        // Verify the fix
        $this->info("\n=== VERIFICATION ===");
        $remainingNullEntries = JournalEntry::where('ref_type', 'sale')
            ->whereNull('user_id')
            ->count();
            
        $this->info("Remaining null user_id entries: {$remainingNullEntries}");
        
        // Show all sale entries now
        $allSaleEntries = JournalEntry::where('ref_type', 'sale')->get();
        $this->info("Total sale entries: " . $allSaleEntries->count());
        
        foreach ($allSaleEntries as $entry) {
            $this->info("  ID: {$entry->id}, User: {$entry->user_id}, Ref: {$entry->ref_id}");
        }
        
        $this->info("\n=== FIX COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
