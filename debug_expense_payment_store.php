<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ExpensePayment;

echo "=== DEBUG: Expense Payment Data ===" . PHP_EOL;

// Cek semua data expense payment
$allPayments = ExpensePayment::all();

echo "Total expense payments: " . $allPayments->count() . PHP_EOL . PHP_EOL;

foreach ($allPayments as $payment) {
    echo "- ID: {$payment->id}" . PHP_EOL;
    echo "  Tanggal: {$payment->tanggal}" . PHP_EOL;
    echo "  COA Beban: {$payment->coa_beban_id}" . PHP_EOL;
    echo "  COA Kas: {$payment->coa_kasbank}" . PHP_EOL;
    echo "  Nominal: " . number_format($payment->nominal, 0) . PHP_EOL;
    echo "  Deskripsi: {$payment->deskripsi}" . PHP_EOL;
    echo "  User ID: {$payment->user_id}" . PHP_EOL;
    echo "  Created: {$payment->created_at}" . PHP_EOL;
    echo "---" . PHP_EOL;
}

echo PHP_EOL . "=== Test Index Query ===" . PHP_EOL;

// Simulasi query index
$query = ExpensePayment::with([
    'coaBeban' => function($q) {
        $q->select('kode_akun', 'nama_akun');
    },
    'coaKasBank' => function($q) {
        $q->select('kode_akun', 'nama_akun');
    }
]);

$rows = $query->orderBy('tanggal', 'desc')->get();

echo "Total rows from index query: " . $rows->count() . PHP_EOL . PHP_EOL;

foreach ($rows as $row) {
    echo "- ID: {$row->id} | Tanggal: {$row->tanggal}" . PHP_EOL;
    echo "  Beban: " . ($row->coaBeban ? "{$row->coaBeban->kode_akun} - {$row->coaBeban->nama_akun}" : 'NULL') . PHP_EOL;
    echo "  Kas: " . ($row->coaKasBank ? "{$row->coaKasBank->kode_akun} - {$row->coaKasBank->nama_akun}" : 'NULL') . PHP_EOL;
    echo "---" . PHP_EOL;
}
