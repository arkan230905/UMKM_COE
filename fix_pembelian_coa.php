<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX PEMBELIAN COA ASSIGNMENT ===\n\n";

// Get Kas Bank account
$kasBank = \App\Models\Coa::where('kode_akun', '111')->first();

if (!$kasBank) {
    echo "Kas Bank account not found!\n";
    exit;
}

echo "Kas Bank Account: " . $kasBank->nama_akun . " (ID: " . $kasBank->id . ")\n\n";

// Find pembelian transactions that should use Kas Bank but are using wrong account
$wrongPembelian = \App\Models\Pembelian::whereDate('tanggal', '2026-04-12')
    ->where('payment_method', 'transfer')
    ->where('bank_id', '!=', $kasBank->id)
    ->get();

echo "Found " . $wrongPembelian->count() . " pembelian transactions to fix:\n";

foreach ($wrongPembelian as $p) {
    echo "ID: " . $p->id . ", Total: " . number_format($p->total_harga, 0, ',', '.') . ", Current Bank ID: " . $p->bank_id . "\n";
    
    // Update to use Kas Bank
    $p->update(['bank_id' => $kasBank->id]);
    
    echo "✓ Fixed ID " . $p->id . " - Now uses Kas Bank (ID: " . $kasBank->id . ")\n";
}

echo "\n=== FIX COMPLETE ===\n";
echo "All pembelian with transfer payment method now use Kas Bank account.\n";
echo "Please check your laporan kas & bank again.\n";
