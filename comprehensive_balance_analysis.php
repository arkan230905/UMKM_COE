<?php
/**
 * Analisis menyeluruh untuk memperbaiki Neraca Saldo
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;
use App\Models\JournalEntry;
use App\Services\TrialBalanceService;
use Illuminate\Support\Facades\DB;

echo "=== COMPREHENSIVE BALANCE ANALYSIS ===\n\n";

// 1. CEK SEMUA JURNAL YANG TIDAK SEIMBANG
echo "=== 1. CEK JURNAL YANG TIDAK SEIMBANG ===\n";

$allJournalEntries = JournalEntry::with('lines.coa')->get();
$unbalancedJournals = [];
$totalAllDebit = 0;
$totalAllKredit = 0;

foreach ($allJournalEntries as $entry) {
    $entryDebit = $entry->lines->sum('debit');
    $entryKredit = $entry->lines->sum('credit');
    
    $totalAllDebit += $entryDebit;
    $totalAllKredit += $entryKredit;
    
    if (abs($entryDebit - $entryKredit) > 0.01) {
        $unbalancedJournals[] = [
            'id' => $entry->id,
            'tanggal' => $entry->tanggal,
            'memo' => $entry->memo,
            'debit' => $entryDebit,
            'kredit' => $entryKredit,
            'selisih' => $entryDebit - $entryKredit
        ];
    }
}

echo "Total Debit Semua Jurnal: Rp " . number_format($totalAllDebit, 0, ',', '.') . "\n";
echo "Total Kredit Semua Jurnal: Rp " . number_format($totalAllKredit, 0, ',', '.') . "\n";
echo "Selisih Jurnal: Rp " . number_format($totalAllDebit - $totalAllKredit, 0, ',', '.') . "\n";

if (count($unbalancedJournals) > 0) {
    echo "\n❌ DITEMUKAN " . count($unbalancedJournals) . " JURNAL TIDAK SEIMBANG:\n";
    foreach ($unbalancedJournals as $journal) {
        echo "- ID {$journal['id']} ({$journal['tanggal']}): {$journal['memo']}\n";
        echo "  Debit: Rp " . number_format($journal['debit'], 0, ',', '.') . 
             " | Kredit: Rp " . number_format($journal['kredit'], 0, ',', '.') . 
             " | Selisih: Rp " . number_format($journal['selisih'], 0, ',', '.') . "\n";
    }
} else {
    echo "✅ Semua jurnal sudah seimbang\n";
}

// 2. ANALISIS SALDO AKHIR PER KATEGORI AKUN
echo "\n=== 2. ANALISIS SALDO AKHIR PER KATEGORI ===\n";

$trialBalanceService = new TrialBalanceService();
$result = $trialBalanceService->calculateTrialBalance('2026-04-01', '2026-04-30');

$categories = [
    'ASET' => ['total' => 0, 'accounts' => []],
    'KEWAJIBAN' => ['total' => 0, 'accounts' => []],
    'MODAL' => ['total' => 0, 'accounts' => []],
    'PENDAPATAN' => ['total' => 0, 'accounts' => []],
    'BEBAN' => ['total' => 0, 'accounts' => []]
];

foreach ($result['accounts'] as $account) {
    $kodeAkun = $account['kode_akun'];
    $saldoAkhir = $account['saldo_akhir'];
    $firstDigit = substr($kodeAkun, 0, 1);
    
    // Kategorisasi berdasarkan digit pertama
    if ($firstDigit == '1') {
        $categories['ASET']['total'] += $saldoAkhir;
        $categories['ASET']['accounts'][] = $account;
    } elseif ($firstDigit == '2') {
        $categories['KEWAJIBAN']['total'] += $saldoAkhir;
        $categories['KEWAJIBAN']['accounts'][] = $account;
    } elseif ($firstDigit == '3') {
        $categories['MODAL']['total'] += $saldoAkhir;
        $categories['MODAL']['accounts'][] = $account;
    } elseif ($firstDigit == '4') {
        $categories['PENDAPATAN']['total'] += $saldoAkhir;
        $categories['PENDAPATAN']['accounts'][] = $account;
    } elseif (in_array($firstDigit, ['5', '6'])) {
        $categories['BEBAN']['total'] += $saldoAkhir;
        $categories['BEBAN']['accounts'][] = $account;
    }
}

foreach ($categories as $category => $data) {
    echo "{$category}: Rp " . number_format($data['total'], 0, ',', '.') . 
         " (" . count($data['accounts']) . " akun)\n";
}

// 3. CEK PERSAMAAN AKUNTANSI
echo "\n=== 3. CEK PERSAMAAN AKUNTANSI ===\n";
$aset = $categories['ASET']['total'];
$kewajiban = $categories['KEWAJIBAN']['total'];
$modal = $categories['MODAL']['total'];
$pendapatan = $categories['PENDAPATAN']['total'];
$beban = $categories['BEBAN']['total'];

echo "ASET = KEWAJIBAN + MODAL + (PENDAPATAN - BEBAN)\n";
echo "Rp " . number_format($aset, 0, ',', '.') . " = Rp " . number_format($kewajiban, 0, ',', '.') . 
     " + Rp " . number_format($modal, 0, ',', '.') . " + (Rp " . number_format($pendapatan, 0, ',', '.') . 
     " - Rp " . number_format($beban, 0, ',', '.') . ")\n";

$rightSide = $kewajiban + $modal + ($pendapatan - $beban);
echo "Rp " . number_format($aset, 0, ',', '.') . " = Rp " . number_format($rightSide, 0, ',', '.') . "\n";

$balanceCheck = $aset - $rightSide;
if (abs($balanceCheck) < 0.01) {
    echo "✅ Persamaan akuntansi SEIMBANG\n";
} else {
    echo "❌ Persamaan akuntansi TIDAK SEIMBANG. Selisih: Rp " . number_format($balanceCheck, 0, ',', '.') . "\n";
}

// 4. ANALISIS NERACA SALDO
echo "\n=== 4. ANALISIS NERACA SALDO ===\n";
echo "Total Debit Neraca Saldo: Rp " . number_format($result['total_debit'], 0, ',', '.') . "\n";
echo "Total Kredit Neraca Saldo: Rp " . number_format($result['total_kredit'], 0, ',', '.') . "\n";
echo "Selisih Neraca Saldo: Rp " . number_format($result['difference'], 0, ',', '.') . "\n";
echo "Status: " . ($result['is_balanced'] ? '✅ SEIMBANG' : '❌ TIDAK SEIMBANG') . "\n";

// 5. IDENTIFIKASI MASALAH
echo "\n=== 5. IDENTIFIKASI MASALAH ===\n";

if (abs($totalAllDebit - $totalAllKredit) > 0.01) {
    echo "❌ MASALAH UTAMA: Jurnal tidak seimbang\n";
    echo "Total debit jurnal ≠ Total kredit jurnal\n";
    echo "Selisih: Rp " . number_format($totalAllDebit - $totalAllKredit, 0, ',', '.') . "\n";
} else {
    echo "✅ Semua jurnal seimbang\n";
}

if (!$result['is_balanced']) {
    echo "❌ MASALAH: Neraca saldo tidak seimbang\n";
    echo "Kemungkinan penyebab:\n";
    echo "1. Logika mapping saldo ke debit/kredit salah\n";
    echo "2. Ada akun yang tidak terhitung\n";
    echo "3. Saldo awal tidak seimbang\n";
}

// 6. CEK SALDO AWAL COA
echo "\n=== 6. CEK SALDO AWAL COA ===\n";
$totalSaldoAwal = Coa::sum('saldo_awal');
echo "Total Saldo Awal COA: Rp " . number_format($totalSaldoAwal, 0, ',', '.') . "\n";

if (abs($totalSaldoAwal) > 0.01) {
    echo "❌ Saldo awal COA tidak seimbang\n";
    echo "Ini bisa menyebabkan ketidakseimbangan neraca saldo\n";
} else {
    echo "✅ Saldo awal COA seimbang\n";
}

// 7. DETAIL AKUN DENGAN SALDO BESAR
echo "\n=== 7. AKUN DENGAN SALDO BESAR (>1 JUTA) ===\n";
foreach ($result['accounts'] as $account) {
    if (abs($account['saldo_akhir']) > 1000000) {
        $category = '';
        $firstDigit = substr($account['kode_akun'], 0, 1);
        if ($firstDigit == '1') $category = 'ASET';
        elseif ($firstDigit == '2') $category = 'KEWAJIBAN';
        elseif ($firstDigit == '3') $category = 'MODAL';
        elseif ($firstDigit == '4') $category = 'PENDAPATAN';
        elseif (in_array($firstDigit, ['5', '6'])) $category = 'BEBAN';
        
        echo "- {$account['kode_akun']}: {$account['nama_akun']} ({$category})\n";
        echo "  Saldo Akhir: Rp " . number_format($account['saldo_akhir'], 0, ',', '.') . "\n";
        echo "  Display: Debit Rp " . number_format($account['debit'], 0, ',', '.') . 
             " | Kredit Rp " . number_format($account['kredit'], 0, ',', '.') . "\n\n";
    }
}

echo "\n=== RINGKASAN MASALAH ===\n";
if (count($unbalancedJournals) > 0) {
    echo "1. ❌ Ada " . count($unbalancedJournals) . " jurnal yang tidak seimbang\n";
}
if (abs($totalSaldoAwal) > 0.01) {
    echo "2. ❌ Saldo awal COA tidak seimbang (Rp " . number_format($totalSaldoAwal, 0, ',', '.') . ")\n";
}
if (!$result['is_balanced']) {
    echo "3. ❌ Neraca saldo tidak seimbang (selisih Rp " . number_format($result['difference'], 0, ',', '.') . ")\n";
}
if (abs($balanceCheck) > 0.01) {
    echo "4. ❌ Persamaan akuntansi tidak seimbang (selisih Rp " . number_format($balanceCheck, 0, ',', '.') . ")\n";
}

echo "\n=== LANGKAH PERBAIKAN YANG DIPERLUKAN ===\n";
echo "1. Perbaiki jurnal yang tidak seimbang\n";
echo "2. Reset saldo awal COA jika perlu\n";
echo "3. Perbaiki logika TrialBalanceService\n";
echo "4. Verifikasi mapping debit/kredit\n";