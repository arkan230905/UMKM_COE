<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "All available COA codes:\n";
$coas = \App\Models\Coa::orderBy('kode_akun')->get();
foreach ($coas as $coa) {
    echo "- {$coa->kode_akun}: {$coa->nama_akun}\n";
}
