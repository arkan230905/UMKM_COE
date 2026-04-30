<?php
/**
 * Test saldo running balance seperti di Buku Besar
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Coa;
use App\Models\JournalLine;
use App\Models\JournalEntry;
use App\Services\TrialBalanceService;

echo "=== TEST SALDO RUNNING BALANCE ===\n";
echo "Simulasi perhitungan saldo seperti di Buku Besar\n\n";

$kodeAkun = '1141';
$endDate = '2026-04-30';

// 1. Manual calculation seperti Buku Besar
$coa = Coa::where('kode_akun', $kodeAkun)->first();
echo "Akun: {$coa->kode_akun} - {$coa->nama_akun}\n";
echo "Saldo Awal: Rp " . number_format($coa->saldo_awal ?? 0, 0, ',', '.') . "\n\n";

// Ambil transaksi seperti di Buku Besar
$transactions = JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->where('journal_lines.coa_id', $coa->id)
    ->where('journal_entries.tanggal', '<=', $endDate)
    ->select('journal_entries.tanggal', 'journal_entries.id as entry_id', 'journal_lines.debit', 'journal_lines.credit')
    ->orderBy('journal_entries.tanggal')
    ->orderBy('journal_entries.id')
    ->get();

echo "=== SIMULASI BUKU BESAR ===\n";
echo sprintf("%-12s %-10s %15s %15s %15s\n", "TANGGAL", "ENTRY ID", "DEBIT", "KREDIT", "RUNNING BALANCE");
echo str_repeat("-", 70) . "\n";

$runningBalance = $coa->saldo_awal ?? 0;

foreach ($transactions as $trans) {
    $runningBalance += $trans->debit - $trans->credit;
    
    echo sprintf("%-12s %-10s %15s %15s %15s\n",
        $trans->tanggal,
        $trans->entry_id,
        $trans->debit > 0 ? number_format($trans->debit, 0, ',', '.') : '-',
        $trans->credit > 0 ? number_format($trans->credit, 0, ',', '.') : '-',
        number_format($runningBalance, 0, ',', '.')
    );
}

echo str_repeat("-", 70) . "\n";
echo "SALDO AKHIR RUNNING BALANCE: Rp " . number_format($runningBalance, 0, ',', '.') . "\n\n";

// 2. Test dengan TrialBalanceService yang sudah diperbaiki
echo "=== TEST DENGAN TRIAL BALANCE SERVICE ===\n";
$service = new TrialBalanceService();
$result = $service->calculateTrialBalance('2026-04-01', '2026-04-30');

foreach ($result['accounts'] as $account) {
    if ($account['kode_akun'] == $kodeAkun) {
        echo "Saldo dari TrialBalanceService: Rp " . number_format($account['saldo_akhir'], 0, ',', '.') . "\n";
        echo "Source: " . ($account['source'] ?? 'periode') . "\n";
        
        if ($account['debit'] > 0) {
            echo "Tampil di Neraca Saldo: DEBIT Rp " . number_format($account['debit'], 0, ',', '.') . "\n";
        } else {
            echo "Tampil di Neraca Saldo: KREDIT Rp " . number_format($account['kredit'], 0, ',', '.') . "\n";
        }
        break;
    }
}

echo "\n=== PERBANDINGAN ===\n";
echo "Running Balance Manual: Rp " . number_format($runningBalance, 0, ',', '.') . "\n";
echo "Target dari screenshot: -Rp 277.435\n";

if (abs($runningBalance + 277435) < 0.01) {
    echo "✅ PERFECT! Sekarang sesuai dengan Buku Besar!\n";
} else {
    echo "❌ Masih belum sesuai. Perlu cek lagi.\n";
    echo "Selisih: Rp " . number_format($runningBalance + 277435, 0, ',', '.') . "\n";
}

echo "\n=== LOGIKA TAMPILAN NERACA SALDO ===\n";
if ($runningBalance >= 0) {
    echo "Saldo positif → Tampil di kolom DEBIT\n";
} else {
    echo "Saldo negatif → Tampil di kolom KREDIT\n";
    echo "Nilai absolut: Rp " . number_format(abs($runningBalance), 0, ',', '.') . "\n";
}