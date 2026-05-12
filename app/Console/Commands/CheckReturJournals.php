<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Retur;

class CheckReturJournals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-retur-journals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check journal entries for retur transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Journal Entries for Retur Transactions...');
        
        // Check all retur records
        $returs = Retur::all();
        $this->info('');
        $this->info('Total Retur Records: ' . $returs->count());
        
        foreach ($returs as $retur) {
            $this->info('');
            $this->info('Retur ID: ' . $retur->id);
            $this->line('  Type: ' . $retur->type);
            $this->line('  Status: ' . $retur->status);
            $this->line('  Jumlah: ' . $retur->jumlah);
        }
        
        // Check journal entries with retur reference
        $this->info('');
        $this->info('Journal Entries with Retur Reference:');
        
        $journals = JournalEntry::where('ref_type', 'retur')
            ->orWhere('memo', 'like', '%Retur%')
            ->orWhere('memo', 'like', '%retur%')
            ->get();
        
        $this->info('Found ' . $journals->count() . ' journal entries');
        
        foreach ($journals as $journal) {
            $this->info('');
            $this->info('Journal ID: ' . $journal->id);
            $this->line('  Ref Type: ' . ($journal->ref_type ?? 'NULL'));
            $this->line('  Ref ID: ' . ($journal->ref_id ?? 'NULL'));
            $this->line('  Memo: ' . ($journal->memo ?? 'NULL'));
            $this->line('  Tanggal: ' . $journal->tanggal);
            
            // Check journal lines
            $lines = JournalLine::where('journal_entry_id', $journal->id)->get();
            foreach ($lines as $line) {
                $this->line('    Line: Account ' . $line->account_id . ' - Debit: ' . $line->debit . ' - Credit: ' . $line->credit);
            }
        }
        
        return 0;
    }
}
