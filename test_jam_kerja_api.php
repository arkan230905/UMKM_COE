<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pegawai;
use App\Models\Presensi;

echo "=== TEST JAM KERJA API ===\n\n";

// Find Ahmad Suryanto
$pegawai = Pegawai::where('nama', 'like', '%Ahmad Suryanto%')->first();

if (!$pegawai) {
    echo "Employee not found!\n";
    exit;
}

echo "Testing API for: {$pegawai->nama} (ID: {$pegawai->id})\n";

// Simulate the API call exactly as it would be made
$pegawaiId = $pegawai->id;
$month = 4; // April
$year = 2026;

echo "API Call: /presensi/jam-kerja?pegawai_id={$pegawaiId}&month={$month}&year={$year}\n";

// This is the exact logic from the API route
$totalJam = Presensi::where('pegawai_id', $pegawaiId)
    ->whereMonth('tgl_presensi', $month)
    ->whereYear('tgl_presensi', $year)
    ->sum('jumlah_jam');

echo "API Response: " . json_encode(['total_jam' => $totalJam]) . "\n";

// Test JavaScript parsing
echo "\nJavaScript parsing simulation:\n";
$apiResponse = json_encode(['total_jam' => $totalJam]);
echo "Raw JSON: {$apiResponse}\n";

$parsed = json_decode($apiResponse, true);
echo "Parsed total_jam: " . ($parsed['total_jam'] ?? 'NULL') . "\n";
echo "Float value: " . (floatval($parsed['total_jam'] ?? 0)) . "\n";

// Check the data type
echo "\nData type analysis:\n";
echo "totalJam type: " . gettype($totalJam) . "\n";
echo "totalJam value: '{$totalJam}'\n";

// Test with different months
echo "\n=== TESTING DIFFERENT MONTHS ===\n";
for ($m = 1; $m <= 12; $m++) {
    $jam = Presensi::where('pegawai_id', $pegawai->id)
        ->whereMonth('tgl_presensi', $m)
        ->whereYear('tgl_presensi', $year)
        ->sum('jumlah_jam');
    
    if ($jam > 0) {
        echo "Month {$m}: {$jam} jam\n";
    }
}

// Check if there's an issue with the date format
echo "\n=== DATE FORMAT CHECK ===\n";
$presensi = Presensi::where('pegawai_id', $pegawai->id)->first();
if ($presensi) {
    echo "First presensi date: {$presensi->tgl_presensi}\n";
    echo "Date format: " . $presensi->tgl_presensi->format('Y-m-d H:i:s') . "\n";
    echo "Month: " . $presensi->tgl_presensi->format('m') . "\n";
    echo "Year: " . $presensi->tgl_presensi->format('Y') . "\n";
}
