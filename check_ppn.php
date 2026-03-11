<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking tax accounts in COA...\n";

// Check for PPN
$ppnAccounts = \App\Models\Coa::where('nama_akun', 'like', '%PPN%')->get(['kode_akun', 'nama_akun']);
echo "PPN accounts: " . $ppnAccounts->count() . "\n";
foreach ($ppnAccounts as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun}\n";
}

// Check for Pajak
$pajakAccounts = \App\Models\Coa::where('nama_akun', 'like', '%pajak%')->get(['kode_akun', 'nama_akun']);
echo "\nPajak accounts: " . $pajakAccounts->count() . "\n";
foreach ($pajakAccounts as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun}\n";
}

// Check for common tax codes
$taxCodes = ['2111', '3101'];
echo "\nChecking specific tax codes:\n";
foreach ($taxCodes as $code) {
    $coa = \App\Models\Coa::where('kode_akun', $code)->first();
    if ($coa) {
        echo "  {$coa->kode_akun} - {$coa->nama_akun}\n";
    } else {
        echo "  {$code} - NOT FOUND\n";
    }
}

echo "\nDone.\n";
