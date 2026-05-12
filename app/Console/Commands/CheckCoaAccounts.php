<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;

class CheckCoaAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-coa-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check COA accounts for journal entries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking COA Accounts:');
        
        // Get all accounts to see what's available
        $accounts = Coa::orderBy('kode_akun')->get();
        
        $this->info('All Available Accounts:');
        foreach ($accounts as $account) {
            $this->line($account->kode_akun . ' - ' . $account->nama_akun);
        }
        
        $this->info('');
        $this->info('Looking for specific accounts:');
        
        // Get relevant accounts
        $specificAccounts = Coa::where(function($query) {
            $query->whereIn('nama_akun', ['Kas', 'Bank', 'Persediaan Bahan Baku', 'Persediaan Bahan Pendukung', 'Hutang Usaha'])
                  ->orWhereIn('kode_akun', ['1101', '1102', '1104', '1105', '2101']);
        })->orderBy('kode_akun')->get();
        
        foreach ($specificAccounts as $account) {
            $this->line($account->kode_akun . ' - ' . $account->nama_akun);
        }
        
        // Sync COA to accounts table
        $this->info('');
        $this->info('Syncing COA to accounts table...');
        \App\Helpers\CoaValidator::validatePurchaseAccounts();
        $this->info('COA sync completed!');
        
        return 0;
    }
}
