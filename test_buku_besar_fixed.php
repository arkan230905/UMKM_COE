<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Fixed Buku Besar Calculation...\n\n";

// Simulate user login
\Illuminate\Support\Facades\Auth::loginUsingId(1);

// Test with a specific account
$accountCode = '111'; // Kas Bank
$month = '04';
$year = '2026';

echo "Testing Account: {$accountCode} (Kas Bank)\n";
echo "Period: {$month}/{$year}\n\n";

// Get COA data
$coa = \App\Models\Coa::where('kode_akun', $accountCode)->first();
if (!$coa) {
    echo "COA not found for account code: {$accountCode}\n";
    exit;
}

echo "COA Info:\n";
echo "Kode: {$coa->kode_akun}\n";
echo "Nama: {$coa->nama_akun}\n";
echo "Tipe: {$coa->tipe_akun}\n";
echo "Saldo Awal: " . number_format($coa->saldo_awal ?? 0, 0, ',', '.') . "\n\n";

// Get saldo awal (same logic as controller)
$bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
$bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];

if (in_array($accountCode, $bahanBakuCoas) || in_array($accountCode, $bahanPendukungCoas)) {
    // For inventory accounts, use inventory saldo
    $saldoAwal = 0; // Simplified for test
} else {
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
}

echo "Calculated Saldo Awal: " . number_format($saldoAwal, 0, ',', '.') . "\n\n";

// Query jurnal umum data (fixed logic)
$query = \Illuminate\Support\Facades\DB::table('jurnal_umum as ju')
    ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
    ->select([
        'ju.*',
        'coas.kode_akun',
        'coas.nama_akun',
        'coas.tipe_akun'
    ])
    ->where(function($q) {
        $q->where('ju.debit', '>', 0)
          ->orWhere('ju.kredit', '>', 0);
    })
    ->where('coas.kode_akun', $accountCode)
    ->whereMonth('ju.tanggal', $month)
    ->whereYear('ju.tanggal', $year)
    ->orderBy('ju.tanggal','asc')
    ->orderBy('ju.id','asc');

$journalLines = $query->get();

echo "Journal Entries Found: {$journalLines->count()}\n\n";

// Display journal entries
echo "Journal Entries:\n";
echo "================\n";

$totalDebit = 0;
$totalKredit = 0;

foreach ($journalLines as $journal) {
    echo "ID: {$journal->id}\n";
    echo "Tanggal: {$journal->tanggal}\n";
    echo "Keterangan: {$journal->keterangan}\n";
    echo "Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
    echo "Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
    echo "Referensi: {$journal->referensi}\n";
    echo "\n";
    
    $totalDebit += $journal->debit;
    $totalKredit += $journal->kredit;
}

// Calculate final saldo
$saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;

echo "Calculation Summary:\n";
echo "====================\n";
echo "Saldo Awal: " . number_format($saldoAwal, 0, ',', '.') . "\n";
echo "Total Debit: " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Kredit: " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Saldo Akhir: " . number_format($saldoAkhir, 0, ',', '.') . "\n\n";

echo "Formula: Saldo Awal + Debit - Kredit\n";
echo "= " . number_format($saldoAwal, 0, ',', '.') . " + " . number_format($totalDebit, 0, ',', '.') . " - " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "= " . number_format($saldoAkhir, 0, ',', '.') . "\n\n";

// Test with another account
echo "Testing Another Account: 112 (Kas)\n";
echo "====================================\n";

$accountCode2 = '112';
$coa2 = \App\Models\Coa::where('kode_akun', $accountCode2)->first();
$saldoAwal2 = (float)($coa2->saldo_awal ?? 0);

$query2 = \Illuminate\Support\Facades\DB::table('jurnal_umum as ju')
    ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
    ->where('coas.kode_akun', $accountCode2)
    ->whereMonth('ju.tanggal', $month)
    ->whereYear('ju.tanggal', $year);

$journalLines2 = $query2->get();
$totalDebit2 = $journalLines2->sum('debit');
$totalKredit2 = $journalLines2->sum('kredit');
$saldoAkhir2 = $saldoAwal2 + $totalDebit2 - $totalKredit2;

echo "Saldo Awal: " . number_format($saldoAwal2, 0, ',', '.') . "\n";
echo "Total Debit: " . number_format($totalDebit2, 0, ',', '.') . "\n";
echo "Total Kredit: " . number_format($totalKredit2, 0, ',', '.') . "\n";
echo "Saldo Akhir: " . number_format($saldoAkhir2, 0, ',', '.') . "\n\n";

echo "Buku besar calculation test completed!\n";
