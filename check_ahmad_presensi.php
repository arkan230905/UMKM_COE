<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pegawai;
use App\Models\Presensi;

echo "=== CHECK AHMAD SURYANTI PRESENSI ===\n\n";

// Find Ahmad Suryanto
$pegawai = Pegawai::where('nama', 'like', '%Ahmad Suryanto%')->first();

if (!$pegawai) {
    echo "Employee 'Ahmad Suryanto' not found!\n";
    
    // Show similar employees
    $employees = Pegawai::where('nama', 'like', '%Ahmad%')->get();
    echo "\nEmployees with 'Ahmad' in name:\n";
    foreach ($employees as $emp) {
        echo "- ID: {$emp->id}, Name: {$emp->nama}, Jabatan: {$emp->jabatan_nama}\n";
    }
    exit;
}

echo "Employee Found: {$pegawai->nama}\n";
echo "ID: {$pegawai->id}\n";
echo "Jabatan: {$pegawai->jabatan_nama}\n";
echo "Jenis Pegawai: {$pegawai->jenis_pegawai}\n\n";

// Check presensi data for April 2026
$month = 4;
$year = 2026;

echo "Checking presensi for Month: {$month}, Year: {$year}\n";

$totalJam = Presensi::where('pegawai_id', $pegawai->id)
    ->whereMonth('tgl_presensi', $month)
    ->whereYear('tgl_presensi', $year)
    ->sum('jumlah_jam');

echo "Total Jam Kerja: {$totalJam}\n";

// Get all presensi records
$presensiRecords = Presensi::where('pegawai_id', $pegawai->id)
    ->whereMonth('tgl_presensi', $month)
    ->whereYear('tgl_presensi', $year)
    ->orderBy('tgl_presensi', 'asc')
    ->get();

echo "\nPresensi Records:\n";
if ($presensiRecords->isEmpty()) {
    echo "No presensi records found for this month.\n";
} else {
    foreach ($presensiRecords as $record) {
        echo "- {$record->tgl_presensi}: {$record->jumlah_jam} jam\n";
    }
}

// Test API endpoint simulation
echo "\n=== API ENDPOINT TEST ===\n";
$pegawaiId = $pegawai->id;

$apiResponse = [
    'total_jam' => $totalJam
];

echo "API Response would be: " . json_encode($apiResponse) . "\n";

// Check if there are any presensi records at all for this employee
$allPresensi = Presensi::where('pegawai_id', $pegawai->id)->count();
echo "\nTotal presensi records for this employee (all time): {$allPresensi}\n";

if ($allPresensi == 0) {
    echo "This employee has NO presensi records at all!\n";
    echo "This explains why jam kerja is 0.\n";
} elseif ($totalJam == 0) {
    echo "This employee has presensi records but none for April 2026.\n";
    echo "The system should show jam kerja for the current month.\n";
}
