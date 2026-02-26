<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check AccountHelper::getKasBankAccounts() ===\n";

try {
    $akunKasBank = \App\Helpers\AccountHelper::getKasBankAccounts();
    echo "Total akun Kas & Bank: " . $akunKasBank->count() . "\n";
    
    foreach ($akunKasBank as $akun) {
        echo "- {$akun->nama_akun} ({$akun->kode_akun}), ID: {$akun->id}, Saldo Awal: Rp " . number_format($akun->saldo_awal, 2) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Check KAS_BANK_CODES ===\n";
if (class_exists('\App\Helpers\AccountHelper')) {
    $reflection = new ReflectionClass('\App\Helpers\AccountHelper');
    if ($reflection->hasConstant('KAS_BANK_CODES')) {
        echo "KAS_BANK_CODES: " . print_r(\App\Helpers\AccountHelper::KAS_BANK_CODES, true);
    } else {
        echo "❌ KAS_BANK_CODES tidak ada\n";
    }
} else {
    echo "❌ AccountHelper tidak ada\n";
}
