<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penjualan;
use App\Services\JournalService;
use App\Models\JurnalUmum;

class FixPenjualanJournal extends Command
{
    protected $signature = 'penjualan:fix-journal {id? : ID penjualan (optional, jika kosong akan fix semua)}';
    protected $description = 'Fix/rebuild journal entries for penjualan (sales) transactions';

    public function handle()
    {
        $id = $this->argument('id');
        
        if ($id) {
            // Fix single penjualan
            $penjualan = Penjualan::with(['details.produk', 'produk'])->find($id);
            
            if (!$penjualan) {
                $this->error("Penjualan ID {$id} tidak ditemukan!");
                return 1;
            }
            
            $this->fixSinglePenjualan($penjualan);
        } else {
            // Fix all penjualan
            $this->info("Fixing journals for ALL penjualan...");
            
            $penjualans = Penjualan::with(['details.produk', 'produk'])->get();
            
            $this->info("Found {$penjualans->count()} penjualan records");
            
            $bar = $this->output->createProgressBar($penjualans->count());
            $bar->start();
            
            $success = 0;
            $failed = 0;
            
            foreach ($penjualans as $penjualan) {
                try {
                    $this->fixSinglePenjualan($penjualan, false);
                    $success++;
                } catch (\Exception $e) {
                    $failed++;
                    \Log::error("Failed to fix journal for penjualan {$penjualan->id}: " . $e->getMessage());
                }
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->info("✓ Success: {$success}");
            if ($failed > 0) {
                $this->error("✗ Failed: {$failed}");
            }
        }
        
        return 0;
    }
    
    private function fixSinglePenjualan($penjualan, $verbose = true)
    {
        if ($verbose) {
            $this->info("Processing Penjualan ID: {$penjualan->id}");
            $this->info("  Nomor: {$penjualan->nomor_penjualan}");
            $this->info("  Tanggal: {$penjualan->tanggal}");
            $this->info("  Grand Total: Rp " . number_format($penjualan->grand_total ?? 0, 0, ',', '.'));
        }
        
        // Check existing journals
        $existingJournals = JurnalUmum::where('tipe_referensi', 'sale')
            ->where('referensi', (string)$penjualan->id)
            ->get();
        
        if ($verbose) {
            if ($existingJournals->count() > 0) {
                $this->warn("  Found {$existingJournals->count()} existing journal entries - will be deleted and recreated");
                
                $totalDebit = $existingJournals->sum('debit');
                $totalKredit = $existingJournals->sum('kredit');
                $balanced = (abs($totalDebit - $totalKredit) < 0.01);
                
                $this->info("  Existing Journal:");
                $this->info("    Total Debit:  Rp " . number_format($totalDebit, 0, ',', '.'));
                $this->info("    Total Kredit: Rp " . number_format($totalKredit, 0, ',', '.'));
                $this->info("    Balanced: " . ($balanced ? '✓ YES' : '✗ NO'));
            } else {
                $this->info("  No existing journal entries found");
            }
        }
        
        // Recreate journal
        try {
            JournalService::createJournalFromPenjualan($penjualan);
            
            // Verify new journals
            $newJournals = JurnalUmum::where('tipe_referensi', 'sale')
                ->where('referensi', (string)$penjualan->id)
                ->get();
            
            $totalDebit = $newJournals->sum('debit');
            $totalKredit = $newJournals->sum('kredit');
            $balanced = (abs($totalDebit - $totalKredit) < 0.01);
            
            if ($verbose) {
                $this->info("  ✓ Journal recreated successfully!");
                $this->info("  New Journal:");
                $this->info("    Total Debit:  Rp " . number_format($totalDebit, 0, ',', '.'));
                $this->info("    Total Kredit: Rp " . number_format($totalKredit, 0, ',', '.'));
                $this->info("    Balanced: " . ($balanced ? '✓ YES' : '✗ NO'));
                $this->info("    Lines: {$newJournals->count()}");
                
                if (!$balanced) {
                    $this->error("  WARNING: Journal is NOT balanced!");
                }
                
                $this->newLine();
                $this->table(
                    ['Akun', 'Debit', 'Kredit', 'Memo'],
                    $newJournals->map(fn($j) => [
                        $j->coa->kode_akun . ' - ' . $j->coa->nama_akun,
                        $j->debit > 0 ? 'Rp ' . number_format($j->debit, 0, ',', '.') : '-',
                        $j->kredit > 0 ? 'Rp ' . number_format($j->kredit, 0, ',', '.') : '-',
                        $j->memo
                    ])->toArray()
                );
            }
            
        } catch (\Exception $e) {
            if ($verbose) {
                $this->error("  ✗ Failed to create journal: " . $e->getMessage());
            }
            throw $e;
        }
    }
}
