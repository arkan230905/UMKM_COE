<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalLine;
use App\Models\JournalEntry;
use App\Models\Coa;

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
        $cashBankAccounts = Coa::whereIn('kode_akun', ['101', '102', '1101', '1102'])->get();
        
        foreach ($cashBankAccounts as $coa) {
            $this->line('  ' . $coa->kode_akun . ' - ' . $coa->nama_akun);
        }
        
        // 2. Check journal entries for returns
        $this->info('');
        $this->info('2. Journal Entries for Returns:');
        $returnEntries = JournalEntry::where('ref_type', 'purchase_return')
            ->with('lines.coa')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        foreach ($returnEntries as $entry) {
            $this->line('  Entry #' . $entry->id . ' - ' . $entry->memo . ' (' . $entry->tanggal . ')');
            
            foreach ($entry->lines as $line) {
                $coaName = $line->coa ? $line->coa->nama_akun : 'Unknown';
                $this->line('    ' . $line->coa->kode_akun . ' - ' . $coaName . 
                    ' | Debit: ' . number_format($line->debit, 2) . 
                    ' | Credit: ' . number_format($line->credit, 2));
            }
        }
        
        // 3. Calculate cash inflow from returns
        $this->info('');
        $this->info('3. Cash Inflow from Returns:');
        
        $kasCoa = Coa::where('kode_akun', '101')->first();
        if ($kasCoa) {
            $cashInflow = JournalLine::where('coa_id', $kasCoa->id)
                ->whereHas('entry', function($query) {
                    $query->where('ref_type', 'purchase_return');
                })
                ->sum('debit');
                
            $this->line('  Total cash inflow from returns: Rp ' . number_format($cashInflow, 2));
        }
        
        // 4. Check all cash transactions (for comparison)
        $this->info('');
        $this->info('4. All Cash Transactions (Recent):');
        
        if ($kasCoa) {
            $allCashTransactions = JournalLine::where('coa_id', $kasCoa->id)
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
