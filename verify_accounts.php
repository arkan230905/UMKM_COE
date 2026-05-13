<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 VERIFIKASI TABEL ACCOUNTS\n";
echo str_repeat("=", 60) . "\n\n";

// Cek total records
$total = DB::table('accounts')->count();
echo "✅ Total akun: $total\n\n";

// Tampilkan 10 data pertama
echo "📋 Sample Data (10 pertama):\n";
echo str_repeat("-", 60) . "\n";

$accounts = DB::table('accounts')
    ->limit(10)
    ->get(['kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal']);

foreach ($accounts as $account) {
    echo sprintf(
        "%-10s | %-30s | %-12s | %-6s | %s\n",
        $account->kode_akun,
        substr($account->nama_akun, 0, 30),
        $account->tipe_akun,
        $account->saldo_normal,
        number_format($account->saldo_awal, 2)
    );
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ VERIFIKASI SELESAI!\n";
