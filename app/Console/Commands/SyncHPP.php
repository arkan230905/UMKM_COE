<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\JournalService;

class SyncHPP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hpp:sync {--all : Sync all transactions} {--sales : Sync HPP for existing sales only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync HPP entries for existing transactions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting HPP sync process...');
        
        if ($this->option('all')) {
            $this->info('Syncing all transactions...');
            $stats = JournalService::syncAllTransactionsToJurnalUmum();
        } elseif ($this->option('sales')) {
            $this->info('Syncing HPP for existing sales...');
            $stats = JournalService::syncHPPForExistingSales();
        } else {
            $this->error('Please specify --all or --sales option');
            return 1;
        }
        
        $this->info('HPP sync completed!');
        $this->info('Synced: ' . $stats['synced']);
        $this->info('Skipped: ' . $stats['skipped']);
        $this->info('Errors: ' . $stats['errors']);
        
        return Command::SUCCESS;
    }
}
