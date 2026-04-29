<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIXING PURCHASE JOURNAL ENTRIES ===\n";

// Get the problematic purchase
$purchase = App\Models\Pembelian::find(1);
if (!$purchase) {
    echo "Purchase #1 not found\n";
    exit;
}

echo "Purchase #1: {$purchase->nomor_pembelian}\n";
echo "Payment Method: {$purchase->payment_method}\n";
echo "Bank ID: " . ($purchase->bank_id ?? 'NULL') . "\n";
echo "Total: Rp " . number_format($purchase->total_harga, 0, ',', '.') . "\n";

// Check the bank account
if ($purchase->bank_id) {
    $bankCoa = App\Models\Coa::find($purchase->bank_id);
    if ($bankCoa) {
        echo "Bank Account: [{$bankCoa->kode_akun}] {$bankCoa->nama_akun} (ID: {$bankCoa->id})\n";
    }
}

echo "\n=== RECREATING JOURNAL ENTRY ===\n";

// Delete existing journal entries
App\Services\JournalService::deleteByRef('purchase', $purchase->id);
echo "Deleted existing journal entries\n";

// Recreate journal entries using the fixed method
try {
    App\Services\JournalService::createJournalFromPembelian($purchase);
    echo "✅ Journal entries recreated successfully\n";
} catch (Exception $e) {
    echo "❌ Error recreating journal entries: " . $e->getMessage() . "\n";
    exit;
}

echo "\n=== VERIFYING NEW JOURNAL ENTRIES ===\n";

// Check the new journal entries
$journalEntries = App\Models\JournalEntry::where('ref_type', 'purchase')
    ->where('ref_id', $purchase->id)
    ->with('lines.coa')
    ->get();

if ($journalEntries->count() > 0) {
    echo "New Journal Entries: {$journalEntries->count()}\n";
    foreach ($journalEntries as $entry) {
        echo "  Entry ID: {$entry->id} | Date: {$entry->tanggal} | Memo: {$entry->memo}\n";
        foreach ($entry->lines as $line) {
        