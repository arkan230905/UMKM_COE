<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "INVESTIGASI ROOT CAUSE NERACA SALDO IMBALANCE\n";
echo "=============================================\n";

echo "\n=== CURRENT SITUATION ===\n";
echo "Neraca Saldo Report:\n";
echo "- Total Debit: Rp 178.472.760\n";
echo "- Total Credit: Rp 177.372.760\n";
echo "- Selisih: Rp 1.100.000 (Debit > Credit)\n";
echo "- Status: TIDAK SEIMBANG\n";

echo "\nJurnal Umum Table:\n";
echo "- Total Debit: Rp 6.535.580\n";
echo "- Total Credit: Rp 6.535.580\n";
echo "- Status: BALANCED\n";

echo "\n=== INVESTIGASI LOGIKA CONTROLLER ===\n";

// Simulate controller logic exactly
$from = '2026-04-01';
$to = '2026-04-30';

// Get all COA
$coas = \App\Models\Coa::select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_awal')
    ->groupBy('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_awal')
    ->orderBy('kode_akun')
    ->get();

// Get account summary using controller's getAccountSummary method
$mutasiJournalLines = \Illuminate\Support\Facades\DB::table('journal_lines as jl')
    ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
    ->join('coas', 'coas.id', '=', 'jl.coa_id')
    ->whereBetween('je.tanggal', [$from, $to])
    ->where('coas.user_id', 1)
    ->select(
        'coas.kode_akun',
        \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(jl.debit),0) as total_debit'),
        \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(jl.credit),0) as total_kredit')
    )
    ->groupBy('coas.kode_akun')
    ->get();

$mutasiJurnalUmum = \Illuminate\Support\Facades\DB::table('jurnal_umum as ju')
    ->join('coas', 'coas.id', '=', 'ju.coa_id')
    ->whereBetween('ju.tanggal', [$from, $to])
    ->whereNotIn('ju.tipe_referensi', [
        'purchase', 'sale', 'retur_pembelian', 'retur_penjualan',
        'production_material', 'production_labor_overhead', 'production_finished',
        'produksi', 'expense_payment'
    ])
    ->where('coas.user_id', 1)
    ->select(
        'coas.kode_akun',
        \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(ju.debit),0) as total_debit'),
        \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(ju.kredit),0) as total_kredit')
    )
    ->groupBy('coas.kode_akun')
    ->get();

// Combine mutasi
$mutasiByKodeAkun = [];
foreach ($mutasiJournalLines as $line) {
    $mutasiByKodeAkun[$line->kode_akun] = [
        'total_debit' => $line->total_debit,
        'total_kredit' => $line->total_kredit
    ];
}
foreach ($mutasiJurnalUmum as $line) {
    if (isset($mutasiByKodeAkun[$line->kode_akun])) {
        $mutasiByKodeAkun[$line->kode_akun]['total_debit'] += $line->total_debit;
        $mutasiByKodeAkun[$line->kode_akun]['total_kredit'] += $line->total_kredit;
    } else {
        $mutasiByKodeAkun[$line->kode_akun] = [
            'total_debit' => $line->total_debit,
            'total_kredit' => $line->total_kredit
        ];
    }
}

echo "\n=== DETAILED CALCULATION PER COA ===\n";
echo "Kode\tNama Akun\t\t\tSaldo Awal\tDebit\tCredit\tSaldo Akhir\tPosisi\n";
echo "================================================================================\n";

$totalDebit = 0;
$totalKredit = 0;
$calculatedTotals = [];

foreach ($coas as $coa) {
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    $totalDebitMutasi = $mutasiByKodeAkun[$coa->kode_akun]['total_debit'] ?? 0;
    $totalKreditMutasi = $mutasiByKodeAkun[$coa->kode_akun]['total_kredit'] ?? 0;
    
    // Determine if debit normal based on first digit (controller logic)
    $firstDigit = substr($coa->kode_akun, 0, 1);
    $isDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
    
    // Calculate saldo akhir
    if ($isDebitNormal) {
        $saldoAkhir = $saldoAwal + $totalDebitMutasi - $totalKreditMutasi;
    } else {
        $saldoAkhir = $saldoAwal - $totalDebitMutasi + $totalKreditMutasi;
    }
    
    // Use posisiNeracaSaldo logic
    $isDebitNormalType = in_array($coa->tipe_akun, ['ASET', 'BEBAN']);
    
    $debit = 0;
    $kredit = 0;
    
    if ($saldoAkhir != 0) {
        if ($saldoAkhir > 0) {
            if ($isDebitNormalType) {
                $debit = $saldoAkhir;
            } else {
                $kredit = $saldoAkhir;
            }
        } elseif ($saldoAkhir < 0) {
            $nilai = abs($saldoAkhir);
            if ($isDebitNormalType) {
                $kredit = $nilai;
            } else {
                $debit = $nilai;
            }
        }
    }
    
    $totalDebit += $debit;
    $totalKredit += $kredit;
    
    $calculatedTotals[$coa->kode_akun] = [
        'saldo_awal' => $saldoAwal,
        'debit_mutasi' => $totalDebitMutasi,
        'kredit_mutasi' => $totalKreditMutasi,
        'saldo_akhir' => $saldoAkhir,
        'posisi_debit' => $debit,
        'posisi_kredit' => $kredit
    ];
    
    if ($debit != 0 || $kredit != 0) {
        printf("%-8s\t%-30s\t%10s\t%10s\t%10s\t%10s\t%s\n", 
            $coa->kode_akun, 
            substr($coa->nama_akun, 0, 30), 
            number_format($saldoAwal, 0, ',', '.'), 
            number_format($totalDebitMutasi, 0, ',', '.'), 
            number_format($totalKreditMutasi, 0, ',', '.'), 
            number_format($saldoAkhir, 0, ',', '.'),
            ($debit > 0 ? "Debit" : "Credit")
        );
    }
}

echo "\n================================================================================\n";
echo "Calculated Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Calculated Total Credit: Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Calculated Selisih: Rp " . number_format(abs($totalDebit - $totalKredit), 0, ',', '.') . "\n";
echo "Calculated Status: " . ($totalDebit == $totalKredit ? "SEIMBANG" : "TIDAK SEIMBANG") . "\n";

echo "\n=== COMPARISON WITH USER REPORT ===\n";
echo "User Report:\n";
echo "- Total Debit: Rp 178.472.760\n";
echo "- Total Credit: Rp 177.372.760\n";
echo "- Selisih: Rp 1.100.000\n";

echo "\nController Calculation:\n";
echo "- Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "- Total Credit: Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "- Selisih: Rp " . number_format(abs($totalDebit - $totalKredit), 0, ',', '.') . "\n";

$debitDiff = 178472760 - $totalDebit;
$creditDiff = 177372760 - $totalKredit;

echo "\nDifferences:\n";
echo "- Debit Difference: Rp " . number_format($debitDiff, 0, ',', '.') . "\n";
echo "- Credit Difference: Rp " . number_format($creditDiff, 0, ',', '.') . "\n";

echo "\n=== ROOT CAUSE ANALYSIS ===\n";

if ($totalDebit != 178472760 || $totalKredit != 177372760) {
    echo "FOUND MISMATCH: Controller calculation doesn't match user report\n";
    echo "This suggests the neraca saldo display uses different logic\n";
    
    echo "\nPossible causes:\n";
    echo "1. User report data comes from different calculation method\n";
    echo "2. Some COA data excluded/included differently\n";
    echo "3. Saldo awal calculation differs\n";
    echo "4. Different filtering logic applied\n";
    
    echo "\n=== INVESTIGATING SPECIFIC DIFFERENCES ===\n";
    
    // Check which COAs contribute to the difference
    echo "COAs that might cause the difference:\n";
    
    $userReportData = [
        '111' => ['debit' => 98500000, 'credit' => 0],
        '112' => ['debit' => 73742300, 'credit' => 0],
        '1141' => ['debit' => 800000, 'credit' => 0],
        '1151' => ['debit' => 186120, 'credit' => 0],
        '1152' => ['debit' => 430000, 'credit' => 0],
        '1153' => ['debit' => 172000, 'credit' => 0],
        '1161' => ['debit' => 376040, 'credit' => 0],
        '127' => ['debit' => 106700, 'credit' => 0],
        '210' => ['debit' => 0, 'credit' => 44760],
        '211' => ['debit' => 0, 'credit' => 54000],
        '212' => ['debit' => 0, 'credit' => 110000],
        '310' => ['debit' => 0, 'credit' => 176164000],
        '41' => ['debit' => 0, 'credit' => 1000000],
        '513' => ['debit' => 2000000, 'credit' => 0],
        '514' => ['debit' => 200000, 'credit' => 0],
        '52' => ['debit' => 191000, 'credit' => 0],
        '54' => ['debit' => 1500000, 'credit' => 0],
        '56' => ['debit' => 268600, 'credit' => 0],
    ];
    
    echo "\nComparison per COA:\n";
    echo "Kode\tUser Report\t\tController\t\tDifference\n";
    echo "========================================================\n";
    
    foreach ($userReportData as $kode => $userData) {
        $calcData = $calculatedTotals[$kode] ?? null;
        if ($calcData) {
            $userTotal = $userData['debit'] + $userData['credit'];
            $calcTotal = $calcData['posisi_debit'] + $calcData['posisi_kredit'];
            $diff = $userTotal - $calcTotal;
            
            if ($diff != 0) {
                printf("%-8s\t%10s\t\t%10s\t\t%10s\n", 
                    $kode, 
                    number_format($userTotal, 0, ',', '.'), 
                    number_format($calcTotal, 0, ',', '.'), 
                    number_format($diff, 0, ',', '.')
                );
            }
        }
    }
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Investigate why controller calculation differs from user report\n";
echo "2. Check if there are additional data sources or filters\n";
echo "3. Look for missing COA entries or incorrect calculations\n";
echo "4. Consider if user report includes data not in current calculation\n";

echo "\nRoot cause investigation completed!\n";
