<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$coa = \App\Models\Coa::withoutGlobalScopes()->whereNull('user_id')->where('kode_akun', '11')->first();
if($coa) {
    $coa->nama_akun = 'Aset';
    $coa->saveQuietly();
    echo "✓ Reset nama COA master 11 ke 'Aset'\n";
} else {
    echo "✗ COA master 11 tidak ditemukan\n";
}
