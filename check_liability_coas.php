<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Coa;

$codes = ['21', '210', '211', '212', '2101'];

echo "Checking liability COAs:\n\n";

foreach ($codes as $code) {
    $exists = Coa::where('kode_akun', $code)->exists();
    echo "COA {$code}: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
    
    if ($exists) {
        $coa = Coa::where('kode_akun', $code)->first();
        echo "  - Name: {$coa->nama_akun}\n";
        echo "  - Type: {$coa->tipe_akun}\n";
    }
    echo "\n";
}