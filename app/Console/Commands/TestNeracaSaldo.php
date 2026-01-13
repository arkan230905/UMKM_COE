<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Account;
use Carbon\Carbon;

class TestNeracaSaldo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-neraca-saldo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test neraca saldo calculation with actual journal data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Neraca Saldo Calculation...');
        
        // Test untuk periode Desember 2025
        $from = '2025-12-01';
        $to = '2025-12-31';
        
        $this->info('');
        $this->info("Periode: {$from} s/d {$to}");
        
        // Get semua COA
        $coas = Coa::where('is_akun_header', false)->orderBy('kode_akun')->get();
        
        $totalDebit = 0;
        $totalKredit = 0;
        $totalSaldoAkhir = 0;
        
        $this->info('');
        $this->info('Kode Akun | Nama Akun | Saldo Awal | Debit | Kredit | Saldo Akhir');
        $this->info(str_repeat('-', 80));
        
        foreach ($coas as $coa) {
            // Get saldo awal
            $saldoAwal = $coa->saldo_awal ?? 0;
            
            // Hitung mutasi menggunakan JournalLine dan JournalEntry
            $debit = JournalLine::whereHas('entry', function($query) use ($from, $to) {
                    $query->whereBetween('tanggal', [$from, $to]);
                })
                ->whereHas('account', function($query) use ($coa) {
                    $query->where('code', $coa->kode_akun);
                })
                ->sum('debit');
            
            $kredit = JournalLine::whereHas('entry', function($query) use ($from, $to) {
                    $query->whereBetween('tanggal', [$from, $to]);
                })
                ->whereHas('account', function($query) use ($coa) {
                    $query->where('code', $coa->kode_akun);
                })
                ->sum('credit');
            
            // Hitung saldo akhir
            if ($coa->saldo_normal === 'debit') {
                $saldoAkhir = $saldoAwal + $debit - $kredit;
            } else {
                $saldoAkhir = $saldoAwal + $kredit - $debit;
            }
            
            // Hanya tampilkan jika ada nilai
            if ($saldoAwal != 0 || $debit > 0 || $kredit > 0 || $saldoAkhir != 0) {
                $this->info(sprintf(
                    "%-10s | %-20s | %10s | %10s | %10s | %10s",
                    $coa->kode_akun,
                    substr($coa->nama_akun, 0, 20),
                    number_format($saldoAwal, 0),
                    number_format($debit, 0),
                    number_format($kredit, 0),
                    number_format($saldoAkhir, 0)
                ));
                
                $totalDebit += $debit;
                $totalKredit += $kredit;
                $totalSaldoAkhir += $saldoAkhir;
            }
        }
        
        $this->info(str_repeat('-', 80));
        $this->info(sprintf("TOTAL | | | %10s | %10s |", 
            number_format($totalDebit, 0), 
            number_format($totalKredit, 0)
        ));
        
        $this->info('');
        $this->info('Balance Check:');
        $this->info('Total Debit: ' . number_format($totalDebit, 0));
        $this->info('Total Kredit: ' . number_format($totalKredit, 0));
        $this->info('Balance: ' . ($totalDebit == $totalKredit ? 'BALANCED' : 'NOT BALANCED'));
        
        // Show journal details for verification
        $this->info('');
        $this->info('Journal Entries in Period:');
        
        $journals = JournalEntry::whereBetween('tanggal', [$from, $to])
            ->with('lines.account')
            ->orderBy('tanggal')
            ->get();
            
        foreach ($journals as $journal) {
            $this->info('');
            $this->info("{$journal->tanggal} - {$journal->ref_type}#{$journal->ref_id} - {$journal->memo}");
            foreach ($journal->lines as $line) {
                $account = $line->account;
                $accountName = $account ? $account->name : 'Unknown';
                $this->info("  Account {$line->account_id} ({$accountName}): Debit {$line->debit} / Credit {$line->credit}");
            }
        }
        
        return 0;
    }
}
