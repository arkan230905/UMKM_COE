<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penjualan;
use App\Models\JurnalUmum;
use Illuminate\Support\Facades\DB;

class VerifyPenjualanJournal extends Command
{
    protected $signature = 'verify:penjualan-journal {--user-id= : Filter by user_id} {--show-details : Show detailed journal entries}';
    protected $description = 'Verify yang penjualan dengan payment_status=paid memiliki jurnal di jurnal_umum';

    public function handle()
    {
        $this->info('🔍 Verifying Penjualan Journal Status...');
        $this->newLine();

        // Get penjualan with payment_status='paid'
        $query = Penjualan::where('payment_status', 'paid');
        
        if ($this->option('user-id')) {
            $query->where('user_id', $this->option('user-id'));
        }

        $penjualans = $query->orderBy('created_at', 'desc')->get();

        if ($penjualans->isEmpty()) {
            $this->warn('⚠️  No penjualan found with payment_status=paid');
            return;
        }

        $this->info("📊 Found {$penjualans->count()} penjualan with payment_status='paid'");
        $this->newLine();

        $summary = [
            'total' => 0,
            'with_journal' => 0,
            'without_journal' => 0,
            'balance_ok' => 0,
            'balance_not_ok' => 0,
        ];

        $problemPenjualans = [];

        foreach ($penjualans as $penjualan) {
            $summary['total']++;
            
            // Check if journal exists
            $journals = JurnalUmum::where('referensi', (string)$penjualan->id)
                ->where('tipe_referensi', 'sale')
                ->get();

            if ($journals->isEmpty()) {
                $summary['without_journal']++;
                $problemPenjualans[] = $penjualan;
                
                $this->error("❌ Penjualan ID {$penjualan->id} ({$penjualan->nomor_penjualan}): NO JOURNAL");
            } else {
                $summary['with_journal']++;
                
                // Verify balance
                $totalDebit = $journals->sum('debit');
                $totalCredit = $journals->sum('kredit');
                $selisih = abs($totalDebit - $totalCredit);

                if ($selisih > 0.01) { // Allow small rounding errors
                    $summary['balance_not_ok']++;
                    $problemPenjualans[] = $penjualan;
                    $this->error("⚠️  Penjualan ID {$penjualan->id} ({$penjualan->nomor_penjualan}): JOURNAL IMBALANCE");
                } else {
                    $summary['balance_ok']++;
                    $this->line("✅ Penjualan ID {$penjualan->id} ({$penjualan->nomor_penjualan}): OK");
                }

                if ($this->option('show-details')) {
                    $this->showJournalDetails($journals, $penjualan);
                }
            }
        }

        $this->newLine();
        $this->info('📈 SUMMARY:');
        $this->line("  Total Penjualan: {$summary['total']}");
        $this->line("  With Journal: {$summary['with_journal']} ✅");
        $this->line("  Without Journal: {$summary['without_journal']} ❌");
        $this->line("  Balance OK: {$summary['balance_ok']} ✅");
        $this->line("  Balance NOT OK: {$summary['balance_not_ok']} ⚠️");

        if (!empty($problemPenjualans)) {
            $this->newLine();
            $this->error('⚠️  PROBLEM PENJUALANS DETECTED:');
            foreach ($problemPenjualans as $penjualan) {
                $journals = JurnalUmum::where('referensi', (string)$penjualan->id)
                    ->where('tipe_referensi', 'sale')
                    ->get();

                if ($journals->isEmpty()) {
                    $this->error("  - ID {$penjualan->id} ({$penjualan->nomor_penjualan}): NO JOURNAL");
                    $this->line("    Grand Total: Rp " . number_format($penjualan->grand_total, 2));
                    $this->line("    Created At: {$penjualan->created_at}");
                    $this->line("    User ID: {$penjualan->user_id}");
                    
                    // Check penjualan details
                    $details = $penjualan->details()->count();
                    $this->line("    Details: {$details} records");
                    
                    // Suggest action
                    $this->line("    ACTION: php artisan rebuild:jurnal-penjualan {$penjualan->id}");
                } else {
                    $totalDebit = $journals->sum('debit');
                    $totalCredit = $journals->sum('kredit');
                    $this->error("  - ID {$penjualan->id} ({$penjualan->nomor_penjualan}): IMBALANCE");
                    $this->line("    Total Debit: Rp " . number_format($totalDebit, 2));
                    $this->line("    Total Credit: Rp " . number_format($totalCredit, 2));
                    $this->line("    Difference: Rp " . number_format(abs($totalDebit - $totalCredit), 2));
                }
            }
        }

        $this->newLine();
        if ($summary['without_journal'] > 0 || $summary['balance_not_ok'] > 0) {
            $this->warn('⚠️  Issues detected! Please investigate or rebuild journals.');
            return 1;
        } else {
            $this->info('✅ All penjualan journals are OK!');
            return 0;
        }
    }

    private function showJournalDetails($journals, $penjualan)
    {
        $this->line("   Jurnal entries for Penjualan {$penjualan->nomor_penjualan}:");
        foreach ($journals as $journal) {
            $debit = $journal->debit > 0 ? 'Rp ' . number_format($journal->debit, 2) : '-';
            $credit = $journal->kredit > 0 ? 'Rp ' . number_format($journal->kredit, 2) : '-';
            $this->line("     • {$journal->keterangan} | D: {$debit} | K: {$credit}");
        }
    }
}
