<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking COA table structure...\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('coas');
foreach ($columns as $column) {
    echo "- {$column}\n";
}

echo "\nChecking existing COA with kategori_akun values:\n";
$existingCoa = \App\Models\Coa::whereNotNull('kategori_akun')->limit(5)->get();
foreach ($existingCoa as $coa) {
    echo "- {$coa->kode_akun}: kategori_akun = '{$coa->kategori_akun}'\n";
}
