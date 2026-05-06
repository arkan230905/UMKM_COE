<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;

class CheckPurchaseJournal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:purchase-journal {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check purchase transactions and their journal entries';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== CHECKING PURCHASE TRANSACTIONS FOR USER ID: {$userId} ===");
        
        // Check user
        $user = User::find($userId);
        if (!$user) {
            $this->error("User ID {$userId} not found");
            return Command::FAILURE;
        }
        
        $this->info("User: {$user->name}");
        
        // Get all pembelian for this user
        $pembelians = Pembelian::where('user_id', $userId)->get();
        $this->info("\n=== PEMBELIAN TRANSACTIONS ===");
        $this->info("Total pembelian: " . $pembelians->count());
        
        foreach ($pembelians as $pembelian) {
            $this->info("\n📋 Pembelian ID: {$pembelian->id}");
            $this->info("  Nomor: {$pembelian->nomor_pembelian}");
            $this->info("  Tanggal: {$pembelian->tanggal}");
            $this->info("  Total: Rp " . number_format($pembelian->total, 2));
            $this->info("  User ID: {$pembelian->user_id}");
            
            // Check journal entries for this pembelian
            $journalEntries = JournalEntry::where('ref_type', 'purchase')
                ->where('ref_id', $pembelian->id)
                ->get();
                
            $this->info("  📊 Journal Entries: " . $journalEntries->count());
            
            foreach ($journalEntries as $entry) {
                $this->info("    Entry ID: {$entry->id}");
                $this->info("    User ID: " . ($entry->user_id ?? 'NULL'));
                $this->info("    Date: {$entry->tanggal}");
                $this->info("    Memo: {$entry->memo}");
                
                // Check journal lines
                $lines = JournalLine::where('journal_entry_id', $entry->id)
                    ->with('coa')
                    ->get();
                    
                $this->info("    Lines: " . $lines->count());
                foreach ($lines as $line) {
                    $this->info("      - {$line->coa->kode_akun} ({$line->coa->nama_akun}): Debit={$line->debit}, Credit={$line->credit}");
                }
            }
        }
        
        // Check all purchase journal entries for user
        $this->info("\n=== ALL PURCHASE JOURNAL ENTRIES FOR USER {$userId} ===");
        $allPurchaseEntries = JournalEntry::where('ref_type', 'purchase')
            ->where('user_id', $userId)
            ->get();
            
        $this->info("Total purchase journal entries: " . $allPurchaseEntries->count());
        
        foreach ($allPurchaseEntries as $entry) {
            $this->info("  Entry ID: {$entry->id}, Ref ID: {$entry->ref_id}, Date: {$entry->tanggal}");
        }
        
        // Check purchase entries with null user_id
        $this->info("\n=== PURCHASE ENTRIES WITH NULL USER_ID ===");
        $nullUserEntries = JournalEntry::where('ref_type', 'purchase')
            ->whereNull('user_id')
            ->get();
            
        $this->info("Purchase entries with null user_id: " . $nullUserEntries->count());
        
        foreach ($nullUserEntries as $entry) {
            $this->info("  Entry ID: {$entry->id}, Ref ID: {$entry->ref_id}");
        }
        
        $this->info("\n=== CHECK COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
