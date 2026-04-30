<?php
/**
 * Analisis mendalam persamaan akuntansi untuk menemukan sumber ketidakseimbangan
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\TrialBalanceService;
use Illuminate\Support\Facades\DB;

echo "=== ANALISIS PERSAMAAN AKUNTANSI ===\n\n";

// 1. ANALISIS SALDO AKHIR DETAIL
echo "=== 1. ANALISIS SALDO AKHIR DETAIL ===\n";

$trialBalanceService = new TrialBalanceService();
$result = $trialBalanceService->calculateTrialBalance('2026-04-01', '2026-04-30');

$categories = [
    'ASET' => [],
    'KEWAJIBAN' => [],
    'MODAL' => [],
    'PENDAPATAN' => [],
    'BEBAN' => []
];

foreach ($result['accounts'] as $account) {
    $firstDigit = substr($account['kode_akun'], 0, 1);
    
    if ($firstDigit == '1') {
        $categories['ASET'][] = $account;
    } elseif ($firstDigit == '2') {
        $categories['KEWAJIBAN'][] = $account;
    } elseif ($firstDigit == '3') {
        $categories['MODAL'][] = $account;
    } elseif ($firstDigit == '4') {
        $categories['PENDAPATAN'][] = $account;
    } elseif (in_array($firstDigit, ['5', '6'])) {
        $categories['BEBAN'][] = $account;
    }
}

foreach ($categories as $category => $accounts) {
    echo "\n{$category}:\n";
    $total = 0;
    foreach ($accounts as $account) {
        if (abs($account['saldo_akhir']) > 0.01) {
            echo "  {$account['kode_akun']}: {$account['nama_akun']} = Rp " . 
                 number_format($account['saldo_akhir'], 0, ',', '.') . "\n";
            $total += $account['saldo_akhir'];
        }
    }
    echo "  TOTAL {$category}: Rp " . number_format($total, 0, ',', '.') . "\n";
}

// 2. ANALISIS JURNAL PEMBUKAAN
echo "\n=== 2. ANALISIS JURNAL PEMBUKAAN ===\n";

$openingJournal = JournalEntry::where('ref_type', 'opening_balance')->with('lines.coa')->first();
if ($openingJournal) {
    echo "Jurnal Pembukaan ID: {$openingJournal->id}\n";
    echo "Tanggal: {$openingJournal->tanggal}\n";
    echo "Memo: {$openingJournal->memo}\n\n";
    
    $totalOpeningDebit = 0;
    $totalOpeningKredit = 0;
    
    echo "Detail Jurnal Pembukaan:\n";
    foreach ($openingJournal->lines as $line) {
        $totalOpeningDebit += $line->debit;
        $totalOpeningKredit += $line->credit;
        
        if ($line->debit > 0) {
            echo "  DEBIT {$line->coa->kode_akun}: {$line->coa->nama_akun} = Rp " . 
                 number_format($line->debit, 0, ',', '.') . "\n";
        }
        if ($line->credit > 0) {
            echo "  KREDIT {$line->coa->kode_akun}: {$line->coa->nama_akun} = Rp " . 
                 number_format($line->credit, 0, ',', '.') . "\n";
        }
    }
    
    echo "\nTotal Opening Debit: Rp " . number_format($totalOpeningDebit, 0, ',', '.') . "\n";
    echo "Total Opening Kredit: Rp " . number_format($totalOpeningKredit, 0, ',', '.') . "\n";
    echo "Selisih Opening: Rp " . number_format($totalOpeningDebit - $totalOpeningKredit, 0, ',', '.') . "\n";
} else {
    echo "❌ Tidak ada jurnal pembukaan ditemukan\n";
}

// 3. ANALISIS JURNAL OPERASIONAL (NON-OPENING)
echo "\n=== 3. ANALISIS JURNAL OPERASIONAL ===\n";

$operationalJournals = JournalEntry::where('ref_type', '!=', 'opening_balance')
    ->orWhereNull('ref_type')
    ->with('lines.coa')
    ->get();

$totalOpDebit = 0;
$totalOpKredit = 0;
$opCategories = [
    'ASET' => ['debit' => 0, 'kredit' => 0],
    'KEWAJIBAN' => ['debit' => 0, 'kredit' => 0],
    'MODAL' => ['debit' => 0, 'kredit' => 0],
    'PENDAPATAN' => ['debit' => 0, 'kredit' => 0],
    'BEBAN' => ['debit' => 0, 'kredit' => 0]
];

echo "Jurnal Operasional (non-opening balance):\n";
foreach ($operationalJournals as $journal) {
    $journalDebit = $journal->lines->sum('debit');
    $journalKredit = $journal->lines->sum('credit');
    
    $totalOpDebit += $journalDebit;
    $totalOpKredit += $journalKredit;
    
    echo "  ID {$journal->id} ({$journal->tanggal}): {$journal->memo}\n";
    echo "    Debit: Rp " . number_format($journalDebit, 0, ',', '.') . 
         " | Kredit: Rp " . number_format($journalKredit, 0, ',', '.') . "\n";
    
    // Kategorisasi per akun
    foreach ($journal->lines as $line) {
        if ($line->coa) {
            $firstDigit = substr($line->coa->kode_akun, 0, 1);
            $category = '';
            
            if ($firstDigit == '1') $category = 'ASET';
            elseif ($firstDigit == '2') $category = 'KEWAJIBAN';
            elseif ($firstDigit == '3') $category = 'MODAL';
            elseif ($firstDigit == '4') $category = 'PENDAPATAN';
            elseif (in_array($firstDigit, ['5', '6'])) $category = 'BEBAN';
            
            if ($category) {
                $opCategories[$category]['debit'] += $line->debit;
                $opCategories[$category]['kredit'] += $line->credit;
            }
        }
    }
}

echo "\nTotal Operasional Debit: Rp " . number_format($totalOpDebit, 0, ',', '.') . "\n";
echo "Total Operasional Kredit: Rp " . number_format($totalOpKredit, 0, ',', '.') . "\n";
echo "Selisih Operasional: Rp " . number_format($totalOpDebit - $totalOpKredit, 0, ',', '.') . "\n";

echo "\nMutasi Operasional per Kategori:\n";
foreach ($opCategories as $category => $data) {
    $netChange = $data['debit'] - $data['kredit'];
    echo "  {$category}: Debit Rp " . number_format($data['debit'], 0, ',', '.') . 
         " - Kredit Rp " . number_format($data['kredit'], 0, ',', '.') . 
         " = Net Rp " . number_format($netChange, 0, ',', '.') . "\n";
}

// 4. REKONSILIASI SALDO AKHIR
echo "\n=== 4. REKONSILIASI SALDO AKHIR ===\n";

echo "Rumus: Saldo Akhir = Opening Balance + Mutasi Operasional\n\n";

$totalAset = array_sum(array_column($categories['ASET'], 'saldo_akhir'));
$totalKewajiban = array_sum(array_column($categories['KEWAJIBAN'], 'saldo_akhir'));
$totalModal = array_sum(array_column($categories['MODAL'], 'saldo_akhir'));
$totalPendapatan = array_sum(array_column($categories['PENDAPATAN'], 'saldo_akhir'));
$totalBeban = array_sum(array_column($categories['BEBAN'], 'saldo_akhir'));

echo "SALDO AKHIR:\n";
echo "ASET: Rp " . number_format($totalAset, 0, ',', '.') . "\n";
echo "KEWAJIBAN: Rp " . number_format($totalKewajiban, 0, ',', '.') . "\n";
echo "MODAL: Rp " . number_format($totalModal, 0, ',', '.') . "\n";
echo "PENDAPATAN: Rp " . number_format($totalPendapatan, 0, ',', '.') . "\n";
echo "BEBAN: Rp " . number_format($totalBeban, 0, ',', '.') . "\n\n";

// 5. CEK PERSAMAAN AKUNTANSI
echo "=== 5. CEK PERSAMAAN AKUNTANSI ===\n";

$leftSide = $totalAset + $totalBeban;
$rightSide = $totalKewajiban + $totalModal + $totalPendapatan;
$difference = $leftSide - $rightSide;

echo "ASET + BEBAN = KEWAJIBAN + MODAL + PENDAPATAN\n";
echo "Rp " . number_format($leftSide, 0, ',', '.') . " = Rp " . number_format($rightSide, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($difference, 0, ',', '.') . "\n";

if (abs($difference) < 0.01) {
    echo "✅ Persamaan akuntansi SEIMBANG\n";
} else {
    echo "❌ Persamaan akuntansi TIDAK SEIMBANG\n";
    
    echo "\nKemungkinan penyebab:\n";
    if ($difference > 0) {
        echo "- Aset/Beban terlalu besar dibanding sumber pendanaan\n";
        echo "- Kurang modal atau pendapatan\n";
        echo "- Ada transaksi yang tidak lengkap (hanya debit tanpa kredit)\n";
    } else {
        echo "- Kewajiban/Modal/Pendapatan terlalu besar\n";
        echo "- Kurang aset atau ada beban yang tidak tercatat\n";
    }
}

// 6. REKOMENDASI PERBAIKAN
echo "\n=== 6. REKOMENDASI PERBAIKAN ===\n";

if (abs($difference) > 0.01) {
    echo "Untuk memperbaiki ketidakseimbangan sebesar Rp " . number_format(abs($difference), 0, ',', '.') . ":\n\n";
    
    if ($difference > 0) {
        echo "OPSI 1: Tambah Modal Awal\n";
        echo "- Buat jurnal: Debit Kas/Aset Rp " . number_format($difference, 0, ',', '.') . "\n";
        echo "                Kredit Modal Usaha Rp " . number_format($difference, 0, ',', '.') . "\n\n";
        
        echo "OPSI 2: Koreksi Aset yang Overvalued\n";
        echo "- Periksa persediaan yang mungkin terlalu tinggi\n";
        echo "- Koreksi nilai aset yang tidak realistis\n\n";
    } else {
        echo "OPSI 1: Tambah Aset atau Kurangi Kewajiban\n";
        echo "- Periksa kewajiban yang mungkin double count\n";
        echo "- Tambah aset yang belum tercatat\n\n";
    }
    
    echo "OPSI 3: Audit Jurnal Pembukaan\n";
    echo "- Pastikan opening balance mencerminkan kondisi riil perusahaan\n";
    echo "- Sesuaikan dengan neraca periode sebelumnya jika ada\n";
}

echo "\n=== RINGKASAN ===\n";
echo "Total Jurnal Debit: Rp " . number_format($totalOpDebit + ($openingJournal ? $openingJournal->lines->sum('debit') : 0), 0, ',', '.') . "\n";
echo "Total Jurnal Kredit: Rp " . number_format($totalOpKredit + ($openingJournal ? $openingJournal->lines->sum('credit') : 0), 0, ',', '.') . "\n";
echo "Selisih Persamaan Akuntansi: Rp " . number_format($difference, 0, ',', '.') . "\n";
echo "Status: " . (abs($difference) < 0.01 ? '✅ SEIMBANG' : '❌ TIDAK SEIMBANG') . "\n";