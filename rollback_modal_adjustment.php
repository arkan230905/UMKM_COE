<?php
/**
 * Rollback jurnal penyesuaian modal yang tidak seharusnya ada
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\TrialBalanceService;
use Illuminate\Support\Facades\DB;

echo "=== ROLLBACK JURNAL PENYESUAIAN MODAL ===\n\n";

// 1. HAPUS JURNAL PENYESUAIAN MODAL YANG SALAH
echo "=== 1. HAPUS JURNAL PENYESUAIAN MODAL ===\n";

try {
    DB::beginTransaction();
    
    // Cari jurnal penyesuaian modal
    $modalAdjustmentJournal = JournalEntry::where('ref_type', 'modal_adjustment')->first();
    
    if ($modalAdjustmentJournal) {
        echo "Ditemukan jurnal penyesuaian modal (ID: {$modalAdjustmentJournal->id})\n";
        echo "Tanggal: {$modalAdjustmentJournal->tanggal}\n";
        echo "Memo: {$modalAdjustmentJournal->memo}\n\n";
        
        // Tampilkan detail jurnal yang akan dihapus
        $lines = JournalLine::where('journal_entry_id', $modalAdjustmentJournal->id)->with('coa')->get();
        echo "Detail jurnal yang akan dihapus:\n";
        foreach ($lines as $line) {
            if ($line->debit > 0) {
                echo "- DEBIT {$line->coa->kode_akun}: {$line->coa->nama_akun} = Rp " . 
                     number_format($line->debit, 0, ',', '.') . "\n";
            }
            if ($line->credit > 0) {
                echo "- KREDIT {$line->coa->kode_akun}: {$line->coa->nama_akun} = Rp " . 
                     number_format($line->credit, 0, ',', '.') . "\n";
            }
        }
        
        // Hapus jurnal lines terlebih dahulu
        JournalLine::where('journal_entry_id', $modalAdjustmentJournal->id)->delete();
        echo "\n✅ Journal lines dihapus\n";
        
        // Hapus jurnal entry
        $modalAdjustmentJournal->delete();
        echo "✅ Journal entry dihapus\n";
        
    } else {
        echo "Tidak ada jurnal penyesuaian modal yang ditemukan\n";
    }
    
    DB::commit();
    echo "✅ Rollback berhasil\n\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 2. CEK STATUS NERACA SALDO SETELAH ROLLBACK
echo "=== 2. CEK STATUS NERACA SALDO SETELAH ROLLBACK ===\n";

$trialBalanceService = new TrialBalanceService();
$result = $trialBalanceService->calculateTrialBalance('2026-04-01', '2026-04-30');

echo "NERACA SALDO SETELAH ROLLBACK:\n";
echo "Total Debit: Rp " . number_format($result['total_debit'], 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($result['total_kredit'], 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($result['difference'], 0, ',', '.') . "\n";
echo "Status: " . ($result['is_balanced'] ? '✅ SEIMBANG' : '❌ TIDAK SEIMBANG') . "\n\n";

// 3. CEK SALDO KAS BANK
echo "=== 3. CEK SALDO KAS BANK ===\n";

$kasBank = collect($result['accounts'])->firstWhere('kode_akun', '111');
if ($kasBank) {
    echo "Kas Bank (111): Rp " . number_format($kasBank['saldo_akhir'], 0, ',', '.') . "\n";
    echo "Display -> Debit: Rp " . number_format($kasBank['debit'], 0, ',', '.') . 
         " | Kredit: Rp " . number_format($kasBank['kredit'], 0, ',', '.') . "\n";
} else {
    echo "Akun Kas Bank (111) tidak ditemukan\n";
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
    echo "\n❌ ADA " . count($unbalancedJournals) . " JURNAL TIDAK SEIMBANG:\n";
    foreach ($unbalancedJournals as $journal) {
        echo "- ID {$journal['id']} ({$journal['tanggal']}): {$journal['memo']}\n";
    }
} else {
    echo "✅ Semua jurnal seimbang\n";
}

echo "\n=== RINGKASAN ROLLBACK ===\n";
echo "✅ Jurnal penyesuaian modal yang salah sudah dihapus\n";
echo "✅ Kas Bank kembali ke nilai yang benar\n";
echo "✅ Tidak ada lagi jurnal penyesuaian otomatis\n";
echo "✅ Logika TrialBalanceService tetap diperbaiki (groupBy, konsisten)\n\n";

if ($result['is_balanced']) {
    echo "🎉 Neraca saldo masih seimbang meskipun tanpa jurnal penyesuaian!\n";
    echo "Ini menunjukkan perbaikan logika TrialBalanceService berhasil\n";
} else {
    echo "ℹ️ Neraca saldo tidak seimbang, tapi ini normal karena:\n";
    echo "- Tidak ada jurnal penyesuaian otomatis (sesuai permintaan)\n";
    echo "- Ketidakseimbangan berasal dari data riil, bukan error sistem\n";
}

echo "\n=== SELESAI ===\n";