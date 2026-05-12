<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Retur;

class CleanupOrphanedJournals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-orphaned-journals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up journal entries that don\'t have corresponding transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up orphaned journal entries...');
        
        $deletedJournals = 0;
        $deletedLines = 0;
        
        // Clean up purchase_return journals
        $this->info('');
        $this->info('Checking purchase_return journals...');
        
        $purchaseReturnJournals = JournalEntry::where('ref_type', 'purchase_return')->get();
        
        foreach ($purchaseReturnJournals as $journal) {
            $retur = Retur::find($journal->ref_id);
            
            if (!$retur || $retur->status !== 'posted') {
                $this->line("  Deleting orphaned journal: {$journal->memo} (Ref ID: {$journal->ref_id})");
                
                // Delete journal lines first
                $linesDeleted = JournalLine::where('journal_entry_id', $journal->id)->delete();
                $deletedLines += $linesDeleted;
                
                // Delete journal entry
                $journal->delete();
                $deletedJournals++;
            }
        }
        
        // Clean up purchase journals
        $this->info('');
        $this->info('Checking purchase journals...');
        
        $purchaseJournals = JournalEntry::where('ref_type', 'purchase')->get();
        
        foreach ($purchaseJournals as $journal) {
            $pembelian = Pembelian::find($journal->ref_id);
            
            if (!$pembelian) {
                $this->line("  Deleting orphaned journal: {$journal->memo} (Ref ID: {$journal->ref_id})");
                
                // Delete journal lines first
                $linesDeleted = JournalLine::where('journal_entry_id', $journal->id)->delete();
                $deletedLines += $linesDeleted;
                
                // Delete journal entry
                $journal->delete();
                $deletedJournals++;
            }
        }
        
        // Clean up sale journals
        $this->info('');
        $this->info('Checking sale journals...');
        
        $saleJournals = JournalEntry::where('ref_type', 'sale')->get();
        
        foreach ($saleJournals as $journal) {
            $penjualan = Penjualan::find($journal->ref_id);
            
            if (!$penjualan) {
                $this->line("  Deleting orphaned journal: {$journal->memo} (Ref ID: {$journal->ref_id})");
                
                // Delete journal lines first
                $linesDeleted = JournalLine::where('journal_entry_id', $journal->id)->delete();
                $deletedLines += $linesDeleted;
                
                // Delete journal entry
                $journal->delete();
                $deletedJournals++;
            }
        }
        
        $this->info('');
        $this->info('Cleanup completed!');
        $this->info("Deleted {$deletedJournals} journal entries and {$deletedLines} journal lines");
        
        // Show remaining journals
        $this->info('');
        $this->info('Remaining journals:');
        
        $remainingJournals = JournalEntry::with('lines')
            ->whereIn('ref_type', ['purchase', 'purchase_return', 'sale'])
            ->orderBy('tanggal')
            ->get();
            
        foreach ($remainingJournals as $journal) {
            $this->line("  {$journal->tanggal} - {$journal->ref_type}#{$journal->ref_id} - {$journal->memo}");
            foreach ($journal->lines as $line) {
                $this->line("    Account {$line->account_id}: Debit {$line->debit} / Credit {$line->credit}");
            }
        }
        
        return 0;
    }
}
