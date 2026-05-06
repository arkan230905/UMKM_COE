<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ANALISIS NERACA SALDO TIDAK BALANCE ===" . PHP_EOL . PHP_EOL;

// Cek semua akun dengan saldo akhir
$startDate = '2026-05-01';
$endDate = '2026-05-31';
$userId = 1;

$coas = DB::table('coas')
    ->where('user_id', $userId)
    ->orderBy('kode_akun')
    ->get();

$totalSaldoDebit = 0;
$totalSaldoKredit = 0;

echo "Perhitungan Saldo Debit dan Kredit:" . PHP_EOL;
echo str_repeat('=', 100) . PHP_EOL;
printf("%-10s %-40s %-15s %-15s %-15s %-15s%s", "Kode", "Nama", "Tipe", "Saldo Akhir", "Posisi Debit", "Posisi Kredit", PHP_EOL);
echo str_repeat('-', 100) . PHP_EOL;

foreach ($coas as $coa) {
    // Get mutasi
    $mutasi = DB::table('jurnal_umum')
        ->where('coa_id', $coa->id)
        ->where('user_id', $userId)
        ->whereBetween('tanggal', [$startDate, $endDate])
        ->selectRaw('SUM(debit) as total_debit, SUM(kredit) as total_kredit')
        ->first();
    
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    $totalDebit = (float)($mutasi->total_debit ?? 0);
    $totalKredit = (float)($mutasi->total_kredit ?? 0);
    
    // Hitung saldo akhir
    $tipeAkunUpper = strtoupper(trim($coa->tipe_akun));
    $isDebitNormal = in_array($tipeAkunUpper, [
        'ASET', 'ASSET', 'AKTIVA',
        'BEBAN', 'EXPENSE', 'BIAYA', 'COST'
    ]);
    
    if ($isDebitNormal) {
        $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
    } else {
        $saldoAkhir = $saldoAwal - $totalDebit + $totalKredit;
    }
    
    // Tentukan posisi
    $posisiDebit = 0;
    $posisiKredit = 0;
    
    if ($saldoAkhir != 0) {
        if ($saldoAkhir > 0) {
            if ($isDebitNormal) {
                $posisiDebit = $saldoAkhir;
            } else {
                $posisiKredit = $saldoAkhir;
            }
        } else {
            // Saldo negatif (abnormal)
            $nilai = abs($saldoAkhir);
            if ($isDebitNormal) {
                $posisiKredit = $nilai;
            } else {
                $posisiDebit = $nilai;
            }
        }
    }
    
    // Skip akun tanpa aktivitas
    if ($saldoAwal == 0 && $totalDebit == 0 && $totalKredit == 0) {
        continue;
    }
    
    $totalSaldoDebit += $posisiDebit;
    $totalSaldoKredit += $posisiKredit;
    
    printf(
        "%-10s %-40s %-15s %15s %15s %15s%s",
        $coa->kode_akun,
        substr($coa->nama_akun, 0, 40),
        $coa->tipe_akun,
        number_format($saldoAkhir, 0, ',', '.'),
        number_format($posisiDebit, 0, ',', '.'),
        number_format($posisiKredit, 0, ',', '.'),
        PHP_EOL
    );
}

echo str_repeat('=', 100) . PHP_EOL;
printf(
    "%-66s %15s %15s%s",
    "TOTAL",
    number_format($totalSaldoDebit, 0, ',', '.'),
    number_format($totalSaldoKredit, 0, ',', '.'),
    PHP_EOL
);
echo str_repeat('=', 100) . PHP_EOL;

$selisih = $totalSaldoDebit - $totalSaldoKredit;
echo PHP_EOL;
echo "BALANCE CHECK:" . PHP_EOL;
echo "Total Saldo Debit:  Rp " . number_format($totalSaldoDebit, 2, ',', '.') . PHP_EOL;
echo "Total Saldo Kredit: Rp " . number_format($totalSaldoKredit, 2, ',', '.') . PHP_EOL;
echo "Selisih:            Rp " . number_format($selisih, 2, ',', '.') . PHP_EOL;
echo PHP_EOL;

if (abs($selisih) < 0.01) {
    echo "✅ BALANCED!" . PHP_EOL;
} else {
    echo "❌ TIDAK BALANCE!" . PHP_EOL;
    echo PHP_EOL;
    echo "Kemungkinan penyebab:" . PHP_EOL;
    echo "1. Ada akun dengan saldo negatif yang tidak seharusnya" . PHP_EOL;
    echo "2. Ada kesalahan dalam pencatatan jurnal" . PHP_EOL;
    echo "3. Ada duplikasi atau jurnal yang hilang" . PHP_EOL;
}
