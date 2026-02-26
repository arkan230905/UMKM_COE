<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Cek Tanggal Pembelian ===\n";

// Cek semua pembelian
$allPembelian = \DB::table('pembelians')->get();
echo "Total pembelian: " . $allPembelian->count() . "\n";

foreach ($allPembelian as $p) {
    echo "ID: {$p->id}, Tanggal: {$p->tanggal}, Format: " . date('Y-m-d', strtotime($p->tanggal)) . "\n";
}

echo "\n=== Test Query Tanggal ===\n";
$startDate = '2026-02-01';
$endDate = '2026-02-28';

// Test dengan format yang berbeda
echo "\nTest query dengan tanggal format asli:\n";
$pembelianAsli = \DB::table('pembelians')
    ->whereBetween('tanggal', [$startDate, $endDate])
    ->where('bank_id', 2)
    ->where('payment_method', 'transfer')
    ->sum('total');

echo "Hasil: Rp " . number_format($pembelianAsli, 2) . "\n";

// Test dengan format Y-m-d
echo "\nTest query dengan tanggal format Y-m-d:\n";
$pembelianYmd = \DB::table('pembelians')
    ->whereBetween('tanggal', [\DB::raw('DATE_FORMAT(tanggal, "%Y-%m-%d")'), \DB::raw('DATE_FORMAT("2026-02-01", "%Y-%m-%d")'), \DB::raw('DATE_FORMAT("2026-02-28", "%Y-%m-%d")')])
    ->where('bank_id', 2)
    ->where('payment_method', 'transfer')
    ->sum('total');

echo "Hasil: Rp " . number_format($pembelianYmd, 2) . "\n";

// Test dengan DATE
echo "\nTest query dengan DATE:\n";
$pembelianDate = \DB::table('pembelians')
    ->whereBetween('tanggal', [\DB::raw('DATE("2026-02-01")'), \DB::raw('DATE("2026-02-28")')])
    ->where('bank_id', 2)
    ->where('payment_method', 'transfer')
    ->sum('total');

echo "Hasil: Rp " . number_format($pembelianDate, 2) . "\n";

// Test manual
echo "\nTest manual tanggal 02/02/2026:\n";
$pembelianManual = \DB::table('pembelians')
    ->where('tanggal', '2026-02-02')
    ->where('bank_id', 2)
    ->where('payment_method', 'transfer')
    ->sum('total');

echo "Hasil: Rp " . number_format($pembelianManual, 2) . "\n";
