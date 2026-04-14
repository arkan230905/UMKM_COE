<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== COA PPN DAN KAS ===\n";
$coas = \App\Models\Coa::whereIn('kode_akun', ['1130', '112', '1120', '1121', '2110'])
    ->orWhere('nama_akun', 'like', '%ppn%')
    ->orWhere('nama_akun', 'like', '%kas%')
    ->orderBy('kode_akun')
    ->get();

foreach ($coas as $coa) {
    echo "{$coa->kode_akun} - {$coa->nama_akun}\n";
}

?>