<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;
use App\Models\JournalLine;
use App\Models\JournalEntry;

class DebugRealLabaRugi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:real-laba-rugi {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug real laba rugi data using actual journal entries';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== DEBUGGING REAL LABA RUGI DATA FOR USER ID: {$userId} ===");
        
        $periode = now()->format('Y-m');
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 5, 2);
        
        $from = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $to = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
        
        $this->info("Periode: {$periode} ({$from} to {$to})");
        
        // Get all COAs for user
        $coas = Coa::where('user_id', $userId)->get();
        $this->info("Total COAs: " . $coas->count());
        
        // Get actual journal data for the period
        $journalLines = JournalLine::whereHas('entry', function($query) use ($userId, $from, $to) {
            $query->where('user_id', $userId)
                  ->whereBetween('tanggal', [$from, $to]);
        })->with('entry', 'coa')
        ->get();
        
        $this->info("Journal lines found: " . $journalLines->count());
        
        // Calculate account balances from actual journal lines
        $accountData = [];
        foreach ($coas as $coa) {
            $saldoAwal = (float)($coa->saldo_awal ?? 0);
            
            // Calculate mutasi from journal lines
            $totalDebit = $journalLines->where('coa_id', $coa->id)->sum('debit');
            $totalKredit = $journalLines->where('coa_id', $coa->id)->sum('credit');
            
            $firstDigit = substr($coa->kode_akun, 0, 1);
            $isDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
            
            if ($isDebitNormal) {
                $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
            } else {
                $saldoAkhir = $saldoAwal - $totalDebit + $totalKredit;
            }
            
            $accountData[$coa->kode_akun] = [
                'coa' => $coa,
                'saldo_akhir' => $saldoAkhir,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit
            ];
        }
        
        // Filter pendapatan and beban
        $pendapatan = $coas->filter(function($coa) use ($accountData) {
            if (!in_array($coa->tipe_akun, ['Revenue', 'revenue', 'Pendapatan'])) return false;
            $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
            return $saldo != 0;
        })->sortBy('kode_akun');
        
        $beban = $coas->filter(function($coa) use ($accountData) {
            if (!in_array($coa->tipe_akun, ['Expense', 'expense', 'Beban', 'Biaya'])) return false;
            $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
            return $saldo != 0;
        })->sortBy('kode_akun');
        
        $this->info("\n📊 PENDAPATAN DATA:");
        $this->info("Count: " . $pendapatan->count());
        foreach ($pendapatan as $coa) {
            $data = $accountData[$coa->kode_akun];
            $this->info("  {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun}):");
            $this->info("    Debit: {$data['total_debit']}, Credit: {$data['total_kredit']}");
            $this->info("    Saldo Akhir: Rp " . number_format($data['saldo_akhir'], 2));
        }
        
        $this->info("\n📊 BEBAN DATA:");
        $this->info("Count: " . $beban->count());
        foreach ($beban as $coa) {
            $data = $accountData[$coa->kode_akun];
            $this->info("  {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun}):");
            $this->info("    Debit: {$data['total_debit']}, Credit: {$data['total_kredit']}");
            $this->info("    Saldo Akhir: Rp " . number_format($data['saldo_akhir'], 2));
        }
        
        // Check specifically for HPP
        $this->info("\n🔍 HPP ANALYSIS:");
        $hppCoa = Coa::where('kode_akun', '560')->where('user_id', $userId)->first();
        if ($hppCoa) {
            $hppInPendapatan = $pendapatan->contains('kode_akun', '560');
            $hppInBeban = $beban->contains('kode_akun', '560');
            
            $this->info("HPP COA Found: {$hppCoa->nama_akun}");
            $this->info("HPP Tipe Akun: '{$hppCoa->tipe_akun}'");
            $this->info("HPP in Pendapatan: " . ($hppInPendapatan ? '❌ YES' : '✅ NO'));
            $this->info("HPP in Beban: " . ($hppInBeban ? '✅ YES' : '❌ NO'));
            
            if ($hppInPendapatan) {
                $this->error("❌ PROBLEM: HPP appears in pendapatan section!");
                $this->info("This should not happen with correct tipe_akun = 'Expense'");
            }
            
            if ($hppInBeban) {
                $this->info("✅ HPP correctly appears in beban section");
                $hppData = $accountData['560'];
                $this->info("HPP Saldo: Rp " . number_format($hppData['saldo_akhir'], 2));
            }
        }
        
        // Calculate totals like in controller
        $totalPendapatan = $pendapatan->sum(function($coa) use ($accountData) {
            return $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
        });
        
        $totalBeban = $beban->sum(function($coa) use ($accountData) {
            return $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
        });
        
        $labaRugi = $totalPendapatan - $totalBeban;
        
        $this->info("\n💰 LABA RUGI CALCULATION:");
        $this->info("Total Pendapatan: Rp " . number_format($totalPendapatan, 2));
        $this->info("Total Beban: Rp " . number_format($totalBeban, 2));
        $this->info("Laba/Rugi: Rp " . number_format($labaRugi, 2));
        
        $this->info("\n=== DEBUG COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
