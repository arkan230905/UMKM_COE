<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JurnalUmum;
use App\Models\Aset;
use Illuminate\Support\Facades\DB;

class UpdateApril2026JournalValues extends Command
{
    protected $signature = 'journal:update-april-2026 {--dry-run : Show what would be updated without making changes}';
    protected $description = 'Update April 2026 depreciation journal values to match correct amounts';

    // Data koreksi yang benar
    private $corrections = [
        [
            'nama_aset' => 'Mesin Produksi',
            'old_amount' => 1416667.00,
            'new_amount' => 1333333.00,
            'keywords' => ['Mesin', 'Produksi']
        ],
        [
            'nama_aset' => 'Peralatan Produksi',
            'old_amount' => 2833333.00,
            'new_amount' => 659474.00,
            'keywords' => ['Peralatan', 'Produksi']
        ],
        [
            'nama_aset' => 'Kendaraan',
            'old_amount' => 2361111.00,
            'new_amount' => 888889.00,
            'keywords' => ['Kendaraan']
        ]
    ];

    public function handle()
    {
        $this->info('=== UPDATE JURNAL PENYUSUTAN APRIL 2026 ===');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('MODE DRY RUN - Tidak ada perubahan yang akan disimpan');
            $this->newLine();
        }

        $targetDate = '2026-04-30';
        $updated = 0;
        $errors = 0;

        foreach ($this->corrections as $correction) {
            $this->info("Memproses: {$correction['nama_aset']}");
            
            try {
                // Cari jurnal yang perlu diupdate
                $journals = JurnalUmum::where('tanggal', $targetDate)
                    ->where('keterangan', 'like', '%Penyusutan%')
                    ->where(function($query) use ($correction) {
                        foreach ($correction['keywords'] as $keyword) {
                            $query->orWhere('keterangan', 'like', "%{$keyword}%");
                        }
                    })
                    ->where(function($query) use ($correction) {
                        $query->where('debit', $correction['old_amount'])
                              ->orWhere('kredit', $correction['old_amount']);
                    })
                    ->get();

                if ($journals->isEmpty()) {
                    $this->warn("  Tidak ditemukan jurnal dengan nilai lama: Rp " . number_format($correction['old_amount'], 0, ',', '.'));
                    continue;
                }

                $this->line("  Ditemukan {$journals->count()} jurnal entry");
                
                foreach ($journals as $journal) {
                    $oldDebit = (float)$journal->debit;
                    $oldKredit = (float)$journal->kredit;
                    
                    if ($oldDebit == $correction['old_amount']) {
                        // Update debit (beban penyusutan)
                        $this->line("  Update debit: Rp " . number_format($oldDebit, 0, ',', '.') . 
                                   " → Rp " . number_format($correction['new_amount'], 0, ',', '.'));
                        
                        if (!$dryRun) {
                            $journal->update(['debit' => $correction['new_amount']]);
                        }
                        $updated++;
                    }
                    
                    if ($oldKredit == $correction['old_amount']) {
                        // Update kredit (akumulasi penyusutan)
                        $this->line("  Update kredit: Rp " . number_format($oldKredit, 0, ',', '.') . 
                                   " → Rp " . number_format($correction['new_amount'], 0, ',', '.'));
                        
                        if (!$dryRun) {
                            $journal->update(['kredit' => $correction['new_amount']]);
                        }
                        $updated++;
                    }
                }

                // Update data aset juga
                if (!$dryRun) {
                    $aset = Aset::where(function($query) use ($correction) {
                        foreach ($correction['keywords'] as $keyword) {
                            $query->orWhere('nama_aset', 'like', "%{$keyword}%");
                        }
                    })->first();

                    if ($aset) {
                        $aset->update([
                            'penyusutan_per_bulan' => $correction['new_amount'],
                            'penyusutan_per_tahun' => $correction['new_amount'] * 12
                        ]);
                        $this->line("  Update data aset: penyusutan_per_bulan = Rp " . number_format($correction['new_amount'], 0, ',', '.'));
                    }
                }

            } catch (\Exception $e) {
                $this->error("  Error: " . $e->getMessage());
                $errors++;
            }

            $this->newLine();
        }

        // Validasi hasil
        if (!$dryRun && $updated > 0) {
            $this->info('VALIDASI HASIL:');
            $this->validateResults($targetDate);
        }

        // Summary
        $this->info('=== RINGKASAN ===');
        $this->info("Jurnal diupdate: {$updated}");
        if ($errors > 0) {
            $this->error("Error: {$errors}");
        }

        if ($dryRun && $updated > 0) {
            $this->newLine();
            $this->warn('Untuk menerapkan perubahan, jalankan command tanpa --dry-run');
        }

        return 0;
    }

    private function validateResults(string $targetDate): void
    {
        $this->newLine();
        
        foreach ($this->corrections as $correction) {
            $journals = JurnalUmum::where('tanggal', $targetDate)
                ->where('keterangan', 'like', '%Penyusutan%')
                ->where(function($query) use ($correction) {
                    foreach ($correction['keywords'] as $keyword) {
                        $query->orWhere('keterangan', 'like', "%{$keyword}%");
                    }
                })
                ->get();

            $this->line("{$correction['nama_aset']}:");
            
            $foundCorrectValue = false;
            foreach ($journals as $journal) {
                $amount = $journal->debit > 0 ? $journal->debit : $journal->kredit;
                $type = $journal->debit > 0 ? 'Debit' : 'Kredit';
                
                if (abs($amount - $correction['new_amount']) < 0.01) {
                    $this->info("  ✓ {$type}: Rp " . number_format($amount, 0, ',', '.'));
                    $foundCorrectValue = true;
                } else {
                    $this->error("  ✗ {$type}: Rp " . number_format($amount, 0, ',', '.') . " (seharusnya: Rp " . number_format($correction['new_amount'], 0, ',', '.') . ")");
                }
            }
            
            if (!$foundCorrectValue) {
                $this->error("  ✗ Tidak ada nilai yang benar ditemukan");
            }
        }
    }
}