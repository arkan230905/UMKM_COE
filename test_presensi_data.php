<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Presensi;
use App\Models\Pegawai;

echo "=== PRESENSI DATA TEST ===\n\n";

// Find Budi Susanto
$pegawai = Pegawai::where('nama', 'like', '%Budi Susanto%')->first();

if (!$pegawai) {
    echo "Employee not found!\n";
    exit;
}

echo "Testing presensi data for employee ID: {$pegawai->id}\n\n";

// Test current month (April 2026)
$month = 4;
$year = 2026;

echo "Month: {$month}, Year: {$year}\n";

$totalJam = Presensi::where('pegawai_id', $pegawai->id)
    ->whereMonth('tgl_presensi', $month)
    ->whereYear('tgl_presensi', $year)
    ->sum('jumlah_jam');

echo "Total Jam Kerja: {$totalJam}\n";

// Check if there are any presensi records
$presensiRecords = Presensi::where('pegawai_id', $pegawai->id)
    ->whereMonth('tgl_presensi', $month)
    ->whereYear('tgl_presensi', $year)
    ->get();

echo "\nPresensi Records:\n";
foreach ($presensiRecords as $record) {
    echo "- {$record->tgl_presensi}: {$record->jumlah_jam} jam\n";
}

if ($presensiRecords->isEmpty()) {
    echo "No presensi records found for this month.\n";
    echo "This might be why gajiDasar is 0.\n";
}

// Test API endpoint simulation
echo "\n=== API Endpoint Simulation ===\n";
$pegawaiId = $pegawai->id;

$totalJam = Presensi::where('pegawai_id', $pegawaiId)
    ->whereMonth('tgl_presensi', $month)
    ->whereYear('tgl_presensi', $year)
    ->sum('jumlah_jam');

echo "API Response would be:\n";
echo json_encode(['total_jam' => $totalJam]) . "\n";

// Test calculation with actual jam kerja
$tarif = 20000;
$actualGajiDasar = $tarif * $totalJam;
$totalTunjangan = 525000;
$asuransi = 100000;

echo "\nCalculation with actual jam kerja:\n";
echo "Tarif: {$tarif}\n";
echo "Jam Kerja: {$totalJam}\n";
echo "Gaji Dasar: {$actualGajiDasar}\n";
echo "Total Tunjangan: {$totalTunjangan}\n";
echo "Asuransi: {$asuransi}\n";
echo "Total: " . ($actualGajiDasar + $totalTunjangan + $asuransi) . "\n";

if ($totalJam == 0) {
    echo "\nPROBLEM: Jam kerja is 0, so gajiDasar is 0!\n";
    echo "This explains why the total is only 525,000 (tunjangan + asuransi).\n";
}
