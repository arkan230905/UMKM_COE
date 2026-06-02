<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;

$coas = Coa::where('user_id', 20)->orderBy('kode_akun')->get();

echo "Total COA records: " . $coas->count() . "\n\n";

echo "Sample records:\n";
foreach ($coas->take(20) as $coa) {
    echo $coa->kode_akun . " - " . $coa->nama_akun . " (" . $coa->tipe_akun . ")\n";
}

echo "\n\nKey accounts check:\n";
$keyAccounts = ['11', '410', '411', '52', '520', '521', '522', '53', '530', '54', '55', '550', '559'];
foreach ($keyAccounts as $code) {
    $coa = Coa::where('user_id', 20)->where('kode_akun', $code)->first();
    if ($coa) {
        echo "✓ " . $code . " - " . $coa->nama_akun . "\n";
    } else {
        echo "✗ " . $code . " - NOT FOUND\n";
    }
}
