<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Retur;

class SyncJournalWithDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-journal-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync journal entries with database transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing journal entries with database transactions...');
        
        $deletedCount = 0;
        
        // Sync purchase journals
        $this->info('');
        $this->info('Syncing purchase journals...');
        
        $purchaseJournals = JournalEntry::where('ref_type', 'purchase')->get();
        
        foreach ($purchaseJournals as $journal) {
            $pembelian = Pembelian::find($journal->ref_id);
            
            if (!$pembelian) {
                $this->line("  Removing purchase journal - Ref ID {$journal->ref_id} not found");
                $this->deleteJournalWithLines($journal);
                $deletedCount++;
            }
        }
        
        // Sync purchase_return journals
        $this->info('');
        $this->info('Syncing purchase_return journals...');
        
        $returnJournals = JournalEntry::where('ref_type', 'purchase_return')->get();
        
        foreach ($returnJournals as $journal) {
            $retur = Retur::find($journal->ref_id);
            
            if (!$retur || $retur->status !== 'posted') {
                $this->line("  Removing purchase_return journal - Ref ID {$journal->ref_id} not valid");
                $this->deleteJournalWithLines($journal);
                $deletedCount++;
            }
        }
        
        // Sync sale journals
        $this->info('');
        $this->info('Syncing sale journals...');
        
        $saleJournals = JournalEntry::where('ref_type', 'sale')->get();
        
        foreach ($saleJournals as $journal) {
            $penjualan = Penjualan::find($journal->ref_id);
            
            if (!$penjualan) {
                $this->line("  Removing sale journal - Ref ID {$journal->ref_id} not found");
                $this->deleteJournalWithLines($journal);
                $deletedCount++;
            }
        }
        
        $this->info('');
        $this->info("Sync completed! Deleted {$deletedCount} invalid journal entries");
        
        return 0;
    }
    
    /**
     * Delete journal entry and its lines
     */
    private function deleteJournalWithLines($journal)
    {
        // Delete journal lines first
        JournalLine::where('journal_entry_id', $journal->id)->delete();
        
        // Delete journal entry
        $journal->delete();
    }
}
