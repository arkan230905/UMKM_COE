<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Available COA codes related to PPN:\n";
$coas = \App\Models\Coa::where('nama_akun', 'like', '%PPN%')->orWhere('kode_akun', 'like', '12%')->get();
foreach ($coas as $coa) {
    echo "- {$coa->kode_akun}: {$coa->nama_akun}\n";
}

echo "\nAvailable COA codes related to Biaya Angkut:\n";
$coas = \App\Models\Coa::where('nama_akun', 'like', '%Angkut%')->orWhere('kode_akun', 'like', '511%')->get();
foreach ($coas as $coa) {
    echo "- {$coa->kode_akun}: {$coa->nama_akun}\n";
}
