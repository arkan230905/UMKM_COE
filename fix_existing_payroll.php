<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Penggajian;
use App\Models\Pegawai;

echo "=== FIX EXISTING PAYROLL ===\n\n";

// Find Budi Susanto
$pegawai = Pegawai::where('nama', 'like', '%Budi Susanto%')->first();

if (!$pegawai) {
    echo "Employee not found!\n";
    exit;
}

echo "Fixing payroll for: {$pegawai->nama} (ID: {$pegawai->id})\n\n";

// Get the existing payroll record
$payroll = Penggajian::where('pegawai_id', $pegawai->id)
    ->where('tanggal_penggajian', '2026-04-24')
    ->first();

if (!$payroll) {
    echo "Payroll record not found for 2026-04-24\n";
    exit;
}

echo "Current payroll values:\n";
echo "ID: {$payroll->id}\n";
echo "Tarif per Jam: " . ($payroll->tarif_per_jam ?? 'NULL') . "\n";
echo "Total Jam Kerja: " . ($payroll->total_jam_kerja ?? 'NULL') . "\n";
echo "Tunjangan: " . ($payroll->tunjangan ?? 'NULL') . "\n";
echo "Asuransi: " . ($payroll->asuransi ?? 'NULL') . "\n";
echo "Total Gaji: " . ($payroll->total_gaji ?? 'NULL') . "\n";

// Get correct values from employee data
$jabatan = $pegawai->jabatanRelasi;
$tarif = $jabatan->tarif_per_jam ?? $pegawai->tarif_per_jam ?? 0;
$jamKerja = $payroll->total_jam_kerja ?? 7;
$gajiDasar = $tarif * $jamKerja;
$tunjanganJabatan = $jabatan->tunjangan ?? 0;
$tunjanganTransport = $jabatan->tunjangan_transport ?? 0;
$tunjanganKonsumsi = $jabatan->tunjangan_konsumsi ?? 0;
$totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;
$asuransi = $jabatan->asuransi ?? $pegawai->asuransi ?? 0;
$bonus = $payroll->bonus ?? 0;
$potongan = $payroll->potongan ?? 0;

$newTotal = $gajiDasar + $totalTunjangan + $asuransi + $bonus - $potongan;

echo "\nCorrect values should be:\n";
echo "Tarif per Jam: {$tarif}\n";
echo "Total Jam Kerja: {$jamKerja}\n";
echo "Gaji Dasar: {$gajiDasar}\n";
echo "Total Tunjangan: {$totalTunjangan}\n";
echo "Asuransi: {$asuransi}\n";
echo "Bonus: {$bonus}\n";
echo "Potongan: {$potongan}\n";
echo "New Total: {$newTotal}\n";

// Update the payroll record
$payroll->tarif_per_jam = $tarif;
$payroll->tunjangan = $totalTunjangan;
$payroll->asuransi = $asuransi;
$payroll->total_gaji = $newTotal;

// For BTKL employees, gaji_pokok should be 0
if (strtolower($pegawai->jenis_pegawai) === 'btkl') {
    $payroll->gaji_pokok = 0;
} else {
    $payroll->gaji_pokok = $jabatan->gaji_pokok ?? $pegawai->gaji_pokok ?? 0;
}

if ($payroll->save()) {
    echo "\n=== PAYROLL UPDATED SUCCESSFULLY ===\n";
    echo "The payroll record has been corrected.\n";
    echo "Please refresh the payroll detail page to see the updated values.\n";
    
    echo "\nUpdated values:\n";
    echo "Tarif per Jam: " . $payroll->tarif_per_jam . "\n";
    echo "Total Jam Kerja: " . $payroll->total_jam_kerja . "\n";
    echo "Tunjangan: " . $payroll->tunjangan . "\n";
    echo "Asuransi: " . $payroll->asuransi . "\n";
    echo "Total Gaji: " . $payroll->total_gaji . "\n";
} else {
    echo "\n=== FAILED TO UPDATE PAYROLL ===\n";
    echo "There was an error updating the payroll record.\n";
}
