<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Aset;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\JurnalUmum;
use App\Models\Coa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FixDepreciationDiscrepancy extends Command
{
    protected $signature = 'depreciation:fix-discrepancy {--dry-run : Show what would be fixed without making changes} {--asset-id= : Fix specific asset by ID}';
    protected $description = 'Fix discrepancies between asset depreciation values and journal entries';

    public function handle()
    {
        $this->info('=== MEMPERBAIKI KETIDAKSESUAIAN PENYUSUTAN ===');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $assetId = $this->option('asset-id');

        if ($dryRun) {
            $this->warn('MODE DRY RUN - Tidak ada perubahan yang akan disimpan');
            $this->newLine();
        }

        // Ambil aset yang akan diproses
        $query = Aset::whereNotNull('metode_penyusutan')
            ->whereNotNull('umur_manfaat')
            ->where('umur_manfaat', '>', 0);

        if ($assetId) {
            $query->where('id', $assetId);
        }

        $asets = $query->get();

        if ($asets->isEmpty()) {
            $this->error('Tidak ada aset yang ditemukan untuk diproses.');
            return 1;
        }

        $this->info("Memproses {$asets->count()} aset...");
        $this->newLine();

        $fixed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($asets as $aset) {
            try {
                $result = $this->processAsset($aset, $dryRun);
                
                if ($result['fixed']) {
                    $fixed++;
                    $this->info("✓ {$aset->nama_aset} - DIPERBAIKI");
                    if (!empty($result['changes'])) {
                        foreach ($result['changes'] as $change) {
                            $this->line("  - {$change}");
                        }
                    }
                } else {
                    $skipped++;
                    $this->line("- {$aset->nama_aset} - OK (tidak perlu diperbaiki)");
                }
                
            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ {$aset->nama_aset} - ERROR: " . $e->getMessage());
            }
            
            $this->newLine();
        }

        // Summary
        $this->info('=== RINGKASAN ===');
        $this->info("Diperbaiki: {$fixed}");
        $this->info("Dilewati: {$skipped}");
        if ($errors > 0) {
            $this->error("Error: {$errors}");
        }

        if ($dryRun && $fixed > 0) {
            $this->newLine();
            $this->warn('Untuk menerapkan perbaikan, jalankan command tanpa --dry-run');
        }

        return 0;
    }

    private function processAsset(Aset $aset, bool $dryRun): array
    {
        $changes = [];
        $needsFix = false;

        // 1. Hitung penyusutan yang benar
        $correctDepreciation = $this->calculateCorrectDepreciation($aset);
        
        // 2. Periksa nilai yang tersimpan
        $storedMonthly = (float)($aset->penyusutan_per_bulan ?? 0);
        $storedYearly = (float)($aset->penyusutan_per_tahun ?? 0);
        
        // 3. Periksa apakah perlu diperbaiki
        $monthlyDiff = abs($correctDepreciation['monthly'] - $storedMonthly);
        $yearlyDiff = abs($correctDepreciation['yearly'] - $storedYearly);
        
        if ($monthlyDiff > 0.01) {
            $needsFix = true;
            $changes[] = "Penyusutan bulanan: Rp " . number_format($storedMonthly, 2, ',', '.') . 
                        " → Rp " . number_format($correctDepreciation['monthly'], 2, ',', '.');
        }
        
        if ($yearlyDiff > 0.01) {
            $needsFix = true;
            $changes[] = "Penyusutan tahunan: Rp " . number_format($storedYearly, 2, ',', '.') . 
                        " → Rp " . number_format($correctDepreciation['yearly'], 2, ',', '.');
        }

        // 4. Periksa akumulasi penyusutan dari jurnal
        $journalAccumulated = $this->getAccumulatedDepreciationFromJournals($aset);
        $storedAccumulated = (float)($aset->akumulasi_penyusutan ?? 0);
        
        $accumulatedDiff = abs($journalAccumulated - $storedAccumulated);
        if ($accumulatedDiff > 0.01) {
            $needsFix = true;
            $changes[] = "Akumulasi penyusutan: Rp " . number_format($storedAccumulated, 2, ',', '.') . 
                        " → Rp " . number_format($journalAccumulated, 2, ',', '.') . " (dari jurnal)";
        }

        // 5. Hitung nilai buku yang benar
        $totalPerolehan = (float)$aset->harga_perolehan + (float)($aset->biaya_perolehan ?? 0);
        $correctBookValue = max($totalPerolehan - $journalAccumulated, (float)($aset->nilai_residu ?? 0));
        $storedBookValue = (float)($aset->nilai_buku ?? 0);
        
        $bookValueDiff = abs($correctBookValue - $storedBookValue);
        if ($bookValueDiff > 0.01) {
            $needsFix = true;
            $changes[] = "Nilai buku: Rp " . number_format($storedBookValue, 2, ',', '.') . 
                        " → Rp " . number_format($correctBookValue, 2, ',', '.');
        }

        // 6. Terapkan perbaikan jika diperlukan
        if ($needsFix && !$dryRun) {
            $aset->update([
                'penyusutan_per_bulan' => $correctDepreciation['monthly'],
                'penyusutan_per_tahun' => $correctDepreciation['yearly'],
                'akumulasi_penyusutan' => $journalAccumulated,
                'nilai_buku' => $correctBookValue
            ]);
        }

        return [
            'fixed' => $needsFix,
            'changes' => $changes
        ];
    }

    private function calculateCorrectDepreciation(Aset $aset): array
    {
        $totalPerolehan = (float)$aset->harga_perolehan + (float)($aset->biaya_perolehan ?? 0);
        $nilaiResidu = (float)($aset->nilai_residu ?? 0);
        $umurManfaat = (int)$aset->umur_manfaat;
        
        if ($umurManfaat <= 0 || $totalPerolehan <= 0) {
            return ['monthly' => 0, 'yearly' => 0];
        }
        
        $nilaiDisusutkan = $totalPerolehan - $nilaiResidu;
        
        // Untuk semua metode, gunakan garis lurus untuk konsistensi posting bulanan
        // Sesuai dengan logika di AsetDepreciationController
        $yearlyDepreciation = $nilaiDisusutkan / $umurManfaat;
        $monthlyDepreciation = $yearlyDepreciation / 12;
        
        return [
            'monthly' => $monthlyDepreciation,
            'yearly' => $yearlyDepreciation
        ];
    }

    private function getAccumulatedDepreciationFromJournals(Aset $aset): float
    {
        $total = 0;
        
        // Method 1: Dari JournalEntry (jika ada)
        if (class_exists('App\Models\JournalEntry')) {
            $journalEntries = JournalEntry::where('ref_type', 'depr')
                ->where('ref_id', $aset->id)
                ->get();
                
            foreach ($journalEntries as $entry) {
                $lines = JournalLine::where('journal_entry_id', $entry->id)
                    ->where('debit', '>', 0)
                    ->get();
                    
                foreach ($lines as $line) {
                    $total += (float)$line->debit;
                }
            }
        }
        
        // Method 2: Dari JurnalUmum berdasarkan keterangan
        if ($total == 0) {
            $jurnalUmum = JurnalUmum::where('keterangan', 'like', '%Penyusutan%')
                ->where('keterangan', 'like', '%' . $aset->nama_aset . '%')
                ->where('debit', '>', 0)
                ->get();
                
            foreach ($jurnalUmum as $jurnal) {
                $total += (float)$jurnal->debit;
            }
        }
        
        return $total;
    }
}