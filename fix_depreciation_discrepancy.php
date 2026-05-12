<?php

/**
 * Script untuk menganalisis dan memperbaiki ketidaksesuaian nominal penyusutan
 * antara data aset dan jurnal umum
 */

require_once 'vendor/autoload.php';

use App\Models\Aset;
use App\Models\JurnalUmum;
use App\Models\JournalEntry;
use App\Models\Coa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DepreciationDiscrepancyFixer
{
    public function analyzeDiscrepancies()
    {
        echo "=== ANALISIS KETIDAKSESUAIAN PENYUSUTAN ===\n\n";
        
        // Ambil semua aset yang memiliki penyusutan
        $asets = Aset::whereNotNull('metode_penyusutan')
            ->whereNotNull('penyusutan_per_bulan')
            ->where('penyusutan_per_bulan', '>', 0)
            ->get();
            
        $discrepancies = [];
        
        foreach ($asets as $aset) {
            echo "Menganalisis: {$aset->nama_aset} (ID: {$aset->id})\n";
            
            // 1. Hitung penyusutan yang seharusnya berdasarkan data aset
            $expectedMonthly = $this->calculateCorrectDepreciation($aset);
            
            // 2. Ambil penyusutan dari jurnal umum
            $journalEntries = $this->getDepreciationJournalEntries($aset);
            
            // 3. Bandingkan nilai
            $storedMonthly = (float)$aset->penyusutan_per_bulan;
            
            echo "  - Penyusutan tersimpan: Rp " . number_format($storedMonthly, 2, ',', '.') . "\n";
            echo "  - Penyusutan terhitung: Rp " . number_format($expectedMonthly, 2, ',', '.') . "\n";
            
            if (!empty($journalEntries)) {
                $journalAmounts = array_column($journalEntries, 'debit');
                $uniqueAmounts = array_unique($journalAmounts);
                
                echo "  - Jurnal entries: " . count($journalEntries) . " entries\n";
                echo "  - Nominal di jurnal: ";
                foreach ($uniqueAmounts as $amount) {
                    echo "Rp " . number_format($amount, 2, ',', '.') . " ";
                }
                echo "\n";
                
                // Cek apakah ada ketidaksesuaian
                foreach ($uniqueAmounts as $journalAmount) {
                    $diff1 = abs($journalAmount - $storedMonthly);
                    $diff2 = abs($journalAmount - $expectedMonthly);
                    
                    if ($diff1 > 0.01 || $diff2 > 0.01) {
                        $discrepancies[] = [
                            'aset_id' => $aset->id,
                            'nama_aset' => $aset->nama_aset,
                            'stored_monthly' => $storedMonthly,
                            'calculated_monthly' => $expectedMonthly,
                            'journal_amounts' => $uniqueAmounts,
                            'metode_penyusutan' => $aset->metode_penyusutan,
                            'total_perolehan' => (float)$aset->harga_perolehan + (float)($aset->biaya_perolehan ?? 0),
                            'nilai_residu' => (float)($aset->nilai_residu ?? 0),
                            'umur_manfaat' => $aset->umur_manfaat
                        ];
                    }
                }
            }
            
            echo "\n";
        }
        
        return $discrepancies;
    }
    
    private function calculateCorrectDepreciation(Aset $aset): float
    {
        $totalPerolehan = (float)$aset->harga_perolehan + (float)($aset->biaya_perolehan ?? 0);
        $nilaiResidu = (float)($aset->nilai_residu ?? 0);
        $umurManfaat = (int)$aset->umur_manfaat;
        
        if ($umurManfaat <= 0 || $totalPerolehan <= 0) {
            return 0;
        }
        
        switch ($aset->metode_penyusutan) {
            case 'garis_lurus':
                $nilaiDisusutkan = $totalPerolehan - $nilaiResidu;
                return $nilaiDisusutkan / ($umurManfaat * 12);
                
            case 'saldo_menurun':
                // Untuk konsistensi dengan posting bulanan, gunakan rata-rata
                $nilaiDisusutkan = $totalPerolehan - $nilaiResidu;
                return $nilaiDisusutkan / ($umurManfaat * 12);
                
            case 'sum_of_years_digits':
                // Untuk konsistensi dengan posting bulanan, gunakan rata-rata
                $nilaiDisusutkan = $totalPerolehan - $nilaiResidu;
                return $nilaiDisusutkan / ($umurManfaat * 12);
                
            default:
                $nilaiDisusutkan = $totalPerolehan - $nilaiResidu;
                return $nilaiDisusutkan / ($umurManfaat * 12);
        }
    }
    
    private function getDepreciationJournalEntries(Aset $aset): array
    {
        // Cari jurnal penyusutan untuk aset ini
        $entries = [];
        
        // Method 1: Cari berdasarkan referensi (jika menggunakan JournalEntry model)
        if (class_exists('App\Models\JournalEntry')) {
            $journalEntries = JournalEntry::where('ref_type', 'depr')
                ->where('ref_id', $aset->id)
                ->with('lines')
                ->get();
                
            foreach ($journalEntries as $entry) {
                foreach ($entry->lines as $line) {
                    if ($line->debit > 0) { // Beban penyusutan (debit)
                        $entries[] = [
                            'tanggal' => $entry->tanggal,
                            'debit' => (float)$line->debit,
                            'keterangan' => $entry->keterangan
                        ];
                    }
                }
            }
        }
        
        // Method 2: Cari berdasarkan keterangan di JurnalUmum
        $jurnalUmum = JurnalUmum::where('keterangan', 'like', '%' . $aset->nama_aset . '%')
            ->where('keterangan', 'like', '%Penyusutan%')
            ->where('debit', '>', 0)
            ->get();
            
        foreach ($jurnalUmum as $jurnal) {
            $entries[] = [
                'tanggal' => $jurnal->tanggal,
                'debit' => (float)$jurnal->debit,
                'keterangan' => $jurnal->keterangan
            ];
        }
        
        return $entries;
    }
    
    public function fixDiscrepancies(array $discrepancies): void
    {
        echo "\n=== MEMPERBAIKI KETIDAKSESUAIAN ===\n\n";
        
        foreach ($discrepancies as $discrepancy) {
            echo "Memperbaiki: {$discrepancy['nama_aset']}\n";
            
            $aset = Aset::find($discrepancy['aset_id']);
            if (!$aset) continue;
            
            // Update nilai penyusutan_per_bulan dengan perhitungan yang benar
            $correctMonthly = $discrepancy['calculated_monthly'];
            $correctYearly = $correctMonthly * 12;
            
            echo "  - Update penyusutan_per_bulan: Rp " . number_format($correctMonthly, 2, ',', '.') . "\n";
            echo "  - Update penyusutan_per_tahun: Rp " . number_format($correctYearly, 2, ',', '.') . "\n";
            
            $aset->update([
                'penyusutan_per_bulan' => $correctMonthly,
                'penyusutan_per_tahun' => $correctYearly
            ]);
            
            // Recalculate nilai buku berdasarkan akumulasi penyusutan yang benar
            $totalPerolehan = $discrepancy['total_perolehan'];
            $akumulasiBenar = $this->calculateCorrectAccumulatedDepreciation($aset);
            $nilaiBukuBenar = max($totalPerolehan - $akumulasiBenar, $discrepancy['nilai_residu']);
            
            echo "  - Update akumulasi_penyusutan: Rp " . number_format($akumulasiBenar, 2, ',', '.') . "\n";
            echo "  - Update nilai_buku: Rp " . number_format($nilaiBukuBenar, 2, ',', '.') . "\n";
            
            $aset->update([
                'akumulasi_penyusutan' => $akumulasiBenar,
                'nilai_buku' => $nilaiBukuBenar
            ]);
            
            echo "  ✓ Selesai\n\n";
        }
    }
    
    private function calculateCorrectAccumulatedDepreciation(Aset $aset): float
    {
        // Hitung akumulasi berdasarkan jurnal yang sudah diposting
        $totalFromJournals = 0;
        
        if (class_exists('App\Models\JournalEntry')) {
            $journalEntries = JournalEntry::where('ref_type', 'depr')
                ->where('ref_id', $aset->id)
                ->with('lines')
                ->get();
                
            foreach ($journalEntries as $entry) {
                foreach ($entry->lines as $line) {
                    if ($line->debit > 0) { // Beban penyusutan (debit)
                        $totalFromJournals += (float)$line->debit;
                    }
                }
            }
        }
        
        return $totalFromJournals;
    }
    
    public function generateReport(array $discrepancies): void
    {
        echo "\n=== LAPORAN KETIDAKSESUAIAN ===\n\n";
        
        if (empty($discrepancies)) {
            echo "✓ Tidak ada ketidaksesuaian ditemukan.\n";
            return;
        }
        
        echo "Ditemukan " . count($discrepancies) . " aset dengan ketidaksesuaian:\n\n";
        
        foreach ($discrepancies as $i => $disc) {
            echo ($i + 1) . ". {$disc['nama_aset']}\n";
            echo "   Metode: {$disc['metode_penyusutan']}\n";
            echo "   Total Perolehan: Rp " . number_format($disc['total_perolehan'], 0, ',', '.') . "\n";
            echo "   Nilai Residu: Rp " . number_format($disc['nilai_residu'], 0, ',', '.') . "\n";
            echo "   Umur Manfaat: {$disc['umur_manfaat']} tahun\n";
            echo "   Penyusutan tersimpan: Rp " . number_format($disc['stored_monthly'], 2, ',', '.') . "/bulan\n";
            echo "   Penyusutan seharusnya: Rp " . number_format($disc['calculated_monthly'], 2, ',', '.') . "/bulan\n";
            echo "   Selisih: Rp " . number_format(abs($disc['stored_monthly'] - $disc['calculated_monthly']), 2, ',', '.') . "\n";
            
            if (!empty($disc['journal_amounts'])) {
                echo "   Nominal di jurnal: ";
                foreach ($disc['journal_amounts'] as $amount) {
                    echo "Rp " . number_format($amount, 2, ',', '.') . " ";
                }
                echo "\n";
            }
            
            echo "\n";
        }
    }
}

// Jalankan analisis
try {
    $fixer = new DepreciationDiscrepancyFixer();
    
    // 1. Analisis ketidaksesuaian
    $discrepancies = $fixer->analyzeDiscrepancies();
    
    // 2. Generate laporan
    $fixer->generateReport($discrepancies);
    
    // 3. Tanya apakah ingin memperbaiki
    if (!empty($discrepancies)) {
        echo "\nApakah Anda ingin memperbaiki ketidaksesuaian ini? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim($line) === 'y' || trim($line) === 'Y') {
            $fixer->fixDiscrepancies($discrepancies);
            echo "\n✓ Perbaikan selesai!\n";
            echo "\nSilakan jalankan posting penyusutan ulang untuk memastikan jurnal sesuai.\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}