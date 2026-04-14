<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECK PAYMENT LOGIC ===\n\n";

// Check ExpensePayment records and their payment methods
echo "1. EXPENSE PAYMENT RECORDS:\n";
echo "==============================\n";
$expensePayments = \App\Models\ExpensePayment::whereDate('tanggal', '2026-04-12')->get();

foreach ($expensePayments as $payment) {
    echo "ID: " . $payment->id . "\n";
    echo "Amount: " . number_format($payment->jumlah, 0, ',', '.') . "\n";
    echo "Payment Method: " . $payment->payment_method . "\n";
    echo "COA ID: " . ($payment->coa_id ?? 'N/A') . "\n";
    echo "----------------------------------------\n";
}

// Check Pembelian records and their payment methods
echo "\n2. PEMBELIAN RECORDS:\n";
echo "=====================\n";
$pembelian = \App\Models\Pembelian::whereDate('tanggal', '2026-04-12')->get();

foreach ($pembelian as $p) {
    echo "ID: " . $p->id . "\n";
    echo "Total: " . number_format($p->total, 0, ',', '.') . "\n";
    echo "Payment Method: " . $p->payment_method . "\n";
    echo "COA KasBank: " . ($p->coa_kasbank ?? 'N/A') . "\n";
    echo "----------------------------------------\n";
}

// Check what COA accounts are available
echo "\n3. AVAILABLE KAS/BANK COA ACCOUNTS:\n";
echo "====================================\n";
$kasBankAccounts = \App\Models\Coa::whereIn('kode_akun', ['111', '112'])->get();

foreach ($kasBankAccounts as $account) {
    echo "ID: " . $account->id . ", Kode: " . $account->kode_akun . ", Nama: " . $account->nama_akun . "\n";
}
