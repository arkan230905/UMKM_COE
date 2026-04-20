<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Penggajian;
use App\Models\Pegawai;

echo "=== TESTING PENGGAJIAN DATA ===\n\n";

// Get the latest penggajian record
$latestPenggajian = Penggajian::with('pegawai')->latest()->first();

if (!$latestPenggajian) {
    echo "No penggajian records found!\n";
    exit;
}

echo "Latest Penggajian Record (ID: {$latestPenggajian->id}):\n";
echo "Pegawai: {$latestPenggajian->pegawai->nama}\n";
echo "Jenis Pegawai: " . strtoupper($latestPenggajian->pegawai->jenis_pegawai ?? $latestPenggajian->pegawai->kategori ?? 'BTKTL') . "\n";
echo "Tanggal: {$latestPenggajian->tanggal_penggajian}\n";
echo "\n--- STORED DATA ---\n";
echo "Gaji Pokok: Rp " . number_format($latestPenggajian->gaji_pokok ?? 0) . "\n";
echo "Tarif per Jam: Rp " . number_format($latestPenggajian->tarif_per_jam ?? 0) . "\n";
echo "Total Jam Kerja: " . ($latestPenggajian->total_jam_kerja ?? 0) . " jam\n";

$jenisPegawai = strtolower($latestPenggajian->pegawai->jenis_pegawai ?? $latestPenggajian->pegawai->kategori ?? 'btktl');
if ($jenisPegawai === 'btkl') {
    $gajiDasar = ($latestPenggajian->tarif_per_jam ?? 0) * ($latestPenggajian->total_jam_kerja ?? 0);
    echo "Gaji Dasar (Tarif × Jam): Rp " . number_format($gajiDasar) . "\n";
} else {
    echo "Gaji Dasar (Gaji Pokok): Rp " . number_format($latestPenggajian->gaji_pokok ?? 0) . "\n";
}

echo "\n--- TUNJANGAN BREAKDOWN ---\n";
echo "Tunjangan (Legacy): Rp " . number_format($latestPenggajian->tunjangan ?? 0) . "\n";
echo "Tunjangan Jabatan: Rp " . number_format($latestPenggajian->tunjangan_jabatan ?? 0) . "\n";
echo "Tunjangan Transport: Rp " . number_format($latestPenggajian->tunjangan_transport ?? 0) . "\n";
echo "Tunjangan Konsumsi: Rp " . number_format($latestPenggajian->tunjangan_konsumsi ?? 0) . "\n";
echo "Total Tunjangan: Rp " . number_format($latestPenggajian->total_tunjangan ?? 0) . "\n";

echo "\n--- OTHER COMPONENTS ---\n";
echo "Asuransi: Rp " . number_format($latestPenggajian->asuransi ?? 0) . "\n";
echo "Bonus: Rp " . number_format($latestPenggajian->bonus ?? 0) . "\n";
echo "Potongan: Rp " . number_format($latestPenggajian->potongan ?? 0) . "\n";

echo "\n--- TOTAL ---\n";
echo "Total Gaji (Stored): Rp " . number_format($latestPenggajian->total_gaji ?? 0) . "\n";

// Calculate expected total
if ($jenisPegawai === 'btkl') {
    $expectedGajiDasar = ($latestPenggajian->tarif_per_jam ?? 0) * ($latestPenggajian->total_jam_kerja ?? 0);
} else {
    $expectedGajiDasar = $latestPenggajian->gaji_pokok ?? 0;
}

$expectedTotal = $expectedGajiDasar 
    + ($latestPenggajian->total_tunjangan ?? $latestPenggajian->tunjangan ?? 0)
    + ($latestPenggajian->asuransi ?? 0)
    + ($latestPenggajian->bonus ?? 0)
    - ($latestPenggajian->potongan ?? 0);

echo "Total Gaji (Calculated): Rp " . number_format($expectedTotal) . "\n";
echo "Match: " . ($latestPenggajian->total_gaji == $expectedTotal ? "YES" : "NO") . "\n";

echo "\n--- PAYMENT INFO ---\n";
echo "COA Kas/Bank: {$latestPenggajian->coa_kasbank}\n";
echo "Status Pembayaran: {$latestPenggajian->status_pembayaran}\n";
echo "Status Posting: " . ($latestPenggajian->status_posting ?? 'belum_posting') . "\n";

// Check employee's jabatan data for comparison
echo "\n--- EMPLOYEE JABATAN DATA ---\n";
$pegawai = $latestPenggajian->pegawai;
if ($pegawai->jabatanRelasi) {
    echo "Jabatan: {$pegawai->jabatanRelasi->nama_jabatan}\n";
    echo "Gaji Pokok: Rp " . number_format($pegawai->jabatanRelasi->gaji_pokok ?? 0) . "\n";
    echo "Tarif per Jam: Rp " . number_format($pegawai->jabatanRelasi->tarif_per_jam ?? 0) . "\n";
    echo "Tunjangan: Rp " . number_format($pegawai->jabatanRelasi->tunjangan ?? 0) . "\n";
    echo "Tunjangan Transport: Rp " . number_format($pegawai->jabatanRelasi->tunjangan_transport ?? 0) . "\n";
    echo "Tunjangan Konsumsi: Rp " . number_format($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0) . "\n";
    echo "Asuransi: Rp " . number_format($pegawai->jabatanRelasi->asuransi ?? 0) . "\n";
} else {
    echo "No jabatan relation found for this employee.\n";
}

echo "\n=== TEST COMPLETED ===\n";
echo "If the data looks correct, the issue might be in the view display logic.\n";
echo "If the data is wrong, the issue is in the store method or form submission.\n";