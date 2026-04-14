<?php

// Clean up duplicate stock entries
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\KartuStok;

echo "Cleaning Up Duplicate Stock Entries\n";
echo "===================================\n\n";

// Find duplicate retur entries for item 1
$duplicates = KartuStok::where('item_id', 1)
    ->where('item_type', 'bahan_baku')
    ->where('keterangan', 'LIKE', '%Retur Pembelian #PRTN-20260411-0001%')
    ->where('keterangan', 'NOT LIKE', '%Kirim ke Vendor%')
    ->get();

echo "Found {$duplicates->count()} duplicate entries to remove:\n";

foreach ($duplicates as $duplicate) {
    echo "ID: {$duplicate->id} | {$duplicate->tanggal->format('Y-m-d')} | -{$duplicate->qty_keluar} | {$duplicate->keterangan}\n";
    
    // Delete the duplicate
    $duplicate->delete();
    echo "✅ Deleted duplicate entry ID: {$duplicate->id}\n";
}

echo "\nCleanup completed!\n\n";

// Show remaining entries
echo "Remaining stock entries:\n";
$remaining = KartuStok::where('item_id', 1)
    ->where('item_type', 'bahan_baku')
    ->orderBy('tanggal', 'asc')
    ->orderBy('id', 'asc')
    ->get();

foreach ($remaining as $entry) {
    echo "ID: {$entry->id} | {$entry->tanggal->format('Y-m-d')} | ";
    if ($entry->qty_masuk) {
        echo "+{$entry->qty_masuk}";
    } else {
        echo "-{$entry->qty_keluar}";
    }
    echo " | {$entry->keterangan}\n";
}