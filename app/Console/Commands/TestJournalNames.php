<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Account;

class TestJournalNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-journal-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test journal entries to show account names correctly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Journal Entries with Account Names:');
        
        // Get recent journal entries
        $entries = JournalEntry::with('lines.account')
            ->where('ref_type', 'purchase_return')
            ->orWhere('ref_type', 'purchase')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($entries as $entry) {
            $this->info('');
            $this->line('Entry: ' . $entry->memo . ' (' . $entry->ref_type . ' #' . $entry->ref_id . ')');
            $this->line('Tanggal: ' . $entry->tanggal);
            
            foreach ($entry->lines as $line) {
                $accountName = $line->account ? $line->account->name : 'Unknown';
                $this->line('  ' . $line->account->code . ' - ' . $accountName . 
                    ' | Debit: ' . number_format($line->debit, 2) . 
                    ' | Credit: ' . number_format($line->credit, 2));
            }
        }
        
        return 0;
    }
}
