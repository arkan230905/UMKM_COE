<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test COA Position Columns ===" . PHP_EOL;

// Test the position calculation logic
echo PHP_EOL . "Testing Position Calculation Logic:" . PHP_EOL;

$coas = DB::table('coas')
    ->select('id', 'kode_akun', 'nama_akun', 'tipe_akun')
    ->orderBy('kode_akun')
    ->limit(20)
    ->get();

echo "Sample COA Accounts with Positions:" . PHP_EOL;
foreach ($coas as $coa) {
    // Calculate position using the same logic as controller
    $firstDigit = substr($coa->kode_akun, 0, 1);
    $isDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
    $posisi = $isDebitNormal ? 'Debit' : 'Kredit';
    
    echo $coa->kode_akun . " - " . $coa->nama_akun . PHP_EOL;
    echo "  Type: " . $coa->tipe_akun . PHP_EOL;
    echo "  First Digit: " . $firstDigit . PHP_EOL;
    echo "  Position: " . $posisi . PHP_EOL;
    echo "---" . PHP_EOL;
}

echo PHP_EOL . "=== Position Rules ===" . PHP_EOL;
echo "1xx (Aset): Debit Normal" . PHP_EOL;
echo "2xx (Kewajiban): Kredit Normal" . PHP_EOL;
echo "3xx (Modal): Kredit Normal" . PHP_EOL;
echo "4xx (Pendapatan): Kredit Normal" . PHP_EOL;
echo "5xx (Beban): Debit Normal" . PHP_EOL;
echo "6xx (Beban): Debit Normal" . PHP_EOL;

echo PHP_EOL . "=== Expected Results in COA Page ===" . PHP_EOL;
echo "The COA master data page should now show:" . PHP_EOL;
echo "- Column 'Posisi' between 'Tipe' and 'Saldo Awal'" . PHP_EOL;
echo "- Blue badge for 'Debit' positions" . PHP_EOL;
echo "- Green badge for 'Kredit' positions" . PHP_EOL;
echo "- Correct position based on account code first digit" . PHP_EOL;

echo PHP_EOL . "=== Verification Complete ===" . PHP_EOL;
echo "Changes made:" . PHP_EOL;
echo "1. Updated CoaController::index() to calculate position" . PHP_EOL;
echo "2. Added 'Posisi' column to table header" . PHP_EOL;
echo "3. Added position badge to each table row" . PHP_EOL;
echo "4. Used color coding: Blue=Debit, Green=Kredit" . PHP_EOL;
