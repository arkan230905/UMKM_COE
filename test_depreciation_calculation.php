<?php
/**
 * Script untuk test perhitungan penyusutan yang benar
 * Menampilkan contoh perhitungan untuk ketiga metode penyusutan
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Carbon\Carbon;

echo "=== TEST PERHITUNGAN PENYUSUTAN ===\n\n";

// Data contoh aset
$hargaPerolehan = 85000000; // Rp 85 juta
$nilaiResidu = 5000000;     // Rp 5 juta
$umurManfaat = 5;           // 5 tahun
$nilaiDisusutkan = $hargaPerolehan - $nilaiResidu; // Rp 80 juta

echo "Data Aset:\n";
echo "- Harga Perolehan: Rp " . number_format($hargaPerolehan, 0, ',', '.') . "\n";
echo "- Nilai Residu: Rp " . number_format($nilaiResidu, 0, ',', '.') . "\n";
echo "- Nilai Disusutkan: Rp " . number_format($nilaiDisusutkan, 0, ',', '.') . "\n";
echo "- Umur Manfaat: {$umurManfaat} tahun\n\n";

// 1. GARIS LURUS (Straight Line)
echo "1. METODE GARIS LURUS:\n";
$penyusutanPerTahun = $nilaiDisusutkan / $umurManfaat;
$penyusutanPerBulan = $penyusutanPerTahun / 12;

echo "   Penyusutan per tahun: Rp " . number_format($penyusutanPerTahun, 0, ',', '.') . "\n";
echo "   Penyusutan per bulan: Rp " . number_format($penyusutanPerBulan, 0, ',', '.') . "\n\n";

// 2. SALDO MENURUN GANDA (Double Declining Balance)
echo "2. METODE SALDO MENURUN GANDA:\n";
$rateTahunan = 2 / $umurManfaat; // 2/5 = 0.4 = 40%
$rateBulanan = $rateTahunan / 12;

echo "   Rate tahunan: " . ($rateTahunan * 100) . "%\n";
echo "   Rate bulanan: " . number_format($rateBulanan * 100, 4) . "%\n";

// Simulasi 3 tahun pertama
$nilaiBuku = $hargaPerolehan;
echo "   \n   Simulasi per tahun:\n";
for ($tahun = 1; $tahun <= 3; $tahun++) {
    $penyusutanTahunIni = $nilaiBuku * $rateTahunan;
    
    // Cek jangan sampai kurang dari nilai residu
    if ($nilaiBuku - $penyusutanTahunIni < $nilaiResidu) {
        $penyusutanTahunIni = $nilaiBuku - $nilaiResidu;
    }
    
    $nilaiBuku -= $penyusutanTahunIni;
    $penyusutanPerBulanTahunIni = $penyusutanTahunIni / 12;
    
    echo "   Tahun {$tahun}: Rp " . number_format($penyusutanTahunIni, 0, ',', '.') . 
         " (per bulan: Rp " . number_format($penyusutanPerBulanTahunIni, 0, ',', '.') . ")\n";
    echo "            Nilai buku akhir: Rp " . number_format($nilaiBuku, 0, ',', '.') . "\n";
}
echo "\n";

// 3. JUMLAH ANGKA TAHUN (Sum of Years Digits)
echo "3. METODE JUMLAH ANGKA TAHUN:\n";
$sumOfYears = ($umurManfaat * ($umurManfaat + 1)) / 2; // (5*6)/2 = 15

echo "   Sum of Years: {$sumOfYears}\n";
echo "   \n   Simulasi per tahun:\n";

for ($tahun = 1; $tahun <= $umurManfaat; $tahun++) {
    $sisaUmur = $umurManfaat - $tahun + 1;
    $penyusutanTahunIni = ($nilaiDisusutkan * $sisaUmur) / $sumOfYears;
    $penyusutanPerBulanTahunIni = $penyusutanTahunIni / 12;
    
    echo "   Tahun {$tahun} (sisa umur {$sisaUmur}): Rp " . number_format($penyusutanTahunIni, 0, ',', '.') . 
         " (per bulan: Rp " . number_format($penyusutanPerBulanTahunIni, 0, ',', '.') . ")\n";
}

echo "\n=== KESIMPULAN ===\n";
echo "Penyusutan per bulan BERBEDA untuk setiap metode dan berubah setiap periode:\n";
echo "- Garis Lurus: TETAP Rp " . number_format($penyusutanPerBulan, 0, ',', '.') . " per bulan\n";
echo "- Saldo Menurun: MENURUN setiap bulan (dimulai dari yang tertinggi)\n";
echo "- Jumlah Angka Tahun: MENURUN setiap tahun (tahun 1 tertinggi, tahun 5 terendah)\n";

echo "\nUntuk implementasi yang benar, sistem harus:\n";
echo "1. Menghitung penyusutan berdasarkan bulan/tahun ke berapa\n";
echo "2. Menggunakan nilai buku saat ini untuk saldo menurun\n";
echo "3. Menggunakan sisa umur untuk jumlah angka tahun\n";