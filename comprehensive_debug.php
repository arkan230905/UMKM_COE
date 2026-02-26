<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Comprehensive Debug Transaksi Keluar ===\n";

$startDate = '2026-02-01';
$endDate = '2026-02-28';

// Get Bank account
$bank = \App\Models\Coa::where('kode_akun', '1120')->first();
echo "Bank Account: {$bank->nama_akun} ({$bank->kode_akun})\n";

// 1. Test semua tabel yang mungkin ada transaksi keluar
$tablesToCheck = [
    'pembelians' => ['bank_id' => 2, 'payment_method' => 'transfer'],
    'penggajians' => ['coa_kasbank' => 2, 'payment_method' => 'transfer'],
    'penjualan_returns' => ['payment_method' => 'transfer'],
    'expense_payments' => ['coa_kasbank' => 2, 'metode_bayar' => 'transfer'],
    'pengeluaran' => ['coa_kasbank' => 2],
    'biayas' => ['coa_kasbank' => 2],
    'operational_costs' => ['coa_kasbank' => 2]
];

$totalKeluar = 0;

foreach ($tablesToCheck as $table => $conditions) {
    echo "\n=== Tabel: $table ===\n";
    
    if (\Schema::hasTable($table)) {
        $query = \DB::table($table)
            ->whereBetween('tanggal', [$startDate, $endDate]);
            
        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }
        
        $total = $query->sum($table === 'penggajians' ? 'total_gaji' : 'total');
        $totalKeluar += $total;
        
        echo "Total $table: Rp " . number_format($total, 2) . "\n";
    } else {
        echo "Tabel $table tidak ada\n";
    }
}

echo "\n=== Total Semua Transaksi Keluar ===\n";
echo "Total keluar yang ditemukan: Rp " . number_format($totalKeluar, 2) . "\n";
echo "Total keluar yang diharapkan: Rp 960.000\n";

if (abs($totalKeluar - 960000) < 1000) {
    echo "✅ TOTAL KELUAR SUDAH SESUAI!\n";
} else {
    echo "❌ TOTAL KELUAR BELUM SESUAI!\n";
    echo "Selisih: Rp " . number_format(abs($totalKeluar - 960000), 2) . "\n";
}

echo "\n=== Cek Data Manual di Database ===\n";
echo "Cek apakah ada data manual yang menyebabkan pengeluaran 960.000...\n";

// Cek semua journal entries yang mungkin berkaitan
echo "\n=== Journal Entries ===\n";
if (\Schema::hasTable('journal_entries')) {
    $journalEntries = \DB::table('journal_entries')
        ->whereBetween('tanggal', [$startDate, $endDate])
        ->get();
    
    echo "Journal entries count: " . $journalEntries->count() . "\n";
    foreach ($journalEntries->take(5) as $je) {
        echo "- {$je->tanggal}: {$je->keterangan}\n";
    }
}

// Cek apakah ada adjustment manual
echo "\n=== COA Saldo Awal ===\n";
$coaBank = \App\Models\Coa::where('kode_akun', '1120')->first();
echo "COA Bank saldo awal: Rp " . number_format($coaBank->saldo_awal, 2) . "\n";

// Cek coa period balances
echo "\n=== Coa Period Balances ===\n";
if (\Schema::hasTable('coa_period_balances')) {
    $periodBalances = \DB::table('coa_period_balances')
        ->where('kode_akun', '1120')
        ->where('period_id', function($query) {
            $query->where('periode', '2026-02');
        })
        ->get();
    
    foreach ($periodBalances as $pb) {
        echo "Period: {$pb->period_id}, Saldo: Rp " . number_format($pb->saldo_akhir, 2) . "\n";
    }
}
