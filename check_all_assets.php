<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Aset;

echo "All assets with their methods:\n";
echo "=============================\n";

$assets = Aset::orderBy('id', 'desc')->take(5)->get();

foreach ($assets as $aset) {
    echo "ID: " . $aset->id . " - " . $aset->nama_aset . " - Metode: " . $aset->metode_penyusutan . "\n";
}
