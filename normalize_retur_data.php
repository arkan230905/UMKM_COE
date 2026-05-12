<?php

// Simple script to normalize retur data
// Run this once to fix existing data

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseReturn;

echo "Normalizing PurchaseReturn data...\n";

$returs = PurchaseReturn::all();
$updated = 0;

foreach ($returs as $retur) {
    $needsUpdate = false;
    $originalStatus = $retur->status;
    $originalJenis = $retur->jenis_retur;
    
    // Normalize status
    if ($retur->status === 'menunggu_acc') {
        $retur->status = 'pending';
        $needsUpdate = true;
    }
    
    // Normalize jenis_retur
    $jenisLower = strtolower($retur->jenis_retur);
    if (str_contains($jenisLower, 'refund') || str_contains($jenisLower, 'pengembalian')) {
        $retur->jenis_retur = 'refund';
        $needsUpdate = true;
    } elseif (str_contains($jenisLower, 'tukar') || str_contains($jenisLower, 'exchange')) {
        $retur->jenis_retur = 'tukar_barang';
        $needsUpdate = true;
    }
    
    if ($needsUpdate) {
        $retur->save();
        $updated++;
        echo "Updated Retur ID {$retur->id}: status '{$originalStatus}' -> '{$retur->status}', jenis '{$originalJenis}' -> '{$retur->jenis_retur}'\n";
    }
}

echo "Normalization complete. Updated {$updated} records.\n";

// Show current data
echo "\nCurrent retur data:\n";
$returs = PurchaseReturn::all();
foreach ($returs as $retur) {
    echo "ID: {$retur->id}, Status: {$retur->status}, Jenis: {$retur->jenis_retur}\n";
}