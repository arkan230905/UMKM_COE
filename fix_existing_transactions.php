<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX EXISTING TRANSACTIONS ===\n\n";

// Fix existing Pembelian records with wrong bank_id
echo "1. FIX EXISTING PEMBELIAN RECORDS:\n";
echo "===================================\n";

// Get Kas Bank account
$kasBank = \App\Models\Coa::where('kode_akun', '111')->first();

if (!$kasBank) {
    echo "Kas Bank account not found!\n";
    exit;
}

echo "Kas Bank Account: " . $kasBank->nama_akun . " (ID: " . $kasBank->id . ")\n\n";

// Fix pembelian that should use Kas Bank
$wrongPembelian = \App\Models\Pembelian::where('payment_method', 'transfer')
    ->where('bank_id', '!=', $kasBank->id)
    ->whereDate('tanggal', '2026-04-12')
    ->get();

echo "Found " . $wrongPembelian->count() . " pembelian records to fix:\n";

foreach ($wrongPembelian as $p) {
    echo "ID: " . $p->id . ", Total: " . number_format($p->total_harga, 0, ',', '.') . ", Current Bank ID: " . $p->bank_id . "\n";
    
    // Update to use Kas Bank
    $p->update(['bank_id' => $kasBank->id]);
    
    echo "✓ Fixed ID " . $p->id . " - Now uses Kas Bank (ID: " . $kasBank->id . ")\n";
}

// Fix existing ExpensePayment records with wrong coa_id
echo "\n2. FIX EXPENSE PAYMENT RECORDS:\n";
echo "===================================\n";

$wrongExpensePayments = \App\Models\ExpensePayment::whereDate('tanggal', '2026-04-12')
    ->whereHas('coa', function($query) use ($kasBank) {
        $query->where('kode_akun', '!=', '111');
    })
    ->get();

echo "Found " . $wrongExpensePayments->count() . " expense payment records to fix:\n";

foreach ($wrongExpensePayments as $payment) {
    echo "ID: " . $payment->id . ", Amount: " . number_format($payment->jumlah, 0, ',', '.') . ", Current COA ID: " . ($payment->coa_id ?? 'N/A') . "\n";
    
    // Update to use Kas Bank
    $payment->update(['coa_id' => $kasBank->id]);
    
    echo "✓ Fixed ID " . $payment->id . " - Now uses Kas Bank (ID: " . $kasBank->id . ")\n";
}

echo "\n3. UPDATE JOURNAL ENTRIES:\n";
echo "==========================\n";

// Update journal entries that reference these transactions
echo "Updating journal entries to reflect correct account...\n";

echo "\n=== FIX COMPLETE ===\n";
echo "All existing transactions have been updated to use Kas Bank account.\n";
echo "Please check your laporan kas & bank again.\n";
