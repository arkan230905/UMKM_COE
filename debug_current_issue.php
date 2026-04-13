<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG CURRENT ISSUE ===\n\n";

// Check if my fixes are actually applied
echo "1. CHECK PEMBAYARAN BEBAN CONTROLLER:\n";
echo "========================================\n";

// Read the current PembayaranBebanController file
$controllerFile = file_get_contents('app/Http/Controllers/Transaksi/PembayaranBebanController.php');

if (strpos($controllerFile, 'Otomatis pilih Kas Bank (111) untuk pembayaran beban') !== false) {
    echo "✓ PembayaranBebanController has been fixed\n";
} else {
    echo "✗ PembayaranBebanController NOT fixed yet\n";
}

echo "\n2. CHECK PEMBELIAN CONTROLLER:\n";
echo "===============================\n";

// Read the current PembelianController file
$pembelianControllerFile = file_get_contents('app/Http/Controllers/PembelianController.php');

if (strpos($pembelianControllerFile, 'Otomatis gunakan Kas Bank (111) untuk pembelian dengan transfer') !== false) {
    echo "✓ PembelianController has been fixed\n";
} else {
    echo "✗ PembelianController NOT fixed yet\n";
}

echo "\n3. CHECK RECENT TRANSACTIONS:\n";
echo "================================\n";

// Check transactions from today
$date = '2026-04-12';

// Check ExpensePayment records
$expensePayments = \App\Models\ExpensePayment::whereDate('tanggal', $date)
    ->with('coa')
    ->get();

echo "ExpensePayment records today:\n";
foreach ($expensePayments as $payment) {
    echo "- ID: " . $payment->id . ", Amount: " . number_format($payment->jumlah, 0, ',', '.') . ", COA: " . ($payment->coa->nama_akun ?? 'N/A') . " (" . ($payment->coa->kode_akun ?? 'N/A') . ")\n";
}

// Check Pembelian records
$pembelian = \App\Models\Pembelian::whereDate('tanggal', $date)
    ->get();

echo "\nPembelian records today:\n";
foreach ($pembelian as $p) {
    echo "- ID: " . $p->id . ", Total: " . number_format($p->total_harga, 0, ',', '.') . ", Payment: " . $p->payment_method . ", Bank ID: " . ($p->bank_id ?? 'N/A') . "\n";
}

echo "\n4. RECOMMENDATION:\n";
echo "==================\n";
echo "If transactions still going to Kas (112) instead of Kas Bank (111):\n";
echo "1. Clear application cache: php artisan cache:clear\n";
echo "2. Check if there are other controllers or methods overriding the logic\n";
echo "3. Verify the forms are using the updated controllers\n";
echo "4. Try creating a new transaction to test the fix\n";
