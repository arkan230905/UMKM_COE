<?php
/**
 * Script untuk memperbaiki perhitungan penyusutan dengan metode yang benar
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Aset;
use Carbon\Carbon;

try {
    echo "=== MEMPERBAIKI PERHITUNGAN PENYUSUTAN ===\n\n";
    
    $asets = Aset::where('status', 'aktif')
        ->whereNotNull('umur_manfaat')
        ->where('umur_manfaat', '>', 0)
        ->get();
    
    echo "Ditemukan " . $asets->count() . " aset untuk diperbaiki\n\n";
    
    foreach ($asets as $aset) {
        echo "Processing: {$aset->nama_aset} (Metode: {$aset->metode_penyusutan})\n";
        
        $totalPerolehan = (float)($aset->harga_perolehan ?? 0) + (float)($aset->biaya_perolehan ?? 0);
        $nilaiResidu = (float)($aset->nilai_residu ?? 0);
        $umurManfaat = (int)($aset->umur_manfaat ?? 0);
        $nilaiDisusutkan = $totalPerolehan - $nilaiResidu;
        
        echo "  Total Perolehan: Rp " . number_format($totalPerolehan, 0, ',', '.') . "\n";
        echo "  Nilai Residu: Rp " . number_format($nilaiResidu, 0, ',', '.') . "\n";
        echo "  Nilai Disusutkan: Rp " . number_format($nilaiDisusutkan, 0, ',', '.') . "\n";
        
        // Hitung penyusutan per tahun dan per bulan berdasarkan metode
        $penyusutanPerTahun = 0;
        $penyusutanPerBulan = 0;
        
        switch ($aset->metode_penyusutan) {
            case 'garis_lurus':
                $penyusutanPerTahun = $nilaiDisusutkan / $umurManfaat;
                $penyusutanPerBulan = $penyusutanPerTahun / 12;
                echo "  Metode: Garis Lurus (tetap setiap bulan)\n";
                break;
                
            case 'saldo_menurun':
                // Double Declining Balance - tahun pertama
                $rateTahunan = 2 / $umurManfaat;
                $penyusutanPerTahun = $totalPerolehan * $rateTahunan; // Tahun pertama
                $penyusutanPerBulan = $aset->hitungPenyusutanPerBulanSaatIni(); // Bulan saat ini
                echo "  Metode: Saldo Menurun (rate: " . ($rateTahunan * 100) . "% per tahun)\n";
                echo "  Penyusutan tahun 1: Rp " . number_format($penyusutanPerTahun, 0, ',', '.') . "\n";
                break;
                
            case 'sum_of_years_digits':
                // Sum of Years Digits - tahun pertama
                $sumOfYears = ($umurManfaat * ($umurManfaat + 1)) / 2;
                $penyusutanPerTahun = ($nilaiDisusutkan * $umurManfaat) / $sumOfYears; // Tahun pertama
                $penyusutanPerBulan = $aset->hitungPenyusutanPerBulanSaatIni(); // Bulan saat ini
                echo "  Metode: Jumlah Angka Tahun (sum: {$sumOfYears})\n";
                echo "  Penyusutan tahun 1: Rp " . number_format($penyusutanPerTahun, 0, ',', '.') . "\n";
                break;
                
            default:
                $penyusutanPerTahun = $nilaiDisusutkan / $umurManfaat;
                $penyusutanPerBulan = $penyusutanPerTahun / 12;
                echo "  Metode: Default (garis lurus)\n";
                break;
        }
        
        echo "  Penyusutan per bulan saat ini: Rp " . number_format($penyusutanPerBulan, 2, ',', '.') . "\n";
        
        // Update nilai buku real-time
        $oldNilaiBuku = $aset->nilai_buku;
        $oldAkumulasi = $aset->akumulasi_penyusutan;
        
        $aset->updateNilaiBukuRealTime();
        
        // Update penyusutan per tahun dan per bulan di database
        $aset->update([
            'penyusutan_per_tahun' => round($penyusutanPerTahun, 2),
            'penyusutan_per_bulan' => round($penyusutanPerBulan, 2),
        ]);
        
        $aset->refresh();
        
        echo "  Akumulasi penyusutan: Rp " . number_format($oldAkumulasi, 0, ',', '.') . 
             " → Rp " . number_format($aset->akumulasi_penyusutan, 0, ',', '.') . "\n";
        echo "  Nilai buku: Rp " . number_format($oldNilaiBuku, 0, ',', '.') . 
             " → Rp " . number_format($aset->nilai_buku, 0, ',', '.') . "\n";
        
        echo "  ✅ Updated\n\n";
    }
    
    echo "=== SELESAI ===\n";
    echo "Semua aset telah diupdate dengan perhitungan penyusutan yang benar.\n";
    echo "\nCatatan:\n";
    echo "- Garis Lurus: Penyusutan tetap setiap bulan\n";
    echo "- Saldo Menurun: Penyusutan menurun setiap bulan berdasarkan nilai buku\n";
    echo "- Jumlah Angka Tahun: Penyusutan menurun setiap tahun berdasarkan sisa umur\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}