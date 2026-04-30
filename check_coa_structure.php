<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking COA table structure...\n";

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('coas');
echo "COA Table Columns:\n";
foreach ($columns as $column) {
    echo "  - {$column}\n";
}

// Check existing COA data to see kategori_akun values
$existingCoa = \Illuminate\Support\Facades\DB::table('coas')->limit(3)->get();
echo "\nExisting COA Data:\n";
foreach ($existingCoa as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun} - " . ($coa->kategori_akun ?? 'NULL') . "\n";
}

echo "\nCOA structure check completed!\n";
