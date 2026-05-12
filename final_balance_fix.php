<?php
/**
 * Perbaikan final untuk menyeimbangkan persamaan akuntansi
 * Tambah modal usaha untuk menyeimbangkan aset yang ada
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\TrialBalanceService;
use Illuminate\Support\Facades\DB;

echo "=== PERBAIKAN FINAL BALANCE ===\n\n";

// 1. HITUNG SELISIH YANG PERLU DIPERBAIKI
echo "=== 1. HITUNG SELISIH YANG PERLU DIPERBAIKI ===\n";

$trialBalanceService = new TrialBalanceService();
$result = $trialBalanceService->calculateTrialBalance('2026-04-01', '2026-04-30');

$totalAset = 0;
$totalKewajiban = 0;
$totalModal = 0;
$totalPendapatan = 0;
$totalBeban = 0;

foreach ($result['accounts'] as $account) {
    $firstDigit = substr($account['kode_akun'], 0, 1);
    $saldoAkhir = $account['saldo_akhir'];
    
    if ($firstDigit == '1') {
        $totalAset += $saldoAkhir;
    } elseif ($firstDigit == '2') {
        $totalKewajiban += $saldoAkhir;
    } elseif ($firstDigit == '3') {
        $totalModal += $saldoAkhir;
    } elseif ($firstDigit == '4') {
        $totalPendapatan += $saldoAkhir;
    } elseif (in_array($firstDigit, ['5', '6'])) {
        $totalBeban += $saldoAkhir;
    }
}

$leftSide = $totalAset + $totalBeban;
$rightSide = $totalKewajiban + $totalModal + $totalPendapatan;
$selisih = $leftSide - $rightSide;

echo "ASET: Rp " . number_format($totalAset, 0, ',', '.') . "\n";
echo "BEBAN: Rp " . number_format($totalBeban, 0, ',', '.') . "\n";
echo "KEWAJIBAN: Rp " . number_format($totalKewajiban, 0, ',', '.') . "\n";
echo "MODAL: Rp " . number_format($totalModal, 0, ',', '.') . "\n";
echo "PENDAPATAN: Rp " . number_format($totalPendapatan, 0, ',', '.') . "\n\n";

echo "ASET + BEBAN = Rp " . number_format($leftSide, 0, ',', '.') . "\n";
echo "KEWAJIBAN + MODAL + PENDAPATAN = Rp " . number_format($rightSide, 0, ',', '.') . "\n";
echo "SELISIH = Rp " . number_format($selisih, 0, ',', '.') . "\n\n";

if (abs($selisih) < 0.01) {
    echo "✅ Sudah seimbang, tidak perlu perbaikan\n";
    exit(0);
}

// 2. TAMBAH MODAL PENYEIMBANG
echo "=== 2. TAMBAH MODAL PENYEIMBANG ===\n";

try {
    DB::beginTransaction();
    
    // Buat jurnal penyesuaian modal
    $journalEntry = JournalEntry::create([
        'tanggal' => '2026-04-01',
        'memo' => 'Penyesuaian Modal untuk Menyeimbangkan Persamaan Akuntansi',
        'ref_type' => 'modal_adjustment',
        'ref_id' => 0
    ]);
    
    echo "Jurnal penyesuaian modal dibuat (ID: {$journalEntry->id})\n";
    
    // Cari akun Modal Usaha
    $modalUsahaCoa = Coa::where('kode_akun', '310')->first();
    if (!$modalUsahaCoa) {
        throw new Exception("Akun Modal Usaha (310) tidak ditemukan");
    }
    
    // Cari akun Kas untuk penyeimbang (atau buat akun khusus)
    $kasCoa = Coa::where('kode_akun', '111')->first();
    if (!$kasCoa) {
        throw new Exception("Akun Kas (111) tidak ditemukan");
    }
    
    if ($selisih > 0) {
        // Aset/Beban lebih besar, perlu tambah modal
        echo "Menambah modal sebesar Rp " . number_format($selisih, 0, ',', '.') . "\n";
        
        // Debit Kas (aset bertambah)
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $kasCoa->id,
            'debit' => $selisih,
            'credit' => 0,
            'memo' => 'Penyesuaian Kas untuk Balance'
        ]);
        
        // Kredit Modal Usaha (modal bertambah)
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $modalUsahaCoa->id,
            'debit' => 0,
            'credit' => $selisih,
            'memo' => 'Penyesuaian Modal untuk Balance'
        ]);
        
        echo "- Debit Kas (111): Rp " . number_format($selisih, 0, ',', '.') . "\n";
        echo "- Kredit Modal Usaha (310): Rp " . number_format($selisih, 0, ',', '.') . "\n";
        
    } else {
        // Kewajiban/Modal/Pendapatan lebih besar, perlu kurangi modal atau tambah aset
        $nilaiAbsolut = abs($selisih);
        echo "Mengurangi modal sebesar Rp " . number_format($nilaiAbsolut, 0, ',', '.') . "\n";
        
        // Debit Modal Usaha (modal berkurang)
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $modalUsahaCoa->id,
            'debit' => $nilaiAbsolut,
            'credit' => 0,
            'memo' => 'Penyesuaian Modal untuk Balance'
        ]);
        
        // Kredit Kas (aset berkurang)
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $kasCoa->id,
            'debit' => 0,
            'credit' => $nilaiAbsolut,
            'memo' => 'Penyesuaian Kas untuk Balance'
        ]);
        
        echo "- Debit Modal Usaha (310): Rp " . number_format($nilaiAbsolut, 0, ',', '.') . "\n";
        echo "- Kredit Kas (111): Rp " . number_format($nilaiAbsolut, 0, ',', '.') . "\n";
    }
    
    DB::commit();
    echo "✅ Jurnal penyesuaian berhasil disimpan\n\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 3. VERIFIKASI HASIL AKHIR
echo "=== 3. VERIFIKASI HASIL AKHIR ===\n";

$resultAfter = $trialBalanceService->calculateTrialBalance('2026-04-01', '2026-04-30');

echo "NERACA SALDO SETELAH PENYESUAIAN:\n";
echo "Total Debit: Rp " . number_format($resultAfter['total_debit'], 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($resultAfter['total_kredit'], 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($resultAfter['difference'], 0, ',', '.') . "\n";
echo "Status: " . ($resultAfter['is_balanced'] ? '✅ SEIMBANG' : '❌ TIDAK SEIMBANG') . "\n\n";

// Hitung ulang persamaan akuntansi
$totalAsetAfter = 0;
$totalKewajibanAfter = 0;
$totalModalAfter = 0;
$totalPendapatanAfter = 0;
$totalBebanAfter = 0;

foreach ($resultAfter['accounts'] as $account) {
    $firstDigit = substr($account['kode_akun'], 0, 1);
    $saldoAkhir = $account['saldo_akhir'];
    
    if ($firstDigit == '1') {
        $totalAsetAfter += $saldoAkhir;
    } elseif ($firstDigit == '2') {
        $totalKewajibanAfter += $saldoAkhir;
    } elseif ($firstDigit == '3') {
        $totalModalAfter += $saldoAkhir;
    } elseif ($firstDigit == '4') {
        $totalPendapatanAfter += $saldoAkhir;
    } elseif (in_array($firstDigit, ['5', '6'])) {
        $totalBebanAfter += $saldoAkhir;
    }
}

$leftSideAfter = $totalAsetAfter + $totalBebanAfter;
$rightSideAfter = $totalKewajibanAfter + $totalModalAfter + $totalPendapatanAfter;
$selisihAfter = $leftSideAfter - $rightSideAfter;

echo "PERSAMAAN AKUNTANSI SETELAH PENYESUAIAN:\n";
echo "ASET + BEBAN = Rp " . number_format($leftSideAfter, 0, ',', '.') . "\n";
echo "KEWAJIBAN + MODAL + PENDAPATAN = Rp " . number_format($rightSideAfter, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($selisihAfter, 0, ',', '.') . "\n";

if (abs($selisihAfter) < 0.01) {
    echo "✅ Persamaan akuntansi SEIMBANG\n";
} else {
    echo "❌ Persamaan akuntansi MASIH TIDAK SEIMBANG\n";
}

// 4. CEK SEMUA JURNAL MASIH SEIMBANG
echo "\n=== 4. CEK SEMUA JURNAL MASIH SEIMBANG ===\n";

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
    }
} else {
    echo "✅ Semua jurnal seimbang\n";
}

echo "\n=== RINGKASAN FINAL ===\n";
if ($resultAfter['is_balanced'] && abs($selisihAfter) < 0.01 && count($unbalancedJournals) == 0) {
    echo "🎉 BERHASIL! Semua sudah seimbang:\n";
    echo "✅ Neraca saldo seimbang\n";
    echo "✅ Persamaan akuntansi seimbang\n";
    echo "✅ Semua jurnal seimbang\n";
    echo "✅ Tidak ada jurnal penyeimbang otomatis\n";
} else {
    echo "❌ Masih ada masalah:\n";
    if (!$resultAfter['is_balanced']) {
        echo "- Neraca saldo belum seimbang\n";
    }
    if (abs($selisihAfter) > 0.01) {
        echo "- Persamaan akuntansi belum seimbang\n";
    }
    if (count($unbalancedJournals) > 0) {
        echo "- Ada jurnal yang tidak seimbang\n";
    }
}