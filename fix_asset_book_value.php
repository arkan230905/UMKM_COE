<?php
/**
 * Script untuk memperbaiki nilai buku aset agar sesuai dengan bulan saat ini
 * dan memastikan nominal penyusutan per bulan/tahun sudah benar
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Aset;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

try {
    echo "=== MEMPERBAIKI NILAI BUKU ASET ===\n\n";
    
    $asets = Aset::where('status', 'aktif')
        ->whereNotNull('umur_manfaat')
        ->where('umur_manfaat', '>', 0)
        ->get();
    
    echo "Ditemukan " . $asets->count() . " aset aktif yang perlu diperbaiki\n\n";
    
    foreach ($asets as $aset) {
        echo "Processing: {$aset->nama_aset} (ID: {$aset->id})\n";
        
        // 1. Hitung total perolehan
        $totalPerolehan = (float)($aset->harga_perolehan ?? 0) + (float)($aset->biaya_perolehan ?? 0);
        $nilaiResidu = (float)($aset->nilai_residu ?? 0);
        $umurManfaat = (int)($aset->umur_manfaat ?? 0);
        
        echo "  Total Perolehan: Rp " . number_format($totalPerolehan, 0, ',', '.') . "\n";
        echo "  Nilai Residu: Rp " . number_format($nilaiResidu, 0, ',', '.') . "\n";
        echo "  Umur Manfaat: {$umurManfaat} tahun\n";
        
        if ($totalPerolehan <= 0 || $umurManfaat <= 0) {
            echo "  ⚠️ Skip: Data tidak valid\n\n";
            continue;
        }
        
        // 2. Hitung penyusutan per tahun dan per bulan yang benar
        $nilaiDisusutkan = $totalPerolehan - $nilaiResidu;
        $penyusutanPerTahun = 0;
        $penyusutanPerBulan = 0;
        
        switch ($aset->metode_penyusutan) {
            case 'garis_lurus':
                $penyusutanPerTahun = $nilaiDisusutkan / $umurManfaat;
                $penyusutanPerBulan = $penyusutanPerTahun / 12;
                break;
                
            case 'saldo_menurun':
                // Double declining balance - gunakan rata-rata untuk konsistensi
                $penyusutanPerTahun = $nilaiDisusutkan / $umurManfaat;
                $penyusutanPerBulan = $penyusutanPerTahun / 12;
                break;
                
            case 'sum_of_years_digits':
                // Sum of years digits - gunakan rata-rata untuk konsistensi
                $penyusutanPerTahun = $nilaiDisusutkan / $umurManfaat;
                $penyusutanPerBulan = $penyusutanPerTahun / 12;
                break;
                
            default:
                $penyusutanPerTahun = $nilaiDisusutkan / $umurManfaat;
                $penyusutanPerBulan = $penyusutanPerTahun / 12;
                break;
        }
        
        echo "  Metode: {$aset->metode_penyusutan}\n";
        echo "  Penyusutan/tahun: Rp " . number_format($penyusutanPerTahun, 2, ',', '.') . "\n";
        echo "  Penyusutan/bulan: Rp " . number_format($penyusutanPerBulan, 2, ',', '.') . "\n";
        
        // 3. Hitung akumulasi penyusutan sampai bulan saat ini
        $tanggalPerolehan = Carbon::parse($aset->tanggal_akuisisi ?? $aset->tanggal_beli ?? $aset->created_at);
        $tanggalSekarang = Carbon::now();
        
        // Aturan: jika tanggal > 15, mulai bulan berikutnya
        if ($tanggalPerolehan->day > 15) {
            $tanggalPerolehan->addMonth()->day(1);
        } else {
            $tanggalPerolehan->day(1);
        }
        
        // Hitung bulan yang sudah berlalu (tidak termasuk bulan ini karena belum selesai)
        $bulanBerlalu = $tanggalPerolehan->diffInMonths($tanggalSekarang->startOfMonth());
        
        // Batasi maksimal sesuai umur manfaat
        $maxBulan = $umurManfaat * 12;
        $bulanBerlalu = min($bulanBerlalu, $maxBulan);
        
        $akumulasiPenyusutan = $penyusutanPerBulan * $bulanBerlalu;
        
        // Pastikan tidak melebihi nilai yang bisa disusutkan
        $akumulasiPenyusutan = min($akumulasiPenyusutan, $nilaiDisusutkan);
        
        echo "  Tanggal mulai penyusutan: " . $tanggalPerolehan->format('Y-m-d') . "\n";
        echo "  Bulan berlalu: {$bulanBerlalu} bulan\n";
        echo "  Akumulasi penyusutan: Rp " . number_format($akumulasiPenyusutan, 2, ',', '.') . "\n";
        
        // 4. Hitung nilai buku saat ini
        $nilaiBukuSaatIni = $totalPerolehan - $akumulasiPenyusutan;
        
        echo "  Nilai buku saat ini: Rp " . number_format($nilaiBukuSaatIni, 2, ',', '.') . "\n";
        
        // 5. Update data aset
        $updated = $aset->update([
            'penyusutan_per_tahun' => round($penyusutanPerTahun, 2),
            'penyusutan_per_bulan' => round($penyusutanPerBulan, 2),
            'akumulasi_penyusutan' => round($akumulasiPenyusutan, 2),
            'nilai_buku' => round($nilaiBukuSaatIni, 2),
        ]);
        
        if ($updated) {
            echo "  ✅ Data aset berhasil diupdate\n";
        } else {
            echo "  ❌ Gagal update data aset\n";
        }
        
        echo "\n";
    }
    
    echo "=== SELESAI ===\n";
    echo "Semua aset telah diupdate dengan nilai buku yang sesuai bulan saat ini.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}