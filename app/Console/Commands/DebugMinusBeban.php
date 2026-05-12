<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;
use App\Models\JournalLine;
use App\Models\JournalEntry;

class DebugMinusBeban extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:minus-beban {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug beban with minus values in laba rugi';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== DEBUGGING MINUS BEBAN FOR USER ID: {$userId} ===");
        
        $periode = now()->format('Y-m');
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 5, 2);
        
        $from = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $to = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
        
        $this->info("Periode: {$periode} ({$from} to {$to})");
        
        // Check COA 53 (BOP)
        $coa53 = Coa::where('kode_akun', '53')->where('user_id', $userId)->first();
        
        if ($coa53) {
            $this->info("\n📊 COA 53 (BOP) Details:");
            $this->info("  Kode: {$coa53->kode_akun}");
            $this->info("  Nama: {$coa53->nama_akun}");
            $this->info("  Tipe Akun: '{$coa53->tipe_akun}'");
            $this->info("  Saldo Normal: '{$coa53->saldo_normal}'");
            $this->info("  Saldo Awal: " . ($coa53->saldo_awal ?? 0));
            
            // Get journal lines for COA 53
            $journalLines = JournalLine::whereHas('entry', function($query) use ($userId, $from, $to) {
                $query->where('user_id', $userId)
                      ->whereBetween('tanggal', [$from, $to]);
            })->where('coa_id', $coa53->id)
            ->with('entry')
            ->get();
            
            $this->info("\n📊 Journal Lines for COA 53:");
            $this->info("Total lines: " . $journalLines->count());
            
            $totalDebit = 0;
            $totalCredit = 0;
            
            foreach ($journalLines as $line) {
                $this->info("  Entry ID: {$line->entry->id}, Date: {$line->entry->tanggal}, Ref: {$line->entry->ref_type}-{$line->entry->ref_id}");
                $this->info("    Debit: {$line->debit}, Credit: {$line->credit}");
                
                $totalDebit += $line->debit;
                $totalCredit += $line->credit;
            }
            
            $this->info("\n💰 Calculation:");
            $this->info("  Saldo Awal: " . ($coa53->saldo_awal ?? 0));
            $this->info("  Total Debit: {$totalDebit}");
            $this->info("  Total Credit: {$totalCredit}");
            
            // COA 53 should have debit normal balance (expense)
            $saldoAkhir = ($coa53->saldo_awal ?? 0) + $totalDebit - $totalCredit;
            $this->info("  Saldo Akhir: {$saldoAkhir}");
            
            if ($saldoAkhir < 0) {
                $this->error("❌ PROBLEM: Saldo akhir minus ({$saldoAkhir})");
                $this->info("  This means Credit > Debit, which is unusual for expense accounts");
                $this->info("  Possible causes:");
                $this->info("    1. Journal entries have wrong debit/credit");
                $this->info("    2. Opening balance is negative");
                $this->info("    3. Refund/correction entries");
            } else {
                $this->info("✅ Saldo akhir normal: {$saldoAkhir}");
            }
            
        } else {
            $this->error("❌ COA 53 (BOP) not found for user {$userId}");
        }
        
        // Check other beban accounts for comparison
        $this->info("\n📊 OTHER BEBAN ACCOUNTS FOR COMPARISON:");
        $bebanCoas = Coa::where('user_id', $userId)
            ->where(function($query) {
                $query->where('tipe_akun', 'Expense')
                      ->orWhere('tipe_akun', 'expense')
                      ->orWhere('tipe_akun', 'Beban')
                      ->orWhere('tipe_akun', 'Biaya');
            })
            ->whereIn('kode_akun', ['52', '513', '514', '550'])
            ->get();
            
        foreach ($bebanCoas as $coa) {
            $journalLines = JournalLine::whereHas('entry', function($query) use ($userId, $from, $to) {
                $query->where('user_id', $userId)
                      ->whereBetween('tanggal', [$from, $to]);
            })->where('coa_id', $coa->id)
            ->get();
            
            $totalDebit = $journalLines->sum('debit');
            $totalCredit = $journalLines->sum('credit');
            $saldoAkhir = ($coa->saldo_awal ?? 0) + $totalDebit - $totalCredit;
            
            $this->info("  {$coa->kode_akun} - {$coa->nama_akun}: Rp " . number_format($saldoAkhir, 2));
        }
        
        $this->info("\n=== DEBUG COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
