<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test LaporanKasBankController Real ===\n";

// Simulate LaporanKasBankController index method
$laporanController = new \App\Http\Controllers\LaporanKasBankController();
$request = new \Illuminate\Http\Request([
    'start_date' => '2026-02-01',
    'end_date' => '2026-02-28'
]);

try {
    // Call the index method
    $result = $laporanController->index($request);
    
    echo "✅ LaporanKasBankController executed successfully\n";
    echo "Total Saldo Awal: Rp " . number_format($result['totalSaldoAwal'], 2) . "\n";
    echo "Total Transaksi Masuk: Rp " . number_format($result['totalTransaksiMasuk'], 2) . "\n";
    echo "Total Transaksi Keluar: Rp " . number_format($result['totalTransaksiKeluar'], 2) . "\n";
    echo "Total Saldo Akhir: Rp " . number_format($result['totalSaldoAkhir'], 2) . "\n";
    
    if (isset($result['dataKasBank'])) {
        echo "\nDetail Kas & Bank:\n";
        foreach ($result['dataKasBank'] as $detail) {
            echo "- {$detail['nama_akun']} ({$detail['kode_akun']}): \n";
            echo "  Saldo Awal: Rp " . number_format($detail['saldo_awal'], 2) . "\n";
            echo "  Transaksi Masuk: Rp " . number_format($detail['transaksi_masuk'], 2) . "\n";
            echo "  Transaksi Keluar: Rp " . number_format($detail['transaksi_keluar'], 2) . "\n";
            echo "  Saldo Akhir: Rp " . number_format($detail['saldo_akhir'], 2) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== Expected vs Actual ===\n";
$expectedTotalKeluar = 960000;
$actualTotalKeluar = $result['totalTransaksiKeluar'] ?? 0;

echo "Expected: Rp " . number_format($expectedTotalKeluar, 2) . "\n";
echo "Actual: Rp " . number_format($actualTotalKeluar, 2) . "\n";

if (abs($actualTotalKeluar - $expectedTotalKeluar) < 1000) {
    echo "✅ SESUAI!\n";
} else {
    echo "❌ BELUM SESUAI! Selisih: Rp " . number_format(abs($actualTotalKeluar - $expectedTotalKeluar), 2) . "\n";
}
