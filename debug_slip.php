<?php
// Debug script untuk slip gaji
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

try {
    // Test database connection
    $penggajian = \App\Models\Penggajian::with(['pegawai', 'bonus', 'tunjangan', 'potongan'])->find(5);
    
    if (!$penggajian) {
        echo "Penggajian dengan ID 5 tidak ditemukan\n";
        exit;
    }
    
    echo "Penggajian ditemukan: " . $penggajian->id . "\n";
    echo "Pegawai: " . $penggajian->pegawai->nama . "\n";
    echo "Jenis Pegawai: " . $penggajian->pegawai->jenis_pegawai . "\n";
    
    // Test calculateSlipData
    $controller = new \App\Http\Controllers\PenggajianController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('calculateSlipData');
    $method->setAccessible(true);
    
    $slipData = $method->invoke($controller, $penggajian);
    
    echo "\nSlip Data berhasil dihitung:\n";
    echo "- Total Pendapatan: " . $slipData['total_pendapatan'] . "\n";
    echo "- Total Potongan: " . $slipData['total_potongan'] . "\n";
    echo "- Gaji Bersih: " . $slipData['gaji_bersih'] . "\n";
    echo "- Jenis Pegawai: " . $slipData['jenis_pegawai'] . "\n";
    
    echo "\nTest berhasil!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
