<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalEntry;

class CheckNewSale extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:new-sale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the newly created sale journal entry';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("=== CHECKING NEW SALE JOURNAL ENTRY ===");
        
        // Check entry ID 11 specifically
        $entry = JournalEntry::find(11);
        if ($entry) {
            $this->info("✅ Entry ID 11 found:");
            $this->info("  User ID: {$entry->user_id}");
            $this->info("  Ref Type: {$entry->ref_type}");
            $this->info("  Ref ID: {$entry->ref_id}");
            $this->info("  Date: {$entry->tanggal}");
            $this->info("  Memo: {$entry->memo}");
            
            // Check lines
            $lines = $entry->lines;
            $this->info("  Lines: " . $lines->count());
            
            foreach ($lines as $line) {
                $this->info("    - COA: {$line->coa->kode_akun}, Debit: {$line->debit}, Credit: {$line->credit}");
            }
        } else {
            $this->info("❌ Entry ID 11 not found");
        }
        
        // Check all sale entries
        $this->info("\n=== ALL SALE ENTRIES ===");
        $saleEntries = JournalEntry::where('ref_type', 'sale')->get();
        $this->info("Total sale entries: " . $saleEntries->count());
        
        foreach ($saleEntries as $sale) {
            $this->info("  ID: {$sale->id}, User: {$sale->user_id}, Ref: {$sale->ref_id}");
        }
        
        // Check entries for user 4
        $this->info("\n=== USER 4 ENTRIES ===");
        $user4Entries = JournalEntry::where('user_id', 4)->get();
        $this->info("User 4 entries: " . $user4Entries->count());
        
        $user4Sales = $user4Entries->where('ref_type', 'sale');
        $this->info("User 4 sales: " . $user4Sales->count());
        
        $this->info("\n=== CHECK COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
