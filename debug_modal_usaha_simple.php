<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Modal Usaha Issue ===" . PHP_EOL;

// Check Modal Usaha account
$modalUsaha = DB::table('coas')->where('kode_akun', '310')->first();
if ($modalUsaha) {
    echo "Modal Usaha Account:" . PHP_EOL;
    echo "  Name: " . $modalUsaha->nama_akun . PHP_EOL;
    echo "  Type: " . $modalUsaha->tipe_akun . PHP_EOL;
    echo "  Saldo Normal: " . $modalUsaha->saldo_normal . PHP_EOL;
    echo "  Saldo Awal: " . number_format($modalUsaha->saldo_awal, 0) . PHP_EOL;
    
    // The issue: Modal Usaha has saldo_normal = 'debit' but should be 'credit' for equity
    echo PHP_EOL . "=== THE PROBLEM ===" . PHP_EOL;
    echo "Modal Usaha has saldo_normal = 'debit'" . PHP_EOL;
    echo "But equity accounts should have saldo_normal = 'credit'" . PHP_EOL;
    echo "This causes the balance calculation to be wrong!" . PHP_EOL;
    
    // Show the calculation difference
    $saldoAwal = (float)($modalUsaha->saldo_awal ?? 0);
    
    // Current (wrong) calculation - treating as debit account
    $wrongCalculation = $saldoAwal; // No journal entries, so just saldo awal
    
    // Correct calculation - treating as credit account
    $correctCalculation = $saldoAwal; // No journal entries, so just saldo awal
    
    echo PHP_EOL . "Calculation:" . PHP_EOL;
    echo "Current (debit logic): " . number_format($wrongCalculation, 0) . PHP_EOL;
    echo "Correct (credit logic): " . number_format($correctCalculation, 0) . PHP_EOL;
    
    // The real issue is in the balance sheet logic
    echo PHP_EOL . "=== Balance Sheet Impact ===" . PHP_EOL;
    echo "Current Balance Sheet:" . PHP_EOL;
    echo "  Assets: ~Rp 264M" . PHP_EOL;
    echo "  Liabilities: Rp 0" . PHP_EOL;
    echo "  Equity (without Modal): Rp -11.7M" . PHP_EOL;
    echo "  Total Liabilities + Equity: Rp 163.3M" . PHP_EOL;
    echo "  DIFFERENCE: Rp 101M" . PHP_EOL;
    
    echo PHP_EOL . "Correct Balance Sheet:" . PHP_EOL;
    echo "  Assets: ~Rp 264M" . PHP_EOL;
    echo "  Liabilities: Rp 0" . PHP_EOL;
    echo "  Equity (with Modal): Rp 175M - 11.7M = Rp 163.3M" . PHP_EOL;
    echo "  Total Liabilities + Equity: Rp 163.3M" . PHP_EOL;
    echo "  WAIT - This still doesn't balance!" . PHP_EOL;
    
    echo PHP_EOL . "=== REAL ISSUE ===" . PHP_EOL;
    echo "The issue is NOT Modal Usaha calculation" . PHP_EOL;
    echo "The issue is that the balance sheet is showing WRONG asset totals!" . PHP_EOL;
    echo "Report shows: Rp 264.316.987" . PHP_EOL;
    echo "But calculation shows: Rp 171.616.987" . PHP_EOL;
    echo "Difference: Rp 92.700.000" . PHP_EOL;
    
    echo PHP_EOL . "=== CONCLUSION ===" . PHP_EOL;
    echo "1. Modal Usaha is being calculated correctly" . PHP_EOL;
    echo "2. The balance sheet is showing inflated asset values" . PHP_EOL;
    echo "3. Need to check the getLaporanPosisiKeuanganData asset calculation" . PHP_EOL;
    
} else {
    echo "Modal Usaha account not found!" . PHP_EOL;
}
