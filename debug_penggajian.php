<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pegawai;
use App\Models\Penggajian;

echo "=== DEBUGGING PENGGAJIAN ISSUE ===\n\n";

// Check BTKL employees and their data
echo "=== BTKL EMPLOYEES DATA ===\n";
$btklEmployees = Pegawai::with('jabatanRelasi')
    ->where(function($query) {
        $query->where('jenis_pegawai', 'btkl')
              ->orWhere('kategori', 'btkl');
    })
    ->get();

foreach ($btklEmployees as $pegawai) {
    echo "Employee: {$pegawai->nama} (ID: {$pegawai->id})\n";
    echo "  Jenis: " . ($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'N/A') . "\n";
    
    if ($pegawai->jabatanRelasi) {
        echo "  Jabatan: {$pegawai->jabatanRelasi->nama_jabatan}\n";
        echo "  Tarif per Jam: Rp " . number_format($pegawai->jabatanRelasi->tarif_per_jam ?? 0) . "\n";
        echo "  Tunjangan: Rp " . number_format($pegawai->jabatanRelasi->tunjangan ?? 0) . "\n";
        echo "  Asuransi: Rp " . number_format($pegawai->jabatanRelasi->asuransi ?? 0) . "\n";
    } else {
        echo "  No jabatan relation found!\n";
        echo "  Direct tarif: Rp " . number_format($pegawai->tarif_per_jam ?? 0) . "\n";
    }
    echo "\n";
}

// Check latest penggajian records
echo "=== LATEST PENGGAJIAN RECORDS ===\n";
$latestRecords = Penggajian::with('pegawai')->latest()->take(3)->get();

foreach ($latestRecords as $record) {
    echo "Penggajian ID: {$record->id}\n";
    echo "  Pegawai: {$record->pegawai->nama}\n";
    echo "  Tanggal: {$record->tanggal_penggajian}\n";
    echo "  Tarif per Jam: Rp " . number_format($record->tarif_per_jam ?? 0) . "\n";
    echo "  Total Jam Kerja: " . ($record->total_jam_kerja ?? 0) . " jam\n";
    echo "  Gaji Pokok: Rp " . number_format($record->gaji_pokok ?? 0) . "\n";
    echo "  Tunjangan: Rp " . number_format($record->tunjangan ?? 0) . "\n";
    echo "  Asuransi: Rp " . number_format($record->asuransi ?? 0) . "\n";
    echo "  Total Gaji: Rp " . number_format($record->total_gaji ?? 0) . "\n";
    echo "  COA Kasbank: " . ($record->coa_kasbank ?? 'NULL') . "\n";
    echo "\n";
}

// Test the API endpoint
echo "=== TESTING API ENDPOINT ===\n";
$testPegawaiId = $btklEmployees->first()->id ?? null;
if ($testPegawaiId) {
    echo "Testing getEmployeeData for pegawai ID: {$testPegawaiId}\n";
    
    // Simulate the API call
    $controller = new \App\Http\Controllers\PenggajianController();
    $response = $controller->getEmployeeData($testPegawaiId);
    $data = json_decode($response->getContent(), true);
    
    echo "API Response:\n";
    print_r($data);
} else {
    echo "No BTKL employees found for testing.\n";
}

echo "\n=== DEBUGGING COMPLETED ===\n";
echo "Check the data above to identify the issue:\n";
echo "1. Are BTKL employees showing correct tarif_per_jam?\n";
echo "2. Are penggajian records storing the correct values?\n";
echo "3. Is the API returning correct data?\n";