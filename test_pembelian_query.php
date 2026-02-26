<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Pembelian Query ===\n";

$startDate = '2026-02-01';
$endDate = '2026-02-28';

// Test query pembelian dengan bank_id = 2
echo "\n=== Test Pembelian dengan Bank ID 2 ===\n";
$pembelianBank2 = \DB::table('pembelians')
    ->whereBetween('tanggal', [$startDate, $endDate])
    ->where('bank_id', 2)  // Bank
    ->sum('total');

echo "Pembelian dengan bank_id=2: Rp " . number_format($pembelianBank2, 2) . "\n";

// Test query pembelian dengan payment_method = transfer
echo "\n=== Test Pembelian dengan Payment Method Transfer ===\n";
$pembelianTransfer = \DB::table('pembelians')
    ->whereBetween('tanggal', [$startDate, $endDate])
    ->where('payment_method', 'transfer')
    ->sum('total');

echo "Pembelian dengan payment_method=transfer: Rp " . number_format($pembelianTransfer, 2) . "\n";

// Test query pembelian dengan kedua kondisi
echo "\n=== Test Pembelian dengan Bank ID 2 AND Payment Method Transfer ===\n";
$pembelianBoth = \DB::table('pembelians')
    ->whereBetween('tanggal', [$startDate, $endDate])
    ->where('bank_id', 2)
    ->where('payment_method', 'transfer')
    ->sum('total');

echo "Pembelian dengan bank_id=2 AND payment_method=transfer: Rp " . number_format($pembelianBoth, 2) . "\n";

// Test semua pembelian
echo "\n=== Test Semua Pembelian ===\n";
$allPembelian = \DB::table('pembelians')
    ->whereBetween('tanggal', [$startDate, $endDate])
    ->sum('total');

echo "Semua pembelian: Rp " . number_format($allPembelian, 2) . "\n";

// Test query yang digunakan LaporanKasBankController
echo "\n=== Test Query LaporanKasBankController Style ===\n";
$pembelianLaporanStyle = \DB::table('pembelians')
    ->whereBetween('tanggal', [$startDate, $endDate])
    ->where(function($query) {
        $query->where('bank_id', 2);  // Bank
        $query->where('payment_method', 'transfer');
    })
    ->sum('total');

echo "Pembelian LaporanKasBankController style: Rp " . number_format($pembelianLaporanStyle, 2) . "\n";

echo "\n=== Kesimpulan ===\n";
echo "Total pembelian yang ditemukan: Rp " . number_format($pembelianLaporanStyle, 2) . "\n";
echo "Total pembelian yang diharapkan: Rp 960.000\n";

if ($pembelianLaporanStyle >= 960000) {
    echo "✅ PEMBELIAN SUDAH SESUAI!\n";
} else {
    echo "❌ PEMBELIAN BELUM SESUAI!\n";
    echo "Selisih: Rp " . number_format(960000 - $pembelianLaporanStyle, 2) . "\n";
}
