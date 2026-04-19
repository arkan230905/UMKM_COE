<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\JournalService;

class SyncJournalToLedger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'journal:sync-to-ledger {--force : Force sync even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all transactions from Journal Entries to Jurnal Umum (General Ledger)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting journal to ledger synchronization...');
        
        if (!$this->option('force')) {
            if (!$this->confirm('This will recreate all journal entries in Jurnal Umum. Continue?')) {
                $this->info('Synchronization cancelled.');
                return 0;
            }
        }
        
        // Clear existing Jurnal Umum entries if force option is used
        if ($this->option('force')) {
            $this->info('Clearing existing Jurnal Umum entries...');
            \App\Models\JurnalUmum::truncate();
        }
        
        // Sync all transactions
        $stats = JournalService::syncAllTransactionsToJurnalUmum();
        
        $this->info('Synchronization completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Transactions Synced', $stats['synced']],
                ['Errors', $stats['errors']],
                ['Skipped', $stats['skipped']],
            ]
        );
        
        if ($stats['errors'] > 0) {
            $this->warn('Some transactions had errors. Check the logs for details.');
        }
        
        // Verify sync
        $jurnalUmumCount = \App\Models\JurnalUmum::count();
        $this->info("Total entries in Jurnal Umum: {$jurnalUmumCount}");
        
        return 0;
    }
}