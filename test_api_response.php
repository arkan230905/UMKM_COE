<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\PenggajianController;
use App\Models\Pegawai;

echo "=== API RESPONSE TEST ===\n\n";

// Find Budi Susanto
$pegawai = Pegawai::where('nama', 'like', '%Budi Susanto%')->first();

if (!$pegawai) {
    echo "Employee not found!\n";
    exit;
}

echo "Testing API response for employee ID: {$pegawai->id}\n\n";

// Create controller instance and test the API method
$controller = new PenggajianController();

try {
    $response = $controller->getEmployeeData($pegawai->id);
    
    echo "API Response:\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "Parsed Values:\n";
    $data = $response->original;
    echo "Jenis: " . ($data['jenis'] ?? 'NULL') . "\n";
    echo "Gaji Pokok: " . ($data['gaji_pokok'] ?? 'NULL') . "\n";
    echo "Tarif: " . ($data['tarif'] ?? 'NULL') . "\n";
    echo "Total Tunjangan: " . ($data['total_tunjangan'] ?? 'NULL') . "\n";
    echo "Asuransi: " . ($data['asuransi'] ?? 'NULL') . "\n";
    echo "Nama: " . ($data['nama'] ?? 'NULL') . "\n";
    echo "Jabatan: " . ($data['jabatan_nama'] ?? 'NULL') . "\n";
    
    // Test JavaScript calculation
    $jenis = $data['jenis'] ?? 'btktl';
    $tarif = floatval($data['tarif'] ?? 0);
    $totalTunjangan = floatval($data['total_tunjangan'] ?? 0);
    $asuransi = floatval($data['asuransi'] ?? 0);
    $jamKerja = 7; // Default jam kerja
    
    echo "\nJavaScript Calculation Simulation:\n";
    echo "Jenis: {$jenis}\n";
    echo "Tarif: {$tarif}\n";
    echo "Total Tunjangan: {$totalTunjangan}\n";
    echo "Asuransi: {$asuransi}\n";
    echo "Jam Kerja: {$jamKerja}\n";
    
    $gajiDasar = $tarif * $jamKerja;
    echo "Gaji Dasar (Tarif × Jam Kerja): {$gajiDasar}\n";
    
    $total = $gajiDasar + $totalTunjangan + $asuransi;
    echo "Total: {$total}\n";
    echo "Formatted: Rp " . number_format($total, 0, ',', '.') . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
