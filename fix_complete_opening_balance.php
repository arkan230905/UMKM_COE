<?php
/**
 * Perbaiki opening balance lengkap untuk semua akun
 * Buat jurnal pembukaan yang seimbang berdasarkan saldo akhir saat ini
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\TrialBalanceService;
use Illuminate\Support\Facades\DB;

echo "=== PERBAIKI OPENING BALANCE LENGKAP ===\n\n";

// 1. ANALISIS SALDO AKHIR SAAT INI
echo "=== 1. ANALISIS SALDO AKHIR SAAT INI ===\n";

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

// 2. HITUNG OPENING BALANCE YANG DIPERLUKAN
echo "\n=== 2. HITUNG OPENING BALANCE YANG DIPERLUKAN ===\n";

$aset = $categories['ASET']['total'];
$kewajiban = $categories['KEWAJIBAN']['total'];
$modal = $categories['MODAL']['total'];
$pendapatan = $categories['PENDAPATAN']['total'];
$beban = $categories['BEBAN']['total'];

echo "Saldo saat ini:\n";
echo "ASET: Rp " . number_format($aset, 0, ',', '.') . "\n";
echo "KEWAJIBAN: Rp " . number_format($kewajiban, 0, ',', '.') . "\n";
echo "MODAL: Rp " . number_format($modal, 0, ',', '.') . "\n";
echo "PENDAPATAN: Rp " . number_format($pendapatan, 0, ',', '.') . "\n";
echo "BEBAN: Rp " . number_format($beban, 0, ',', '.') . "\n\n";

// Untuk opening balance, kita perlu:
// ASET = KEWAJIBAN + MODAL (abaikan pendapatan/beban karena itu operasional)
$modalYangDiperlukan = $aset - $kewajiban;
$selisihModal = $modalYangDiperlukan - $modal;

echo "Modal yang diperlukan untuk balance: Rp " . number_format($modalYangDiperlukan, 0, ',', '.') . "\n";
echo "Modal saat ini: Rp " . number_format($modal, 0, ',', '.') . "\n";
echo "Selisih modal: Rp " . number_format($selisihModal, 0, ',', '.') . "\n\n";

// 3. BUAT JURNAL PEMBUKAAN YANG SEIMBANG
echo "=== 3. BUAT JURNAL PEMBUKAAN YANG SEIMBANG ===\n";

try {
    DB::beginTransaction();
    
    // Hapus jurnal pembukaan lama
    $oldOpeningJournal = JournalEntry::where('ref_type', 'opening_balance')->first();
    if ($oldOpeningJournal) {
        echo "Menghapus jurnal pembukaan lama (ID: {$oldOpeningJournal->id})\n";
        JournalLine::where('journal_entry_id', $oldOpeningJournal->id)->delete();
        $oldOpeningJournal->delete();
    }
    
    // Buat jurnal pembukaan baru
    $journalEntry = JournalEntry::create([
        'tanggal' => '2026-04-01',
        'memo' => 'Jurnal Pembukaan - Opening Balance Seimbang',
        'ref_type' => 'opening_balance',
        'ref_id' => 0
    ]);
    
    echo "Jurnal pembukaan baru dibuat (ID: {$journalEntry->id})\n\n";
    
    $totalJurnalDebit = 0;
    $totalJurnalKredit = 0;
    
    // STRATEGI: Buat opening balance hanya untuk akun yang benar-benar memiliki saldo awal
    // Abaikan pendapatan dan beban (karena itu hasil operasional, bukan opening balance)
    
    // 1. DEBIT semua aset yang memiliki saldo positif
    foreach ($categories['ASET']['accounts'] as $account) {
        if ($account['saldo_akhir'] > 0) {
            $coa = Coa::where('kode_akun', $account['kode_akun'])->first();
            if ($coa) {
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'coa_id' => $coa->id,
                    'debit' => $account['saldo_akhir'],
                    'credit' => 0,
                    'memo' => 'Opening Balance - ' . $account['nama_akun']
                ]);
                $totalJurnalDebit += $account['saldo_akhir'];
                echo "- Debit {$account['kode_akun']}: Rp " . number_format($account['saldo_akhir'], 0, ',', '.') . "\n";
            }
        }
    }
    
    // 2. KREDIT semua kewajiban yang memiliki saldo positif
    foreach ($categories['KEWAJIBAN']['accounts'] as $account) {
        if ($account['saldo_akhir'] > 0) {
            $coa = Coa::where('kode_akun', $account['kode_akun'])->first();
            if ($coa) {
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'coa_id' => $coa->id,
                    'debit' => 0,
                    'credit' => $account['saldo_akhir'],
                    'memo' => 'Opening Balance - ' . $account['nama_akun']
                ]);
                $totalJurnalKredit += $account['saldo_akhir'];
                echo "- Kredit {$account['kode_akun']}: Rp " . number_format($account['saldo_akhir'], 0, ',', '.') . "\n";
            }
        }
    }
    
    // 3. KREDIT modal untuk menyeimbangkan
    $modalPenyeimbang = $totalJurnalDebit - $totalJurnalKredit;
    if ($modalPenyeimbang > 0) {
        // Cari akun Modal Usaha
        $modalUsahaCoa = Coa::where('kode_akun', '310')->first();
        if ($modalUsahaCoa) {
            JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'coa_id' => $modalUsahaCoa->id,
                'debit' => 0,
                'credit' => $modalPenyeimbang,
                'memo' => 'Opening Balance - Modal Penyeimbang'
            ]);
            $totalJurnalKredit += $modalPenyeimbang;
            echo "- Kredit Modal Penyeimbang 310: Rp " . number_format($modalPenyeimbang, 0, ',', '.') . "\n";
        }
    }
    
    echo "\nTotal Jurnal Debit: Rp " . number_format($totalJurnalDebit, 0, ',', '.') . "\n";
    echo "Total Jurnal Kredit: Rp " . number_format($totalJurnalKredit, 0, ',', '.') . "\n";
    echo "Selisih: Rp " . number_format($totalJurnalDebit - $totalJurnalKredit, 0, ',', '.') . "\n";
    
    if (abs($totalJurnalDebit - $totalJurnalKredit) < 0.01) {
        echo "✅ Jurnal pembukaan seimbang!\n";
        DB::commit();
        echo "✅ Jurnal pembukaan berhasil disimpan\n\n";
    } else {
        echo "❌ Jurnal pembukaan tidak seimbang!\n";
        DB::rollback();
        echo "❌ Jurnal pembukaan dibatalkan\n\n";
        exit(1);
    }
    
} catch (\Exception $e) {
    DB::rollback();
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 4. VERIFIKASI HASIL AKHIR
echo "=== 4. VERIFIKASI HASIL AKHIR ===\n";

$resultAfter = $trialBalanceService->calculateTrialBalance('2026-04-01', '2026-04-30');

echo "NERACA SALDO SETELAH PERBAIKAN:\n";
echo "Total Debit: Rp " . number_format($resultAfter['total_debit'], 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($resultAfter['total_kredit'], 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($resultAfter['difference'], 0, ',', '.') . "\n";
echo "Status: " . ($resultAfter['is_balanced'] ? '✅ SEIMBANG' : '❌ TIDAK SEIMBANG') . "\n\n";

// 5. CEK SEMUA JURNAL SEIMBANG
echo "=== 5. CEK SEMUA JURNAL SEIMBANG ===\n";

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
    echo "\n❌ MASIH ADA " . count($unbalancedJournals) . " JURNAL TIDAK SEIMBANG:\n";
    foreach ($unbalancedJournals as $journal) {
        echo "- ID {$journal['id']} ({$journal['tanggal']}): {$journal['memo']}\n";
        echo "  Debit: Rp " . number_format($journal['debit'], 0, ',', '.') . 
             " | Kredit: Rp " . number_format($journal['kredit'], 0, ',', '.') . 
             " | Selisih: Rp " . number_format($journal['selisih'], 0, ',', '.') . "\n";
    }
} else {
    echo "✅ Semua jurnal sudah seimbang\n";
}

echo "\n=== RINGKASAN AKHIR ===\n";
if ($resultAfter['is_balanced'] && count($unbalancedJournals) == 0 && abs($totalAllDebit - $totalAllKredit) < 0.01) {
    echo "🎉 BERHASIL! Semua sudah seimbang:\n";
    echo "✅ Neraca saldo seimbang\n";
    echo "✅ Semua jurnal seimbang\n";
    echo "✅ Opening balance lengkap dan seimbang\n";
} else {
    echo "❌ Masih ada masalah:\n";
    if (!$resultAfter['is_balanced']) {
        echo "- Neraca saldo belum seimbang\n";
    }
    if (count($unbalancedJournals) > 0) {
        echo "- Ada jurnal yang tidak seimbang\n";
    }
    if (abs($totalAllDebit - $totalAllKredit) > 0.01) {
        echo "- Total jurnal tidak seimbang\n";
    }
}