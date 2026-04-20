<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;

echo "=== CHECKING MISSING COAs ===\n\n";

$codsToCheck = ['210', '2101', '2110', '211', '212'];

foreach ($codsToCheck as $code) {
    $coa = Coa::where('kode_akun', $code)->first();
    if ($coa) {
        echo "✓ COA $code: {$coa->nama_akun}\n";
    } else {
        echo "✗ COA $code: NOT FOUND\n";
    }
}

echo "\n\nAll Liability COAs (2xx):\n";
$liabilities = Coa::where('kode_akun', 'like', '2%')->orderBy('kode_akun')->get();
foreach ($liabilities as $coa) {
    echo "  - {$coa->kode_akun}: {$coa->nama_akun}\n";
}
