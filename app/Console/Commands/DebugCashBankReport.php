<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalLine;
use App\Models\JournalEntry;
use App\Models\Account;

class DebugCashBankReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-cash-bank-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug cash bank report for return transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Debugging Cash Bank Report...');
        
        // 1. Check available cash/bank accounts
        $this->info('');
        $this->info('1. Available Cash/Bank Accounts:');
        $cashBankAccounts = Account::whereIn('code', ['101', '102', '1101', '1102'])->get();
        
        foreach ($cashBankAccounts as $account) {
            $this->line('  ' . $account->code . ' - ' . $account->name);
        }
        
        // 2. Check journal entries for returns
        $this->info('');
        $this->info('2. Journal Entries for Returns:');
        $returnEntries = JournalEntry::where('ref_type', 'purchase_return')
            ->with('lines.account')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        foreach ($returnEntries as $entry) {
            $this->line('  Entry #' . $entry->id . ' - ' . $entry->memo . ' (' . $entry->tanggal . ')');
            
            foreach ($entry->lines as $line) {
                $accountName = $line->account ? $line->account->name : 'Unknown';
                $this->line('    ' . $line->account->code . ' - ' . $accountName . 
                    ' | Debit: ' . number_format($line->debit, 2) . 
                    ' | Credit: ' . number_format($line->credit, 2));
            }
        }
        
        // 3. Calculate cash inflow from returns
        $this->info('');
        $this->info('3. Cash Inflow from Returns:');
        
        $kasAccount = Account::where('code', '101')->first();
        if ($kasAccount) {
            $cashInflow = JournalLine::where('account_id', $kasAccount->id)
                ->whereHas('entry', function($query) {
                    $query->where('ref_type', 'purchase_return');
                })
                ->sum('debit');
                
            $this->line('  Total cash inflow from returns: Rp ' . number_format($cashInflow, 2));
        }
        
        // 4. Check all cash transactions (for comparison)
        $this->info('');
        $this->info('4. All Cash Transactions (Recent):');
        
        if ($kasAccount) {
            $allCashTransactions = JournalLine::where('account_id', $kasAccount->id)
                ->with('entry')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
                
            foreach ($allCashTransactions as $line) {
                $type = $line->debit > 0 ? 'IN' : 'OUT';
                $amount = $line->debit > 0 ? $line->debit : $line->credit;
                $this->line('  ' . $line->entry->tanggal . ' - ' . $line->entry->memo . 
                    ' (' . $line->entry->ref_type . ') - ' . $type . ': Rp ' . number_format($amount, 2));
            }
        }
        
        return 0;
    }
}
