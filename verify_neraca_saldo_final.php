<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Verify Neraca Saldo Final ===" . PHP_EOL;

// Test neraca saldo with corrected positions
echo PHP_EOL . "Testing Neraca Saldo with Corrected Account Positions:" . PHP_EOL;

$bulan = 4;
$tahun = 2026;
$from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

echo "Period: April 2026" . PHP_EOL;
echo "Expected: All accounts should show correct balances with proper positions" . PHP_EOL;

// Get COA data
$coas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
    ->orderBy('kode_akun')
    ->get();

echo PHP_EOL . "=== Key Accounts Verification ===" . PHP_EOL;

$keyAccounts = [
    '111' => 'Kas Bank',
    '112' => 'Kas', 
    '1141' => 'Persediaan Ayam Potong',
    '1142' => 'Persediaan Ayam Kampung',
    '1143' => 'Persediaan Bebek',
    '1152' => 'Persediaan Tepung Terigu',
    '310' => 'Modal Usaha',
    '2101' => 'Hutang Usaha',
    '41' => 'PENDAPATAN'
];

foreach ($keyAccounts as $kodeAkun => $nama) {
    echo PHP_EOL . "COA " . $kodeAkun . " - " . $nama . ":" . PHP_EOL;
    
    $coa = $coas->where('kode_akun', $kodeAkun)->first();
    if (!$coa) {
        echo "  NOT FOUND" . PHP_EOL;
        continue;
    }
    
    echo "  Type: " . $coa->tipe_akun . PHP_EOL;
    echo "  Saldo Normal: " . $coa->saldo_normal . PHP_EOL;
    
    // Check position logic
    $firstDigit = substr($coa->kode_akun, 0, 1);
    $isDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
    $expectedPosition = $isDebitNormal ? 'Debit' : 'Kredit';
    
    echo "  Expected Position: " . $expectedPosition . PHP_EOL;
    
    // Calculate balance using neraca saldo logic
    $saldoAwal = 0;
    
    // For inventory accounts, use getInventorySaldoAwal
    if (in_array($kodeAkun, ['1141', '1142', '1143', '1152', '1153', '1154', '1155', '1156'])) {
        if (in_array($kodeAkun, ['1141', '1142', '1143'])) {
            $saldoAwal = DB::table('bahan_bakus')
                ->where('coa_persediaan_id', $kodeAkun)
                ->where('saldo_awal', '>', 0)
                ->sum(DB::raw('saldo_awal * harga_satuan'));
        } elseif (in_array($kodeAkun, ['1152', '1153', '1154', '1155', '1156'])) {
            $saldoAwal = DB::table('bahan_pendukungs')
                ->where('coa_persediaan_id', $kodeAkun)
                ->where('saldo_awal', '>', 0)
                ->sum(DB::raw('saldo_awal * harga_satuan'));
        }
    } else {
        $saldoAwal = (float)($coa->saldo_awal ?? 0);
    }
    
    // Get journal totals
    $totalDebit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $kodeAkun)
        ->sum('journal_lines.debit');
        
    $totalKredit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $kodeAkun)
        ->sum('journal_lines.credit');
    
    // Calculate final balance using corrected position logic
    if ($isDebitNormal) {
        $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
    } else {
        $saldoAkhir = $saldoAwal + $totalKredit - $totalDebit;
    }
    
    echo "  Saldo Awal: " . number_format($saldoAwal, 0) . PHP_EOL;
    echo "  Total Debit: " . number_format($totalDebit, 0) . PHP_EOL;
    echo "  Total Credit: " . number_format($totalKredit, 0) . PHP_EOL;
    echo "  Final Balance: " . number_format($saldoAkhir, 0) . PHP_EOL;
    
    // Show expected vs actual
    $expectedValues = [
        '111' => 93867050,
        '112' => 72398100,
        '1141' => 1920000,
        '1142' => 600000,
        '1143' => 2500000,
        '1152' => 19040000,
        '310' => 175000000,
        '2101' => 0,
        '41' => 4179150
    ];
    
    if (isset($expectedValues[$kodeAkun])) {
        $expected = $expectedValues[$kodeAkun];
        $status = ($saldoAkhir == $expected) ? "CORRECT" : "WRONG";
        echo "  Expected: " . number_format($expected, 0) . " - " . $status . PHP_EOL;
        
        if ($saldoAkhir != $expected) {
            echo "  Difference: " . number_format($saldoAkhir - $expected, 0) . PHP_EOL;
        }
    }
}

echo PHP_EOL . "=== Balance Sheet Equation Check ===" . PHP_EOL;

// Calculate total assets
$assetAccounts = $coas->filter(function($coa) {
    $firstDigit = substr($coa->kode_akun, 0, 1);
    return $firstDigit === '1'; // 1xx = assets
});

$totalAssets = 0;
foreach ($assetAccounts as $coa) {
    // Calculate balance for each asset
    $saldoAwal = 0;
    
    if (in_array($coa->kode_akun, ['1141', '1142', '1143', '1152', '1153', '1154', '1155', '1156'])) {
        if (in_array($coa->kode_akun, ['1141', '1142', '1143'])) {
            $saldoAwal = DB::table('bahan_bakus')
                ->where('coa_persediaan_id', $coa->kode_akun)
                ->where('saldo_awal', '>', 0)
                ->sum(DB::raw('saldo_awal * harga_satuan'));
        } elseif (in_array($coa->kode_akun, ['1152', '1153', '1154', '1155', '1156'])) {
            $saldoAwal = DB::table('bahan_pendukungs')
                ->where('coa_persediaan_id', $coa->kode_akun)
                ->where('saldo_awal', '>', 0)
                ->sum(DB::raw('saldo_awal * harga_satuan'));
        }
    } else {
        $saldoAwal = (float)($coa->saldo_awal ?? 0);
    }
    
    $totalDebit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $coa->kode_akun)
        ->sum('journal_lines.debit');
        
    $totalKredit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $coa->kode_akun)
        ->sum('journal_lines.credit');
    
    $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit; // Assets are debit normal
    $totalAssets += $saldoAkhir;
}

echo "Total Assets: " . number_format($totalAssets, 0) . PHP_EOL;

// Calculate total liabilities + equity
$liabilityEquityAccounts = $coas->filter(function($coa) {
    $firstDigit = substr($coa->kode_akun, 0, 1);
    return in_array($firstDigit, ['2', '3', '4']); // 2xx, 3xx, 4xx = liabilities + equity
});

$totalLiabilityEquity = 0;
foreach ($liabilityEquityAccounts as $coa) {
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    
    $totalDebit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $coa->kode_akun)
        ->sum('journal_lines.debit');
        
    $totalKredit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $coa->kode_akun)
        ->sum('journal_lines.credit');
    
    $saldoAkhir = $saldoAwal + $totalKredit - $totalDebit; // 2xx,3xx,4xx are credit normal
    $totalLiabilityEquity += $saldoAkhir;
}

echo "Total Liabilities + Equity: " . number_format($totalLiabilityEquity, 0) . PHP_EOL;

$balance = $totalAssets - $totalLiabilityEquity;
echo "Balance: " . number_format($balance, 0) . PHP_EOL;
echo "Status: " . ($balance == 0 ? "BALANCED" : "NOT BALANCED") . PHP_EOL;

echo PHP_EOL . "=== Final Verification ===" . PHP_EOL;
echo "Fixes Applied:" . PHP_EOL;
echo "1. Account positions: 2,3,4 = credit normal" . PHP_EOL;
echo "2. Inventory saldo awal from database master" . PHP_EOL;
echo "3. Liability filtering includes 'Kewajiban'" . PHP_EOL;
echo "4. Balance sheet calculation corrected" . PHP_EOL;
echo PHP_EOL . "Result: Neraca Saldo should now be ACCURATE and BALANCED!" . PHP_EOL;
