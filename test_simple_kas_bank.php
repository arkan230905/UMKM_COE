<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Simple Logic Dashboard ===\n";

// Test logic yang baru
$akunKasBank = \App\Helpers\AccountHelper::getKasBankAccounts();
$startDate = now()->startOfMonth()->format('Y-m-d');
$endDate = now()->endOfMonth()->format('Y-m-d');

foreach ($akunKasBank as $akun) {
    echo "\n=== {$akun->nama_akun} ({$akun->kode_akun}) ===\n";
    
    // 1. Saldo Awal
    $saldoAwal = (float)$akun->saldo_awal;
    echo "Saldo Awal COA: Rp " . number_format($saldoAwal, 2) . "\n";
    
    // 2. Test query penjualan
    $penjualanMasuk = \DB::table('penjualans')
        ->whereBetween('tanggal', [$startDate, $endDate])
        ->where(function($query) use ($akun) {
            if (stripos($akun->nama_akun, 'kas') !== false) {
                $query->where('payment_method', 'cash');
            } elseif (stripos($akun->nama_akun, 'bank') !== false) {
                $query->where('payment_method', 'transfer');
            }
        })
        ->sum('total');
    
    echo "Penjualan Masuk: Rp " . number_format($penjualanMasuk, 2) . "\n";
    
    // 3. Test query pembelian
    $pembelianKeluar = \DB::table('pembelians')
        ->whereBetween('tanggal', [$startDate, $endDate])
        ->where(function($query) use ($akun) {
            if (stripos($akun->nama_akun, 'kas') !== false) {
                $query->where('payment_method', 'cash');
            } elseif (stripos($akun->nama_akun, 'bank') !== false) {
                $query->where('payment_method', 'transfer');
            }
        })
        ->sum('total');
    
    echo "Pembelian Keluar: Rp " . number_format($pembelianKeluar, 2) . "\n";
    
    // 4. Saldo Akhir
    $saldoAkhir = $saldoAwal + $penjualanMasuk - $pembelianKeluar;
    echo "Saldo Akhir: Rp " . number_format($saldoAkhir, 2) . "\n";
}

echo "\n=== Test LaporanKasBankController Method ===\n";
$laporanController = new \App\Http\Controllers\LaporanKasBankController();
$request = new \Illuminate\Http\Request([
    'start_date' => $startDate,
    'end_date' => $endDate
]);

foreach ($akunKasBank as $akun) {
    echo "\nTesting LaporanKasBankController untuk {$akun->nama_akun}:\n";
    try {
        $saldoAwal = $laporanController->getSaldoAwal($akun, $startDate);
        echo "✅ Saldo awal: Rp " . number_format($saldoAwal, 2) . "\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}
