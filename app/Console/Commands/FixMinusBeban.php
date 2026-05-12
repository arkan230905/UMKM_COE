<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalLine;
use App\Models\JournalEntry;

class FixMinusBeban extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:minus-beban {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix minus beban values by correcting debit/credit';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== FIXING MINUS BEBAN FOR USER ID: {$userId} ===");
        
        // Fix all problematic beban lines (not just COA 53)
        $problematicLines = JournalLine::whereHas('entry', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereHas('coa', function($query) {
            $query->where('tipe_akun', 'Expense')
                  ->orWhere('tipe_akun', 'expense')
                  ->orWhere('tipe_akun', 'Beban')
                  ->orWhere('tipe_akun', 'Biaya');
        })->where('debit', 0)
        ->where('credit', '>', 0)
        ->with(['entry', 'coa'])
        ->get();
        
        $this->info("📊 Found {$problematicLines->count()} problematic beban lines:");
        
        foreach ($problematicLines as $line) {
            $this->info("\n📊 Fixing journal line:");
            $this->info("  Journal Line ID: {$line->id}");
            $this->info("  Entry ID: {$line->entry->id}");
            $this->info("  Date: {$line->entry->tanggal}");
            $this->info("  Ref: {$line->entry->ref_type}-{$line->entry->ref_id}");
            $this->info("  COA: {$line->coa->kode_akun} - {$line->coa->nama_akun}");
            $this->info("  Current: Debit={$line->debit}, Credit={$line->credit}");
            
            // Fix the debit/credit
            $originalDebit = $line->debit;
            $originalCredit = $line->credit;
            
            $line->debit = $originalCredit;
            $line->credit = $originalDebit;
            $line->save();
            
            $this->info("  ✅ Fixed: Debit={$line->debit}, Credit={$line->credit}");
            
            // Verify the fix for this account
            $coa = $line->coa;
            $journalLines = JournalLine::whereHas('entry', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->where('coa_id', $coa->id)
            ->get();
            
            $totalDebit = $journalLines->sum('debit');
            $totalCredit = $journalLines->sum('credit');
            $saldoAkhir = ($coa->saldo_awal ?? 0) + $totalDebit - $totalCredit;
            
            $this->info("  New Saldo Akhir: {$saldoAkhir} (Rp " . number_format($saldoAkhir, 2) . ")");
        }
        
        if ($problematicLines->count() == 0) {
            $this->info("✅ No problematic journal lines found");
        }
        
        $this->info("\n=== FIX COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
