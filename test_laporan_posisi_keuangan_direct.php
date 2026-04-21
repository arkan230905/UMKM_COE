<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Test Laporan Posisi Keuangan Direct Method Call ===" . PHP_EOL;

// Test the actual getLaporanPosisiKeuanganData method
$bulan = 4;
$tahun = 2026;

echo "Testing getLaporanPosisiKeuanganData for April 2026..." . PHP_EOL;

// Create an instance of AkuntansiController
$controller = new \App\Http\Controllers\AkuntansiController();

// Call the method directly
try {
    $data = $controller->getLaporanPosisiKeuanganData($bulan, $tahun);
    
    echo PHP_EOL . "=== Results from getLaporanPosisiKeuanganData ===" . PHP_EOL;
    
    if (isset($data['aset_lancar'])) {
        echo "ASET LANCAR:" . PHP_EOL;
        foreach ($data['aset_lancar'] as $account) {
            echo "- " . $account['nama'] . ": Rp " . number_format($account['saldo'], 0) . PHP_EOL;
        }
        echo "Total Aset Lancar: Rp " . number_format($data['total_aset_lancar'], 0) . PHP_EOL;
    } else {
        echo "No aset_lancar data found" . PHP_EOL;
    }
    
    if (isset($data['aset_tidak_lancar'])) {
        echo PHP_EOL . "ASET TIDAK LANCAR:" . PHP_EOL;
        foreach ($data['aset_tidak_lancar'] as $account) {
            echo "- " . $account['nama'] . ": Rp " . number_format($account['saldo'], 0) . PHP_EOL;
        }
        echo "Total Aset Tidak Lancar: Rp " . number_format($data['total_aset_tidak_lancar'], 0) . PHP_EOL;
    } else {
        echo "No aset_tidak_lancar data found" . PHP_EOL;
    }
    
    echo PHP_EOL . "TOTAL ASET: Rp " . number_format($data['total_aset'], 0) . PHP_EOL;
    echo "TOTAL KEWAJIBAN DAN EKUITAS: Rp " . number_format($data['total_kewajiban_dan_ekuitas'], 0) . PHP_EOL;
    
    if (isset($data['balance_check'])) {
        echo "Balance Check: " . $data['balance_check'] . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error calling getLaporanPosisiKeuanganData: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}
