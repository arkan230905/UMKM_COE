<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG: Simulasi Form Submit ===" . PHP_EOL;

// Simulasi data dari form
$requestData = [
    'tanggal' => '2026-02-03',
    'coa_beban_id' => '503',
    'coa_kasbank' => '101',
    'metode_bayar' => 'cash',
    'nominal' => 100000,
    'deskripsi' => 'Test pembayaran beban'
];

echo "Data yang akan disimpan:" . PHP_EOL;
foreach ($requestData as $key => $value) {
    echo "- {$key}: {$value}" . PHP_EOL;
}

echo PHP_EOL . "=== Validasi Rules ===" . PHP_EOL;

$rules = [
    'tanggal' => 'required|date',
    'coa_beban_id' => 'required|exists:coas,kode_akun',
    'coa_kasbank' => 'required|exists:coas,kode_akun',
    'nominal' => 'required|numeric|min:0',
];

foreach ($rules as $field => $rule) {
    $value = $requestData[$field] ?? null;
    echo "- {$field}: {$rule} | Value: {$value}" . PHP_EOL;
}

echo PHP_EOL . "=== Check COA Exists ===" . PHP_EOL;

use App\Models\Coa;

$coaBeban = Coa::where('kode_akun', $requestData['coa_beban_id'])->first();
$coaKas = Coa::where('kode_akun', $requestData['coa_kasbank'])->first();

echo "COA Beban (503): " . ($coaBeban ? "EXISTS - {$coaBeban->nama_akun}" : "NOT EXISTS") . PHP_EOL;
echo "COA Kas (101): " . ($coaKas ? "EXISTS - {$coaKas->nama_akun}" : "NOT EXISTS") . PHP_EOL;

if ($coaKas) {
    $saldoKas = (float) $coaKas->saldo_awal + (float) ($coaKas->saldo_debit ?? 0) - (float) ($coaKas->saldo_kredit ?? 0);
    echo "Saldo Kas: " . number_format($saldoKas, 0) . PHP_EOL;
    echo "Nominal: " . number_format($requestData['nominal'], 0) . PHP_EOL;
    echo "Saldo Cukup: " . ($saldoKas >= $requestData['nominal'] ? 'YES' : 'NO') . PHP_EOL;
}
