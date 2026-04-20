<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Presensi;
use App\Models\Pegawai;
use Carbon\Carbon;

echo "=== TESTING ATTENDANCE INTEGRATION ===\n\n";

// Check if there's any presensi data
$totalPresensi = Presensi::count();
echo "Total presensi records: {$totalPresensi}\n\n";

if ($totalPresensi == 0) {
    echo "No presensi data found. Creating sample data...\n";
    
    // Get BTKL employees
    $btklEmployees = Pegawai::where('jenis_pegawai', 'btkl')
        ->orWhere('kategori', 'btkl')
        ->take(3)
        ->get();
    
    if ($btklEmployees->isEmpty()) {
        echo "No BTKL employees found!\n";
        exit;
    }
    
    // Create sample presensi data for current month
    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;
    
    foreach ($btklEmployees as $pegawai) {
        echo "Creating presensi data for: {$pegawai->nama}\n";
        
        // Create 20 working days for this month
        for ($day = 1; $day <= 20; $day++) {
            $tanggal = Carbon::create($currentYear, $currentMonth, $day);
            
            // Skip weekends
            if ($tanggal->isWeekend()) {
                continue;
            }
            
            // Create presensi record
            Presensi::create([
                'pegawai_id' => $pegawai->id,
                'tgl_presensi' => $tanggal->format('Y-m-d'),
                'jam_masuk' => '08:00:00',
                'jam_keluar' => '17:00:00',
                'status' => 'hadir',
                'jumlah_jam' => 8, // 8 hours per day
                'keterangan' => 'Sample data for testing'
            ]);
        }
    }
    
    echo "Sample presensi data created!\n\n";
}

// Test the API endpoint functionality
echo "=== TESTING API FUNCTIONALITY ===\n\n";

// Get a BTKL employee
$btklEmployee = Pegawai::where('jenis_pegawai', 'btkl')
    ->orWhere('kategori', 'btkl')
    ->first();

if (!$btklEmployee) {
    echo "No BTKL employee found for testing!\n";
    exit;
}

echo "Testing with employee: {$btklEmployee->nama} (ID: {$btklEmployee->id})\n";

// Test current month
$currentMonth = Carbon::now()->month;
$currentYear = Carbon::now()->year;

echo "Testing for period: {$currentYear}-{$currentMonth}\n";

// Get presensi data directly from model
$presensiData = Presensi::where('pegawai_id', $btklEmployee->id)
    ->whereMonth('tgl_presensi', $currentMonth)
    ->whereYear('tgl_presensi', $currentYear)
    ->where('status', 'hadir')
    ->get();

$totalJam = 0;
$jumlahHari = 0;

echo "\nPresensi records found:\n";
foreach ($presensiData as $presensi) {
    $jamKerja = $presensi->jumlah_jam;
    echo "- {$presensi->tgl_presensi}: {$presensi->jam_masuk} - {$presensi->jam_keluar} = {$jamKerja} jam\n";
    
    if ($jamKerja > 0) {
        $totalJam += $jamKerja;
        $jumlahHari++;
    }
}

echo "\nSummary:\n";
echo "- Jumlah hari hadir: {$jumlahHari}\n";
echo "- Total jam kerja: {$totalJam}\n";
echo "- Rata-rata jam per hari: " . ($jumlahHari > 0 ? round($totalJam / $jumlahHari, 2) : 0) . "\n";

// Test salary calculation
$tarif = $btklEmployee->jabatanRelasi->tarif_per_jam ?? $btklEmployee->tarif_per_jam ?? 0;
$gajiDasar = $tarif * $totalJam;

echo "\nSalary calculation:\n";
echo "- Tarif per jam: Rp " . number_format($tarif) . "\n";
echo "- Total jam kerja: {$totalJam}\n";
echo "- Gaji dasar: Rp " . number_format($gajiDasar) . "\n";

// Test with different employees
echo "\n=== TESTING ALL BTKL EMPLOYEES ===\n";

$allBtklEmployees = Pegawai::where('jenis_pegawai', 'btkl')
    ->orWhere('kategori', 'btkl')
    ->get();

foreach ($allBtklEmployees as $pegawai) {
    $presensiCount = Presensi::where('pegawai_id', $pegawai->id)
        ->whereMonth('tgl_presensi', $currentMonth)
        ->whereYear('tgl_presensi', $currentYear)
        ->where('status', 'hadir')
        ->count();
    
    $totalJamPegawai = Presensi::where('pegawai_id', $pegawai->id)
        ->whereMonth('tgl_presensi', $currentMonth)
        ->whereYear('tgl_presensi', $currentYear)
        ->where('status', 'hadir')
        ->get()
        ->sum('jumlah_jam');
    
    $tarifPegawai = $pegawai->jabatanRelasi->tarif_per_jam ?? $pegawai->tarif_per_jam ?? 0;
    $gajiDasarPegawai = $tarifPegawai * $totalJamPegawai;
    
    echo "- {$pegawai->nama}: {$presensiCount} hari, {$totalJamPegawai} jam, Rp " . number_format($gajiDasarPegawai) . "\n";
}

echo "\nTest completed!\n";
echo "\nTo test the web interface:\n";
echo "1. Go to /transaksi/penggajian/create\n";
echo "2. Select a BTKL employee\n";
echo "3. Check if 'Total Jam Kerja (Bulan Ini)' field shows the correct hours\n";
echo "4. Verify that 'Gaji Dasar' is calculated correctly (Tarif × Jam Kerja)\n";