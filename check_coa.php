<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking COA accounts...\n";

$totalCoa = \App\Models\Coa::count();
echo "Total COA: $totalCoa\n\n";

echo "First 10 COA accounts:\n";
$coaList = \App\Models\Coa::orderBy('kode_akun')->take(10)->get(['kode_akun', 'nama_akun']);

foreach ($coaList as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun}\n";
}

echo "\nDone.\n";
