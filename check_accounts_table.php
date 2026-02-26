<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Cek Tabel Accounts ===\n";

if (\Schema::hasTable('accounts')) {
    $columns = \Schema::getColumnListing('accounts');
    echo "Kolom di tabel accounts:\n";
    foreach ($columns as $col) {
        echo "- $col\n";
    }
    
    // Cek semua data
    echo "\n=== Semua Data di Tabel Accounts ===\n";
    $accounts = \DB::table('accounts')->get();
    foreach ($accounts as $account) {
        echo "ID: {$account->id}, Code: {$account->code}, Name: " . ($account->name ?? 'N/A') . "\n";
    }
    
    // Cek khusus kode 1110 dan 1120
    echo "\n=== Cek Khusus 1110 dan 1120 ===\n";
    $account1110 = \DB::table('accounts')->where('code', '1110')->first();
    $account1120 = \DB::table('accounts')->where('code', '1120')->first();
    
    if ($account1110) {
        echo "✅ Account 1110 ditemukan - ID: {$account1110->id}, Name: " . ($account1110->name ?? 'N/A') . "\n";
    } else {
        echo "❌ Account 1110 TIDAK ditemukan\n";
    }
    
    if ($account1120) {
        echo "✅ Account 1120 ditemukan - ID: {$account1120->id}, Name: " . ($account1120->name ?? 'N/A') . "\n";
    } else {
        echo "❌ Account 1120 TIDAK ditemukan\n";
    }
} else {
    echo "Tabel accounts tidak ada\n";
}

echo "\n=== Cek COA Data ===\n";
$coas = \App\Models\Coa::whereIn('kode_akun', ['1110', '1120'])->get();
foreach ($coas as $coa) {
    echo "COA: {$coa->nama_akun} ({$coa->kode_akun}), Saldo Awal: Rp " . number_format($coa->saldo_awal, 2) . "\n";
}
