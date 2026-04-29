<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking valid saldo_normal values in existing COA:\n";
$saldoNormalValues = \App\Models\Coa::distinct()->pluck('saldo_normal')->toArray();
foreach ($saldoNormalValues as $value) {
    echo "- '{$value}'\n";
}

echo "\nChecking some existing COA with their saldo_normal:\n";
$existingCoa = \App\Models\Coa::limit(10)->get();
foreach ($existingCoa as $coa) {
    echo "- {$coa->kode_akun}: saldo_normal = '{$coa->saldo_normal}' (tipe: {$coa->tipe_akun})\n";
}
