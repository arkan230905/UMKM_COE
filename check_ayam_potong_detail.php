<?php
/**
 * Cek detail perhitungan ayam potong
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;

echo "=== CEK DETAIL AYAM POTONG (1141) ===\n\n";

// 1. CEK COA AYAM POTONG
$ayamPotongCoa = Coa::where('kode_akun', '1141')->first();
if ($ayamPotongCoa) {
    echo "COA Ayam Potong:\n";
    echo "ID: {$ayamPotongCoa->id}\n";
    echo "Kode: {$ayamPotongCoa->kode_akun}\n";
    echo "Nama: {$ayamPotongCoa->nama_akun}\n";
    echo "Saldo Awal: Rp " . number_format($ayamPotongCoa->saldo_awal, 0, ',', '.') . "\n\n";
} else {
    echo "❌ COA Ayam Potong tidak ditemukan\n";
    exit(1);
}

// 2. CEK SEMUA JOURNAL LINES UNTUK AYAM POTONG
echo "=== SEMUA TRANSAKSI AYAM POTONG ===\n";

$journalLines = DB::table('journal_lines as jl')
    ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
    ->join('coas', 'jl.coa_id', '=', 'coas.id')
    ->where('coas.kode_akun', '1141')
    ->select('je.id as journal_id', 'je.tanggal', 'je.memo', 'jl.debit', 'jl.credit', 'jl.memo as line_memo')
    ->orderBy('je.tanggal')
    ->orderBy('je.id')
    ->get();

$totalDebit = 0;
$totalKredit = 0;
$runningBalance = $ayamPotongCoa->saldo_awal;

echo "Saldo Awal: Rp " . number_format($runningBalance, 0, ',', '.') . "\n\n";

foreach ($journalLines as $line) {
    $totalDebit += $line->debit;
    $totalKredit += $line->credit;
    
    // Hitung running balance (untuk akun aset: saldo = saldo_awal + debit - kredit)
    $runningBalance += $line->debit - $line->credit;
    
    echo "Jurnal ID: {$line->journal_id} ({$line->tanggal})\n";
    echo "Memo: {$line->memo}\n";
    if ($line->debit > 0) {
        echo "DEBIT: Rp " . number_format($line->debit, 0, ',', '.') . "\n";
    }
    if ($line->credit > 0) {
        echo "KREDIT: Rp " . number_format($line->credit, 0, ',', '.') . "\n";
    }
    echo "Running Balance: Rp " . number_format($runningBalance, 0, ',', '.') . "\n\n";
}

echo "=== RINGKASAN PERHITUNGAN ===\n";
echo "Saldo Awal: Rp " . number_format($ayamPotongCoa->saldo_awal, 0, ',', '.') . "\n";
echo "Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Saldo Akhir (Manual): Rp " . number_format($runningBalance, 0, ',', '.') . "\n\n";

// 3. CEK PERHITUNGAN DARI TRIBALANCESERVICE
echo "=== PERHITUNGAN DARI TRIALBALANCESERVICE ===\n";

use App\Services\TrialBalanceService;
$trialBalanceService = new TrialBalanceService();
$result = $trialBalanceService->calculateTrialBalance('2026-04-01', '2026-04-30');

$ayamPotongAccount = collect($result['accounts'])->firstWhere('kode_akun', '1141');
if ($ayamPotongAccount) {
    echo "Dari TrialBalanceService:\n";
    echo "Saldo Awal: Rp " . number_format($ayamPotongAccount['saldo_awal'], 0, ',', '.') . "\n";
    echo "Mutasi Debit: Rp " . number_format($ayamPotongAccount['mutasi_debit'], 0, ',', '.') . "\n";
    echo "Mutasi Kredit: Rp " . number_format($ayamPotongAccount['mutasi_kredit'], 0, ',', '.') . "\n";
    echo "Saldo Akhir: Rp " . number_format($ayamPotongAccount['saldo_akhir'], 0, ',', '.') . "\n";
    echo "Source: {$ayamPotongAccount['source']}\n\n";
} else {
    echo "❌ Ayam Potong tidak ditemukan di TrialBalanceService\n";
}

// 4. CEK APAKAH ADA DUPLIKASI COA
echo "=== CEK DUPLIKASI COA ===\n";

$duplicateCoas = Coa::where('kode_akun', '1141')->get();
echo "Jumlah COA dengan kode 1141: " . $duplicateCoas->count() . "\n";

foreach ($duplicateCoas as $coa) {
    echo "- ID: {$coa->id}, Nama: {$coa->nama_akun}, Saldo Awal: Rp " . 
         number_format($coa->saldo_awal, 0, ',', '.') . "\n";
}

// 5. CEK PERHITUNGAN BUKU BESAR SEPERTI DI AKUNTANSICONTROLLER
echo "\n=== CEK PERHITUNGAN BUKU BESAR (SEPERTI AKUNTANSICONTROLLER) ===\n";

// Ambil saldo awal
$saldoAwal = (float)($ayamPotongCoa->saldo_awal ?? 0);

// Ambil mutasi dari journal_lines
$mutasi = DB::table('journal_entries as je')
    ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
    ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
    ->where('coas.kode_akun', '1141')
    ->where('je.tanggal', '<=', '2026-04-30')
    ->selectRaw('
        COALESCE(SUM(jl.debit), 0) as total_debit,
        COALESCE(SUM(jl.credit), 0) as total_kredit
    ')
    ->first();

$saldoAkhirBukuBesar = $saldoAwal + $mutasi->total_debit - $mutasi->total_kredit;

echo "Perhitungan Buku Besar:\n";
echo "Saldo Awal: Rp " . number_format($saldoAwal, 0, ',', '.') . "\n";
echo "Total Debit: Rp " . number_format($mutasi->total_debit, 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($mutasi->total_kredit, 0, ',', '.') . "\n";
echo "Saldo Akhir: Rp " . number_format($saldoAkhirBukuBesar, 0, ',', '.') . "\n\n";

echo "=== PERBANDINGAN HASIL ===\n";
echo "Manual Calculation: Rp " . number_format($runningBalance, 0, ',', '.') . "\n";
echo "TrialBalanceService: Rp " . number_format($ayamPotongAccount['saldo_akhir'] ?? 0, 0, ',', '.') . "\n";
echo "Buku Besar Logic: Rp " . number_format($saldoAkhirBukuBesar, 0, ',', '.') . "\n";
echo "Yang ditampilkan di UI: Rp 1.230.769\n\n";

if (abs($runningBalance - 1230769) < 1) {
    echo "✅ Manual calculation sesuai dengan UI\n";
} else {
    echo "❌ Ada perbedaan antara manual calculation dengan UI\n";
}