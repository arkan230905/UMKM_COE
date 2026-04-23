<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Vendor;

echo "Checking vendors and their categories:\n\n";

$vendors = Vendor::all(['id', 'nama_vendor', 'kategori']);
foreach($vendors as $vendor) {
    echo "- " . $vendor->id . ': ' . $vendor->nama_vendor . " (Kategori: " . ($vendor->kategori ?? 'null') . ")\n";
}

echo "\nTo see Jagung in the dropdown:\n";
echo "1. Select a vendor with kategori 'Bahan Baku'\n";
echo "2. The dropdown will then show all bahan baku items including Jagung\n";