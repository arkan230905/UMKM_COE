<?php
/**
 * Debug Trial Balance Mapping Logic
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;
use App\Models\JournalEntry;
use App\Services\TrialBalanceService;
use Illuminate\Support\Facades\DB;

echo "=== DEBUG TRIAL BALANCE MAPPING ===\n\n";

$trialBalanceService = new TrialBalanceService();
$result = $trialBalanceService->calculateTrialBalance('2026-04-01', '2026-04-30');

echo "=== DETAIL MAPPING SETIAP AKUN ===\n";

$totalDebugDebit = 0;
$totalDebugKredit = 0;
$totalSaldoAkhir = 0;

foreach ($result['accounts'] as $account) {
    $kodeAkun = $account['kode_akun'];
    $namaAkun = $account['nama_akun'];
    $saldoAkhir = $account['saldo_akhir'];
    $debitDisplay = $account['debit'];
    $kreditDisplay = $account['kredit'];
    $isDebitNormal = $account['is_debit_normal'];
    $source = $account['source'] ?? 'periode';
    
    $totalDebugDebit += $debitDisplay;
    $totalDebugKredit += $kreditDisplay;
    $totalSaldoAkhir += $saldoAkhir;
    
    // Hanya tampilkan akun dengan saldo atau aktivitas
    if (abs($saldoAkhir) > 0.01 || $debitDisplay > 0.01 || $kreditDisplay > 0.01) {
        echo "Akun: {$kodeAkun} - {$namaAkun}\n";
        echo "  Saldo Akhir: Rp " . number_format($saldoAkhir, 0, ',', '.') . "\n";
        echo "  Normal Balance: " . ($isDebitNormal ? 'DEBIT' : 'KREDIT') . "\n";
        echo "  Source: {$source}\n";
        echo "  Display -> Debit: Rp " . number_format($debitDisplay, 0, ',', '.') . 
             " | Kredit: Rp " . number_format($kreditDisplay, 0, ',', '.') . "\n";
        
        // Validasi mapping
        $expectedDebit = 0;
        $expectedKredit = 0;
        
        if ($saldoAkhir == 0) {
            $expectedDebit = 0;
            $expectedKredit = 0;
        } elseif ($saldoAkhir > 0) {
            if ($isDebitNormal) {
                $expectedDebit = $saldoAkhir;
            } else {
                $expectedKredit = $saldoAkhir;
            }
        } else {
            // Saldo negatif (abnormal)
            $nilaiAbsolut = abs($saldoAkhir);
            if ($isDebitNormal) {
                $expectedKredit = $nilaiAbsolut;
            } else {
                $expectedDebit = $nilaiAbsolut;
            }
        }
        
        $mappingCorrect = (abs($debitDisplay - $expectedDebit) < 0.01) && (abs($kreditDisplay - $expectedKredit) < 0.01);
        
        echo "  Expected -> Debit: Rp " . number_format($expectedDebit, 0, ',', '.') . 
             " | Kredit: Rp " . number_format($expectedKredit, 0, ',', '.') . "\n";
        echo "  Mapping: " . ($mappingCorrect ? '✅ BENAR' : '❌ SALAH') . "\n\n";
    }
}

echo "=== RINGKASAN TOTAL ===\n";
echo "Total Saldo Akhir Semua Akun: Rp " . number_format($totalSaldoAkhir, 0, ',', '.') . "\n";
echo "Total Debit Display: Rp " . number_format($totalDebugDebit, 0, ',', '.') . "\n";
echo "Total Kredit Display: Rp " . number_format($totalDebugKredit, 0, ',', '.') . "\n";
echo "Selisih Display: Rp " . number_format($totalDebugDebit - $totalDebugKredit, 0, ',', '.') . "\n\n";

echo "=== ANALISIS PER KATEGORI ===\n";

$categories = [
    'ASET' => ['saldo_total' => 0, 'debit_display' => 0, 'kredit_display' => 0],
    'KEWAJIBAN' => ['saldo_total' => 0, 'debit_display' => 0, 'kredit_display' => 0],
    'MODAL' => ['saldo_total' => 0, 'debit_display' => 0, 'kredit_display' => 0],
    'PENDAPATAN' => ['saldo_total' => 0, 'debit_display' => 0, 'kredit_display' => 0],
    'BEBAN' => ['saldo_total' => 0, 'debit_display' => 0, 'kredit_display' => 0]
];

foreach ($result['accounts'] as $account) {
    $firstDigit = substr($account['kode_akun'], 0, 1);
    $saldoAkhir = $account['saldo_akhir'];
    $debitDisplay = $account['debit'];
    $kreditDisplay = $account['kredit'];
    
    if ($firstDigit == '1') {
        $categories['ASET']['saldo_total'] += $saldoAkhir;
        $categories['ASET']['debit_display'] += $debitDisplay;
        $categories['ASET']['kredit_display'] += $kreditDisplay;
    } elseif ($firstDigit == '2') {
        $categories['KEWAJIBAN']['saldo_total'] += $saldoAkhir;
        $categories['KEWAJIBAN']['debit_display'] += $debitDisplay;
        $categories['KEWAJIBAN']['kredit_display'] += $kreditDisplay;
    } elseif ($firstDigit == '3') {
        $categories['MODAL']['saldo_total'] += $saldoAkhir;
        $categories['MODAL']['debit_display'] += $debitDisplay;
        $categories['MODAL']['kredit_display'] += $kreditDisplay;
    } elseif ($firstDigit == '4') {
        $categories['PENDAPATAN']['saldo_total'] += $saldoAkhir;
        $categories['PENDAPATAN']['debit_display'] += $debitDisplay;
        $categories['PENDAPATAN']['kredit_display'] += $kreditDisplay;
    } elseif (in_array($firstDigit, ['5', '6'])) {
        $categories['BEBAN']['saldo_total'] += $saldoAkhir;
        $categories['BEBAN']['debit_display'] += $debitDisplay;
        $categories['BEBAN']['kredit_display'] += $kreditDisplay;
    }
}

foreach ($categories as $category => $data) {
    echo "{$category}:\n";
    echo "  Saldo Total: Rp " . number_format($data['saldo_total'], 0, ',', '.') . "\n";
    echo "  Debit Display: Rp " . number_format($data['debit_display'], 0, ',', '.') . "\n";
    echo "  Kredit Display: Rp " . number_format($data['kredit_display'], 0, ',', '.') . "\n";
    echo "  Net Display: Rp " . number_format($data['debit_display'] - $data['kredit_display'], 0, ',', '.') . "\n\n";
}

echo "=== EXPECTED BALANCE CHECK ===\n";
echo "Untuk neraca saldo seimbang, yang diharapkan:\n";
echo "Total Debit Display = Total Kredit Display\n\n";

echo "Berdasarkan prinsip akuntansi:\n";
echo "ASET + BEBAN = KEWAJIBAN + MODAL + PENDAPATAN\n";
echo "Rp " . number_format($categories['ASET']['saldo_total'] + $categories['BEBAN']['saldo_total'], 0, ',', '.') . 
     " = Rp " . number_format($categories['KEWAJIBAN']['saldo_total'] + $categories['MODAL']['saldo_total'] + $categories['PENDAPATAN']['saldo_total'], 0, ',', '.') . "\n";

$leftSide = $categories['ASET']['saldo_total'] + $categories['BEBAN']['saldo_total'];
$rightSide = $categories['KEWAJIBAN']['saldo_total'] + $categories['MODAL']['saldo_total'] + $categories['PENDAPATAN']['saldo_total'];
$accountingBalance = $leftSide - $rightSide;

echo "Selisih persamaan akuntansi: Rp " . number_format($accountingBalance, 0, ',', '.') . "\n";

if (abs($accountingBalance) < 0.01) {
    echo "✅ Persamaan akuntansi seimbang\n";
    echo "Maka neraca saldo juga harus seimbang jika mapping benar\n";
} else {
    echo "❌ Persamaan akuntansi tidak seimbang\n";
    echo "Ini menunjukkan ada masalah di data atau jurnal\n";
}