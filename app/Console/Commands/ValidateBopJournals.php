<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produksi;
use App\Models\JurnalUmum;

class ValidateBopJournals extends Command
{
    protected $signature = 'bop:validate-journals {produksi_id?}';
    protected $description = 'Validate that all BOP components are recorded in journals';

    public function handle()
    {
        $produksiId = $this->argument('produksi_id');
        
        if ($produksiId) {
            $produksis = Produksi::where('id', $produksiId)->get();
        } else {
            // Check latest 10 produksi
            $produksis = Produksi::orderBy('id', 'desc')->take(10)->get();
        }
        
        if ($produksis->count() == 0) {
            $this->error('No produksi found!');
            return 1;
        }
        
        $this->info("Checking {$produksis->count()} produksi records...\n");
        
        $issues = [];
        
        foreach ($produksis as $prod) {
            $this->info("========================================");
            $this->info("Produksi ID: {$prod->id}");
            $this->info("Produk: {$prod->produk->nama_produk}");
            $this->info("Tanggal: {$prod->tanggal}");
            $this->info("Total BOP: Rp " . number_format($prod->total_bop, 0));
            
            // Get BOP journals
            $bopJournals = JurnalUmum::where('tipe_referensi', 'produksi_bop')
                ->where('referensi', (string)$prod->id)
                ->where('kredit', '>', 0) // Only credit entries (the components)
                ->get();
            
            $this->info("BOP Journal Entries: {$bopJournals->count()}");
            
            if ($bopJournals->count() == 0 && $prod->total_bop > 0) {
                $this->error("⚠️  WARNING: No BOP journal entries found but total_bop > 0!");
                $issues[] = [
                    'produksi_id' => $prod->id,
                    'issue' => 'No BOP journal entries',
                    'total_bop' => $prod->total_bop
                ];
            }
            
            // Show journal entries
            $totalJournalKredit = 0;
            if ($bopJournals->count() > 0) {
                $this->info("\nBOP Journal Detail:");
                foreach ($bopJournals as $j) {
                    $totalJournalKredit += $j->kredit;
                    $this->line(sprintf(
                        "  %-10s %-40s  Kredit: %15s",
                        $j->coa->kode_akun ?? 'N/A',
                        substr($j->keterangan, 0, 40),
                        'Rp ' . number_format($j->kredit, 0)
                    ));
                }
            }
            
            // Validate: Total journal kredit should equal total_bop
            if (abs($totalJournalKredit - $prod->total_bop) > 1) { // Allow 1 rupiah difference for rounding
                $this->error("❌ MISMATCH! Journal total: Rp " . number_format($totalJournalKredit, 0) . " vs Total BOP: Rp " . number_format($prod->total_bop, 0));
                $issues[] = [
                    'produksi_id' => $prod->id,
                    'issue' => 'Journal mismatch',
                    'journal_total' => $totalJournalKredit,
                    'expected_total' => $prod->total_bop,
                    'difference' => $prod->total_bop - $totalJournalKredit
                ];
            } else {
                $this->info("✅ Journal total matches!");
            }
            
            $this->newLine();
        }
        
        // Summary
        $this->info("========================================");
        $this->info("SUMMARY");
        $this->info("========================================");
        
        if (count($issues) == 0) {
            $this->info("✅ All BOP journals are valid!");
        } else {
            $this->error("❌ Found " . count($issues) . " issues:");
            foreach ($issues as $issue) {
                $this->line("  - Produksi ID {$issue['produksi_id']}: {$issue['issue']}");
                if (isset($issue['difference'])) {
                    $this->line("    Missing: Rp " . number_format($issue['difference'], 0));
                }
            }
        }
        
        return count($issues) == 0 ? 0 : 1;
    }
}
