<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Coa;

echo "=== DEBUG: COA Fields Structure ===" . PHP_EOL;

$coaKas = Coa::where('kode_akun', '101')->first();

if ($coaKas) {
    echo "COA 101 (Kas) ditemukan:" . PHP_EOL;
    echo "- ID: {$coaKas->id}" . PHP_EOL;
    echo "- Kode: {$coaKas->kode_akun}" . PHP_EOL;
    echo "- Nama: {$coaKas->nama_akun}" . PHP_EOL;
    echo "- Saldo Awal: " . ($coaKas->saldo_awal ?? 'NULL') . PHP_EOL;
    echo "- Saldo Debit: " . ($coaKas->saldo_debit ?? 'NULL') . PHP_EOL;
    echo "- Saldo Kredit: " . ($coaKas->saldo_kredit ?? 'NULL') . PHP_EOL;
    
    echo PHP_EOL . "=== All COA Attributes ===" . PHP_EOL;
    foreach ($coaKas->getAttributes() as $key => $value) {
        echo "- {$key}: " . (is_null($value) ? 'NULL' : $value) . PHP_EOL;
    }
} else {
    echo "COA 101 tidak ditemukan!" . PHP_EOL;
}

echo PHP_EOL . "=== Test Saldo Calculation ===" . PHP_EOL;

if ($coaKas) {
    $saldoKas = (float) ($coaKas->saldo_awal ?? 0) + 
               (float) ($coaKas->saldo_debit ?? 0) - 
               (float) ($coaKas->saldo_kredit ?? 0);
    
    echo "Saldo Kas: " . number_format($saldoKas, 0) . PHP_EOL;
    echo "Nominal 100.000: " . ($saldoKas >= 100000 ? 'CUKUP' : 'TIDAK CUKUP') . PHP_EOL;
}
