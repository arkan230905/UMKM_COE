<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== NERACA SALDO VERIFICATION (WITH SALDO AWAL) ===\n";
echo "Period: Mei 2026\n";
echo "User ID: 1\n\n";

$bulan = 5;
$tahun = 2026;

$from = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to   = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

// Get all COAs
$coas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal')
    ->where('user_id', 1)
    ->orderBy('kode_akun')
    ->get();

// Get mutations for the period
$mutasiByKodeAkun = [];
$mutasi = DB::table('jurnal_umum as ju')
    ->join('coas', 'coas.id', '=', 'ju.coa_id')
    ->where('ju.user_id', 1)
    ->whereBetween('ju.tanggal', [$from, $to])
    ->select(
        'coas.kode_akun',
        DB::raw('COALESCE(SUM(ju.debit),0) as total_debit'),
        DB::raw('COALESCE(SUM(ju.kredit),0) as total_kredit')
    )
    ->groupBy('coas.kode_akun')
    ->get();

foreach ($mutasi as $m) {
    $mutasiByKodeAkun[$m->kode_akun] = [
        'total_debit' => $m->total_debit,
        'total_kredit' => $m->total_kredit
    ];
}

$totalSaldoDebit = 0;
$totalSaldoKredit = 0;

echo str_pad("Kode", 8) . str_pad("Nama Akun", 30) . str_pad("Saldo Awal", 15) . str_pad("Debit", 15) . str_pad("Kredit", 15) . str_pad("Saldo Akhir", 15) . "\n";
echo str_repeat("-", 98) . "\n";

foreach ($coas as $coa) {
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    $totalDebit  = $mutasiByKodeAkun[$coa->kode_akun]['total_debit']  ?? 0;
    $totalKredit = $mutasiByKodeAkun[$coa->kode_akun]['total_kredit'] ?? 0;

    // Determine if debit normal based on first digit
    $firstDigit = substr($coa->kode_akun, 0, 1);
    $isDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
    
    // Override with saldo_normal if available
    $saldoNormal = strtolower($coa->saldo_normal ?? '');
    if (!empty($saldoNormal)) {
        if (in_array($firstDigit, ['2', '3', '4'])) {
            $isDebitNormal = false;
        } else {
            $isDebitNormal = ($saldoNormal === 'debit');
        }
    }

    // Calculate saldo akhir
    if ($isDebitNormal) {
        $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
    } else {
        $saldoAkhir = $saldoAwal - $totalDebit + $totalKredit;
    }

    // Skip accounts with no activity
    if ($saldoAwal == 0 && $totalDebit == 0 && $totalKredit == 0 && $saldoAkhir == 0) {
        continue;
    }

    // Determine position in trial balance
    $tipeAkunUpper = strtoupper(trim($coa->tipe_akun));
    $isDebitPosition = in_array($tipeAkunUpper, [
        'ASET', 'ASSET', 'AKTIVA',
        'BEBAN', 'EXPENSE', 'BIAYA', 'COST'
    ]);

    $saldoDebit = 0;
    $saldoKredit = 0;

    if ($saldoAkhir != 0) {
        if ($saldoAkhir > 0) {
            if ($isDebitPosition) {
                $saldoDebit = $saldoAkhir;
            } else {
                $saldoKredit = $saldoAkhir;
            }
        } else {
            // Negative balance - abnormal
            $nilai = abs($saldoAkhir);
            if ($isDebitPosition) {
                $saldoKredit = $nilai;
            } else {
                $saldoDebit = $nilai;
            }
        }
    }

    $totalSaldoDebit += $saldoDebit;
    $totalSaldoKredit += $saldoKredit;

    echo str_pad($coa->kode_akun, 8) . 
         str_pad(substr($coa->nama_akun, 0, 29), 30) . 
         str_pad(number_format($saldoAwal, 0), 15) . 
         str_pad(number_format($totalDebit, 0), 15) . 
         str_pad(number_format($totalKredit, 0), 15) . 
         str_pad(number_format($saldoAkhir, 0), 15) . "\n";
}

echo str_repeat("-", 98) . "\n";
echo "\n=== BALANCE CHECK ===\n";
echo "Total Saldo Debit:  Rp " . number_format($totalSaldoDebit, 0) . "\n";
echo "Total Saldo Kredit: Rp " . number_format($totalSaldoKredit, 0) . "\n";
$difference = $totalSaldoDebit - $totalSaldoKredit;
echo "Difference:         Rp " . number_format($difference, 0) . "\n\n";

if (abs($difference) < 0.01) {
    echo "✅ STATUS: BALANCED!\n";
} else {
    echo "❌ STATUS: NOT BALANCED (Selisih: Rp " . number_format($difference, 0) . ")\n";
}

echo "\n=== KEY ACCOUNTS ===\n";
$keyAccounts = ['116', '1161'];
foreach ($keyAccounts as $kodeAkun) {
    $coa = DB::table('coas')->where('kode_akun', $kodeAkun)->where('user_id', 1)->first();
    if ($coa) {
        $saldoAwal = (float)($coa->saldo_awal ?? 0);
        $debit = $mutasiByKodeAkun[$kodeAkun]['total_debit'] ?? 0;
        $kredit = $mutasiByKodeAkun[$kodeAkun]['total_kredit'] ?? 0;
        
        // Calculate saldo akhir (debit normal for persediaan)
        $saldoAkhir = $saldoAwal + $debit - $kredit;
        
        echo "\n{$coa->kode_akun} - {$coa->nama_akun}:\n";
        echo "  Saldo Awal: Rp " . number_format($saldoAwal, 0) . "\n";
        echo "  Debit:      Rp " . number_format($debit, 0) . "\n";
        echo "  Kredit:     Rp " . number_format($kredit, 0) . "\n";
        echo "  Saldo Akhir: Rp " . number_format($saldoAkhir, 0) . "\n";
        
        if ($kodeAkun == '116' && abs($saldoAkhir) < 0.01) {
            echo "  ✅ Parent account has zero balance (correct!)\n";
        } elseif ($kodeAkun == '1161' && $saldoAkhir > 0) {
            echo "  ✅ Jasuke account has positive balance (correct!)\n";
        }
    }
}
