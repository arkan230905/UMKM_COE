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
        
        // Find the problematic journal line for COA 53 (BOP)
        $problematicLine = JournalLine::whereHas('entry', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereHas('coa', function($query) {
            $query->where('kode_akun', '53');
        })->where('debit', 0)
        ->where('credit', '>', 0)
        ->with(['entry', 'coa'])
        ->first();
        
        if ($problematicLine) {
            $this->info("📊 Found problematic journal line:");
            $this->info("  Journal Line ID: {$problematicLine->id}");
            $this->info("  Entry ID: {$problematicLine->entry->id}");
            $this->info("  Date: {$problematicLine->entry->tanggal}");
            $this->info("  Ref: {$problematicLine->entry->ref_type}-{$problematicLine->entry->ref_id}");
            $this->info("  COA: {$problematicLine->coa->kode_akun} - {$problematicLine->coa->nama_akun}");
            $this->info("  Current: Debit={$problematicLine->debit}, Credit={$problematicLine->credit}");
            
            // Fix the debit/credit
            $originalDebit = $problematicLine->debit;
            $originalCredit = $problematicLine->credit;
            
            $problematicLine->debit = $originalCredit;
            $problematicLine->credit = $originalDebit;
            $problematicLine->save();
            
            $this->info("  ✅ Fixed: Debit={$problematicLine->debit}, Credit={$problematicLine->credit}");
            
            // Verify the fix
            $this->info("\n🔍 VERIFICATION:");
            
            // Recalculate the balance
            $coa = $problematicLine->coa;
            $journalLines = JournalLine::whereHas('entry', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->where('coa_id', $coa->id)
            ->get();
            
            $totalDebit = $journalLines->sum('debit');
            $totalCredit = $journalLines->sum('credit');
            $saldoAkhir = ($coa->saldo_awal ?? 0) + $totalDebit - $totalCredit;
            
            $this->info("  Total Debit: {$totalDebit}");
            $this->info("  Total Credit: {$totalCredit}");
            $this->info("  New Saldo Akhir: {$saldoAkhir}");
            
            if ($saldoAkhir >= 0) {
                $this->info("  ✅ Saldo akhir now positive: Rp " . number_format($saldoAkhir, 2));
            } else {
                $this->error("  ❌ Saldo akhir still negative: {$saldoAkhir}");
            }
            
        } else {
            $this->info("✅ No problematic journal lines found for COA 53");
        }
        
        // Check for other potential issues
        $this->info("\n📊 CHECKING OTHER BEBAN ACCOUNTS:");
        
        $otherProblematicLines = JournalLine::whereHas('entry', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereHas('coa', function($query) {
            $query->where('tipe_akun', 'Expense')
                  ->orWhere('tipe_akun', 'expense')
                  ->orWhere('tipe_akun', 'Beban')
                  ->orWhere('tipe_akun', 'Biaya');
        })->where('debit', 0)
        ->where('credit', '>', 0)
        ->with(['coa'])
        ->get();
        
        $this->info("Other beban lines with debit=0 and credit>0: " . $otherProblematicLines->count());
        
        foreach ($otherProblematicLines as $line) {
            $this->info("  {$line->coa->kode_akun} - {$line->coa->nama_akun}: Credit={$line->credit}");
        }
        
        $this->info("\n=== FIX COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
