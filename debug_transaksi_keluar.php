<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Transaksi Keluar ===\n";

$startDate = '2026-02-01';
$endDate = '2026-02-28';

// Get Bank account
$bank = \App\Models\Coa::where('kode_akun', '1120')->first();
echo "Bank Account: {$bank->nama_akun} ({$bank->kode_akun})\n";

// Test pembelian keluar
echo "\n=== Test Pembelian Keluar ===\n";
$pembelianKeluar = \DB::table('pembelians')
    ->whereBetween('tanggal', [$startDate, $endDate])
    ->where(function($query) use ($bank) {
        if (stripos($bank->nama_akun, 'bank') !== false) {
            $query->where('payment_method', 'transfer');
        }
    })
    ->sum('total');

echo "Pembelian keluar (transfer): Rp " . number_format($pembelianKeluar, 2) . "\n";

// Test semua pembelian (tanpa filter)
echo "\n=== Test Semua Pembelian ===\n";
$allPembelian = \DB::table('pembelians')
    ->whereBetween('tanggal', [$startDate, $endDate])
    ->sum('total');

echo "Semua pembelian: Rp " . number_format($allPembelian, 2) . "\n";

// Test penggajian keluar
echo "\n=== Test Penggajian Keluar ===\n";
try {
    $penggajianKeluar = \DB::table('penggajians')
        ->whereBetween('tanggal', [$startDate, $endDate])
        ->where(function($query) use ($bank) {
            if (stripos($bank->nama_akun, 'bank') !== false) {
                $query->where('payment_method', 'transfer');
            }
        })
        ->sum('total_gaji');
    
    echo "Penggajian keluar (transfer): Rp " . number_format($penggajianKeluar, 2) . "\n";
} catch (Exception $e) {
    echo "Penggajian table tidak ada: " . $e->getMessage() . "\n";
}

// Test retur penjualan keluar
echo "\n=== Test Retur Penjualan Keluar ===\n";
try {
    $returPenjualanKeluar = \DB::table('penjualan_returns')
        ->whereBetween('tanggal', [$startDate, $endDate])
        ->where(function($query) use ($bank) {
            $query->where(function($subQuery) use ($bank) {
                if (stripos($bank->nama_akun, 'bank') !== false) {
                    $subQuery->where('payment_method', 'transfer');
                }
            });
        })
        ->sum('total_refund');
    
    echo "Retur penjualan keluar (transfer): Rp " . number_format($returPenjualanKeluar, 2) . "\n";
} catch (Exception $e) {
    echo "Penjualan returns table tidak ada: " . $e->getMessage() . "\n";
}

// Test expense payments
echo "\n=== Test Expense Payments ===\n";
try {
    $expensePayments = \DB::table('expense_payments')
        ->whereBetween('tanggal', [$startDate, $endDate])
        ->where(function($query) use ($bank) {
            if (stripos($bank->nama_akun, 'bank') !== false) {
                $query->where('payment_method', 'transfer');
            }
        })
        ->sum('amount');
    
    echo "Expense payments (transfer): Rp " . number_format($expensePayments, 2) . "\n";
} catch (Exception $e) {
    echo "Expense payments table tidak ada: " . $e->getMessage() . "\n";
}

echo "\n=== Total yang Harus Ditemukan ===\n";
$totalKeluarExpected = 960000;
$totalKeluarFound = $pembelianKeluar + ($penggajianKeluar ?? 0) + ($returPenjualanKeluar ?? 0) + ($expensePayments ?? 0);

echo "Total keluar yang ditemukan: Rp " . number_format($totalKeluarFound, 2) . "\n";
echo "Total keluar yang diharapkan: Rp " . number_format($totalKeluarExpected, 2) . "\n";

if (abs($totalKeluarFound - $totalKeluarExpected) < 1000) {
    echo "✅ TOTAL KELUAR SUDAH SESUAI!\n";
} else {
    echo "❌ TOTAL KELUAR BELUM SESUAI!\n";
    echo "Selisih: Rp " . number_format(abs($totalKeluarFound - $totalKeluarExpected), 2) . "\n";
}
