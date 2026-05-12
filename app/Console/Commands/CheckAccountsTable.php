<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;

class CheckAccountsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-accounts-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check accounts table for journal entries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Accounts Table:');
        
        $accounts = Account::whereIn('code', ['101', '102', '1105'])->get();
        
        foreach ($accounts as $account) {
            $this->line($account->code . ' - ' . $account->name . ' (' . $account->type . ')');
        }
        
        return 0;
    }
}
