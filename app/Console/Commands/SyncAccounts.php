<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\JournalService;

class SyncAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:sync {--fix-names : Fix generic account names from COA}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync COA accounts to Account table and fix generic names';

    /**
     * Execute the console command.
     */
    public function handle(JournalService $journalService): int
    {
        $this->info('Starting account synchronization...');
        
        // Sync COA to Accounts
        $this->info('Syncing COA to Account table...');
        $stats = $journalService->syncCoaToAccounts();
        
        $this->info("✅ Created: {$stats['created']} accounts");
        $this->info("✅ Updated: {$stats['updated']} accounts");
        $this->info("⏭️  Skipped: {$stats['skipped']} accounts");
        
        // Fix generic names if requested
        if ($this->option('fix-names')) {
            $this->info('Fixing generic account names...');
            $nameStats = $journalService->ensureAccountNames();
            $this->info("✅ Fixed names for: {$nameStats['updated']} accounts");
        }
        
        $this->info('Account synchronization completed successfully!');
        
        return Command::SUCCESS;
    }
}
