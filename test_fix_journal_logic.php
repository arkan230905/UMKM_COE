<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST FIX JOURNAL LOGIC ===" . PHP_EOL;

// Cek PembelianController line yang sekarang
$controllerFile = file_get_contents('c:\UMKM_COE\app\Http\Controllers\PembelianController.php');

// Cari bagian journal logic
$startPos = strpos($controllerFile, '// Kredit: Kas/Bank (uang keluar)');
if ($startPos) {
    $journalSection = substr($controllerFile, $startPos - 50, 500);
    echo "Current Journal Logic Section:" . PHP_EOL;
    echo "=============================" . PHP_EOL;
    echo $journalSection . PHP_EOL;
    echo "=============================" . PHP_EOL . PHP_EOL;
}

echo "ANALYSIS:" . PHP_EOL;
echo "Yang seharusnya untuk PEMBELIAN:" . PHP_EOL;
echo "- Barang masuk = PERSEDIAAN bertambah = DEBIT" . PHP_EOL;
echo "- Uang keluar = KAS/BANK berkurang = KREDIT" . PHP_EOL . PHP_EOL;

echo "Yang mungkin salah di code:" . PHP_EOL;
echo "- Bank masih di DEBIT (seharusnya KREDIT)" . PHP_EOL;
echo "- Persediaan masih di KREDIT (seharusnya DEBIT)" . PHP_EOL;
