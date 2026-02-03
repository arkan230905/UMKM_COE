<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ExpensePayment;

echo "=== DEBUG: Expense Payment Data Detail ===" . PHP_EOL;

// Get the first record
$payment = ExpensePayment::with(['coaBeban', 'coaKasBank'])->first();

if ($payment) {
    echo "Payment ID: {$payment->id}" . PHP_EOL;
    echo "Tanggal: {$payment->tanggal}" . PHP_EOL;
    echo "COA Beban ID: {$payment->coa_beban_id}" . PHP_EOL;
    echo "COA Kas ID: {$payment->coa_kasbank}" . PHP_EOL;
    echo "Nominal: {$payment->nominal}" . PHP_EOL;
    echo "Nominal type: " . gettype($payment->nominal) . PHP_EOL;
    echo "Jumlah: " . ($payment->jumlah ?? 'NULL') . PHP_EOL;
    echo "Deskripsi: {$payment->deskripsi}" . PHP_EOL;
    echo "User ID: {$payment->user_id}" . PHP_EOL;
    
    echo PHP_EOL . "=== Relationships ===" . PHP_EOL;
    echo "coaBeban: " . ($payment->coaBeban ? $payment->coaBeban->nama_akun : 'NULL') . PHP_EOL;
    echo "coaKasBank: " . ($payment->coaKasBank ? $payment->coaKasBank->nama_akun : 'NULL') . PHP_EOL;
    
    echo PHP_EOL . "=== All Attributes ===" . PHP_EOL;
    foreach ($payment->getAttributes() as $key => $value) {
        echo "- {$key}: " . (is_null($value) ? 'NULL' : $value) . PHP_EOL;
    }
} else {
    echo "No payment records found" . PHP_EOL;
}
