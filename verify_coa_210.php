<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Coa;

$coa = Coa::where('kode_akun', '210')->first();

if ($coa) {
    echo "✅ COA 210 EXISTS\n";
    echo "   Name: {$coa->nama_akun}\n";
    echo "   Type: {$coa->tipe_akun}\n";
    echo "   Category: {$coa->kategori_akun}\n";
} else {
    echo "❌ COA 210 NOT FOUND\n";
}

// Also check if COA 2101 is deleted
$coa2101 = Coa::where('kode_akun', '2101')->first();
echo "\nCOA 2101: " . ($coa2101 ? "STILL EXISTS (should be deleted)" : "DELETED ✅") . "\n";