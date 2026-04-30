<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking COA table structure in detail...\n";

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('coas');
echo "COA Table Columns:\n";
foreach ($columns as $column) {
    echo "  - {$column}\n";
}

// Check existing COA 56
echo "\n=== Checking existing COA 56 ===\n";
$coa56 = \Illuminate\Support\Facades\DB::table('coas')
    ->where('kode_akun', '56')
    ->where('user_id', 1)
    ->first();

if ($coa56) {
    echo "COA 56 exists: " . $coa56->nama_akun . "\n";
} else {
    echo "COA 56 does not exist\n";
}

// Show sample COA data
echo "\n=== Sample COA Data ===\n";
$sampleCoas = \Illuminate\Support\Facades\DB::table('coas')
    ->where('user_id', 1)
    ->limit(3)
    ->get();

foreach ($sampleCoas as $coa) {
    echo "  " . $coa->kode_akun . " - " . $coa->nama_akun . " (" . $coa->tipe_akun . ")\n";
}

echo "\nCOA structure check completed!\n";
