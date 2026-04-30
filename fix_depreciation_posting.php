<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PERBAIKAN POSTING PENYUSUTAN ===\n\n";

// 1. Hapus jurnal penyusutan April 2026 yang salah
echo "Step 1: Hapus jurnal penyusutan April 2026 yang lama...\n";

$entries = \App\Models\JournalEntry::where('ref_type', 'depreciation')
    ->whereYear('tanggal', 2026)
    ->whereMonth('tanggal', 4)
    ->get();

foreach ($entries as $entry) {
    echo "  - Menghapus Entry ID: {$entry->id} (Ref ID: {$entry->ref_id})\n";
    
    // Hapus journal lines dulu
    \App\Models\JournalLine::where('journal_entry_id', $entry->id)->delete();
    
    // Hapus journal entry
    $entry->delete();
}

echo "  Total dihapus: " . $entries->count() . " entries\n\n";

// 2. Posting ulang dengan nilai yang benar dari schedule
echo "Step 2: Posting ulang dengan nilai dari schedule...\n\n";

$asets = \App\Models\Aset::whereNotNull('expense_coa_id')
    ->whereNotNull('accum_depr_coa_id')
    ->get();

$service = new \App\Services\DepreciationCalculationService();

foreach ($asets as $aset) {
    echo "Aset: {$aset->nama_aset} (ID: {$aset->id})\n";
    echo "Metode: {$aset->metode_penyusutan}\n";
    
    // Generate schedule
    $scheduleArray = $service->generateMonthlySchedule($aset);
    
    if (empty($scheduleArray)) {
        echo "  ❌ Schedule kosong, skip\n\n";
        continue;
    }
    
    // Cari schedule untuk April 2026
    $scheduleForThisMonth = null;
    foreach ($scheduleArray as $row) {
        if ($row['tahun_bulan'] === 'Apr 2026') {
            $scheduleForThisMonth = $row;
            break;
        }
    }
    
    if (!$scheduleForThisMonth) {
        echo "  ❌ Schedule untuk Apr 2026 tidak ditemukan, skip\n\n";
        continue;
    }
    
    $monthlyDepreciation = (float) $scheduleForThisMonth['penyusutan'];
    
    if ($monthlyDepreciation <= 0) {
        echo "  ❌ Penyusutan = 0, skip\n\n";
        continue;
    }
    
    echo "  Penyusutan dari schedule: Rp " . number_format($monthlyDepreciation, 0, ',', '.') . "\n";
    
    // Buat journal entry
    $currentDate = \Carbon\Carbon::parse('2026-04-30');
    $memo = "Penyusutan Aset {$aset->nama_aset} ({$aset->metode_penyusutan}) - Apr 2026";
    
    $journalEntry = \App\Models\JournalEntry::create([
        'tanggal' => $currentDate->format('Y-m-d'),
        'ref_type' => 'depreciation',
        'ref_id' => $aset->id,
        'memo' => $memo,
    ]);
    
    // DEBIT: Beban Penyusutan
    \App\Models\JournalLine::create([
        'journal_entry_id' => $journalEntry->id,
        'coa_id' => $aset->expense_coa_id,
        'debit' => $monthlyDepreciation,
        'credit' => 0,
        'memo' => "Beban Penyusutan - {$aset->nama_aset}",
    ]);
    
    // CREDIT: Akumulasi Penyusutan
    \App\Models\JournalLine::create([
        'journal_entry_id' => $journalEntry->id,
        'coa_id' => $aset->accum_depr_coa_id,
        'debit' => 0,
        'credit' => $monthlyDepreciation,
        'memo' => "Akumulasi Penyusutan - {$aset->nama_aset}",
    ]);
    
    echo "  ✅ Journal Entry ID: {$journalEntry->id} berhasil dibuat\n";
    echo "     Debit: {$aset->expense_coa_id} = Rp " . number_format($monthlyDepreciation, 0, ',', '.') . "\n";
    echo "     Kredit: {$aset->accum_depr_coa_id} = Rp " . number_format($monthlyDepreciation, 0, ',', '.') . "\n\n";
}

echo "\n=== SELESAI ===\n";
echo "Silakan cek kembali jurnal penyusutan April 2026\n";
