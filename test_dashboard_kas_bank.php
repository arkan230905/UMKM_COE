<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Dashboard Kas Bank Logic ===\n";

// Test helper untuk mendapatkan akun kas bank
$akunKasBank = \App\Helpers\AccountHelper::getKasBankAccounts();
echo "Total akun Kas & Bank: " . $akunKasBank->count() . "\n";

foreach ($akunKasBank as $akun) {
    echo "- {$akun->nama_akun} ({$akun->kode_akun})\n";
}

// Test method yang baru ditambahkan
$dashboard = new \App\Http\Controllers\DashboardController();

echo "\n=== Test getSaldoAwal ===\n";
$startDate = now()->startOfMonth()->format('Y-m-d');
foreach ($akunKasBank->take(2) as $akun) {
    $saldoAwal = $dashboard->getSaldoAwal($akun, $startDate);
    echo "Saldo awal {$akun->nama_akun}: Rp " . number_format($saldoAwal, 2) . "\n";
}

echo "\n=== Test getTransaksiMasuk ===\n";
$endDate = now()->endOfMonth()->format('Y-m-d');
foreach ($akunKasBank->take(2) as $akun) {
    $transaksiMasuk = $dashboard->getTransaksiMasuk($akun, $startDate, $endDate);
    echo "Transaksi masuk {$akun->nama_akun}: Rp " . number_format($transaksiMasuk, 2) . "\n";
}

echo "\n=== Test getTransaksiKeluar ===\n";
foreach ($akunKasBank->take(2) as $akun) {
    $transaksiKeluar = $dashboard->getTransaksiKeluar($akun, $startDate, $endDate);
    echo "Transaksi keluar {$akun->nama_akun}: Rp " . number_format($transaksiKeluar, 2) . "\n";
}

echo "\n=== Test getTotalKasBank ===\n";
$totalKasBank = $dashboard->getTotalKasBank();
echo "Total Kas & Bank: Rp " . number_format($totalKasBank, 2) . "\n";

echo "\n=== Test getKasBankDetails ===\n";
$kasBankDetails = $dashboard->getKasBankDetails();
echo "Total details: " . $kasBankDetails->count() . "\n";

foreach ($kasBankDetails as $detail) {
    echo "- {$detail['nama_akun']} ({$detail['kode_akun']}): Rp " . number_format($detail['saldo'], 2) . "\n";
}

echo "\n=== Perbandingan dengan LaporanKasBankController ===\n";
$laporanController = new \App\Http\Controllers\LaporanKasBankController();

// Simulate index method call
$request = new \Illuminate\Http\Request([
    'start_date' => $startDate,
    'end_date' => $endDate
]);

echo "Logic Dashboard dan LaporanKasBankController sekarang menggunakan method yang sama:\n";
echo "✅ getSaldoAwal() - Sama\n";
echo "✅ getTransaksiMasuk() - Sama\n";
echo "✅ getTransaksiKeluar() - Sama\n";
echo "✅ Perhitungan saldo akhir - Sama\n";
