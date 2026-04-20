<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pegawai;
use App\Models\Jabatan;

echo "=== EMPLOYEE DATA TEST ===\n\n";

// Find Budi Susanto
$pegawai = Pegawai::where('nama', 'like', '%Budi Susanto%')->first();

if (!$pegawai) {
    echo "Employee 'Budi Susanto' not found!\n";
    
    // Show all employees with similar names
    $employees = Pegawai::where('nama', 'like', '%Budi%')->get();
    echo "\nEmployees with 'Budi' in name:\n";
    foreach ($employees as $emp) {
        echo "- ID: {$emp->id}, Name: {$emp->nama}, Jabatan: {$emp->jabatan_nama}\n";
    }
    exit;
}

echo "Employee Found: {$pegawai->nama}\n";
echo "ID: {$pegawai->id}\n";
echo "Jabatan ID: {$pegawai->jabatan_id}\n";
echo "Jabatan: {$pegawai->jabatan_nama}\n";
echo "Jenis Pegawai: {$pegawai->jenis_pegawai}\n\n";

echo "=== Employee Direct Data ===\n";
echo "Gaji Pokok: " . ($pegawai->gaji_pokok ?? 'NULL') . "\n";
echo "Tarif per Jam: " . ($pegawai->tarif_per_jam ?? 'NULL') . "\n";
echo "Tunjangan: " . ($pegawai->tunjangan ?? 'NULL') . "\n";
echo "Asuransi: " . ($pegawai->asuransi ?? 'NULL') . "\n\n";

echo "=== Jabatan Data ===\n";
if ($pegawai->jabatanRelasi) {
    $jabatan = $pegawai->jabatanRelasi;
    echo "Jabatan Name: {$jabatan->nama}\n";
    echo "Gaji Pokok: " . ($jabatan->gaji_pokok ?? 'NULL') . "\n";
    echo "Tarif per Jam: " . ($jabatan->tarif_per_jam ?? 'NULL') . "\n";
    echo "Tunjangan: " . ($jabatan->tunjangan ?? 'NULL') . "\n";
    echo "Tunjangan Transport: " . ($jabatan->tunjangan_transport ?? 'NULL') . "\n";
    echo "Tunjangan Konsumsi: " . ($jabatan->tunjangan_konsumsi ?? 'NULL') . "\n";
    echo "Asuransi: " . ($jabatan->asuransi ?? 'NULL') . "\n";
    
    $totalTunjangan = ($jabatan->tunjangan ?? 0) + ($jabatan->tunjangan_transport ?? 0) + ($jabatan->tunjangan_konsumsi ?? 0);
    echo "Total Tunjangan: {$totalTunjangan}\n";
} else {
    echo "No Jabatan relation found!\n";
}

echo "\n=== Komponen Gaji (from accessor) ===\n";
$komponen = $pegawai->komponen_gaji;
foreach ($komponen as $key => $value) {
    echo "{$key}: {$value}\n";
}

echo "\n=== API Response Simulation ===\n";
// Simulate the API response
$jenis = strtolower($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'btktl');
$jabatan = $pegawai->jabatanRelasi;

if ($jabatan) {
    $gajiPokok = $jabatan->gaji_pokok ?? $pegawai->gaji_pokok ?? 0;
    $tarif = $jabatan->tarif_per_jam ?? $pegawai->tarif_per_jam ?? 0;
    $tunjanganJabatan = $jabatan->tunjangan ?? 0;
    $tunjanganTransport = $jabatan->tunjangan_transport ?? 0;
    $tunjanganKonsumsi = $jabatan->tunjangan_konsumsi ?? 0;
    $asuransi = $jabatan->asuransi ?? 0;
} else {
    $gajiPokok = $pegawai->gaji_pokok ?? 0;
    $tarif = $pegawai->tarif_per_jam ?? 0;
    $tunjanganJabatan = $pegawai->tunjangan ?? 0;
    $tunjanganTransport = 0;
    $tunjanganKonsumsi = 0;
    $asuransi = $pegawai->asuransi ?? 0;
}

$totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;

echo "Jenis: {$jenis}\n";
echo "Gaji Pokok: {$gajiPokok}\n";
echo "Tarif: {$tarif}\n";
echo "Total Tunjangan: {$totalTunjangan}\n";
echo "Asuransi: {$asuransi}\n";

// Calculate expected total
$expectedTotal = ($jenis === 'btkl') ? ($tarif * 7) + $totalTunjangan + $asuransi : $gajiPokok + $totalTunjangan + $asuransi;
echo "\nExpected Total (7 jam kerja): {$expectedTotal}\n";
echo "Expected Total (formatted): Rp " . number_format($expectedTotal, 0, ',', '.') . "\n";
