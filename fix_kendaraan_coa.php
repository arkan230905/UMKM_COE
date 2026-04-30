<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PERBAIKAN COA KENDARAAN ===\n\n";

// Cari COA Beban Penyusutan Kendaraan
echo "Mencari COA Beban Penyusutan Kendaraan...\n";
$bebanCoa = \App\Models\Coa::where('nama_akun', 'LIKE', '%Beban%Penyusutan%Kendaraan%')
    ->orWhere('nama_akun', 'LIKE', '%Biaya%Penyusutan%Kendaraan%')
    ->first();

if (!$bebanCoa) {
    echo "  Tidak ditemukan, cari yang mirip...\n";
    $bebanCoa = \App\Models\Coa::where('tipe_akun', 'Expense')
        ->where(function($q) {
            $q->where('nama_akun', 'LIKE', '%Penyusutan%')
              ->orWhere('nama_akun', 'LIKE', '%Depresiasi%');
        })
        ->get();
    
    echo "\n  COA Beban Penyusutan yang tersedia:\n";
    foreach ($bebanCoa as $coa) {
        echo "    - ID: {$coa->id} | {$coa->kode_akun} - {$coa->nama_akun}\n";
    }
} else {
    echo "  ✅ Ditemukan: {$bebanCoa->kode_akun} - {$bebanCoa->nama_akun}\n";
}

// Cari COA Akumulasi Penyusutan Kendaraan
echo "\nMencari COA Akumulasi Penyusutan Kendaraan...\n";
$accumCoa = \App\Models\Coa::where('nama_akun', 'LIKE', '%Akumulasi%Penyusutan%Kendaraan%')
    ->where('kode_akun', '124')
    ->first();

if ($accumCoa) {
    echo "  ✅ Ditemukan: {$accumCoa->kode_akun} - {$accumCoa->nama_akun} (ID: {$accumCoa->id})\n";
}

echo "\n" . str_repeat('-', 60) . "\n\n";

// Update aset kendaraan
$aset = \App\Models\Aset::find(5);
echo "Update Aset: {$aset->nama_aset}\n";
echo "  expense_coa_id (lama): {$aset->expense_coa_id}\n";
echo "  accum_depr_coa_id (lama): {$aset->accum_depr_coa_id}\n\n";

// Jika ada COA beban yang ditemukan, update
if ($bebanCoa && !is_a($bebanCoa, 'Illuminate\Database\Eloquent\Collection')) {
    $aset->expense_coa_id = $bebanCoa->id;
    echo "  expense_coa_id (baru): {$bebanCoa->id} ({$bebanCoa->kode_akun} - {$bebanCoa->nama_akun})\n";
} else {
    echo "  ⚠️  Silakan pilih COA Beban Penyusutan dari list di atas\n";
    echo "     Masukkan ID COA yang sesuai untuk expense_coa_id\n";
}

if ($accumCoa) {
    $aset->accum_depr_coa_id = $accumCoa->id;
    echo "  accum_depr_coa_id (baru): {$accumCoa->id} ({$accumCoa->kode_akun} - {$accumCoa->nama_akun})\n";
}

// Hanya save jika kedua COA sudah benar dan berbeda
if ($aset->expense_coa_id && $aset->accum_depr_coa_id && $aset->expense_coa_id !== $aset->accum_depr_coa_id) {
    $aset->save();
    echo "\n✅ Aset berhasil diupdate!\n";
    
    // Hapus jurnal lama dan posting ulang
    echo "\nMenghapus jurnal lama untuk kendaraan...\n";
    $oldEntry = \App\Models\JournalEntry::where('ref_type', 'depreciation')
        ->where('ref_id', 5)
        ->whereYear('tanggal', 2026)
        ->whereMonth('tanggal', 4)
        ->first();
    
    if ($oldEntry) {
        \App\Models\JournalLine::where('journal_entry_id', $oldEntry->id)->delete();
        $oldEntry->delete();
        echo "  ✅ Jurnal lama dihapus\n";
    }
    
    echo "\nPosting ulang dengan COA yang benar...\n";
    $service = new \App\Services\DepreciationCalculationService();
    $scheduleArray = $service->generateMonthlySchedule($aset);
    
    $scheduleForThisMonth = null;
    foreach ($scheduleArray as $row) {
        if ($row['tahun_bulan'] === 'Apr 2026') {
            $scheduleForThisMonth = $row;
            break;
        }
    }
    
    if ($scheduleForThisMonth) {
        $monthlyDepreciation = (float) $scheduleForThisMonth['penyusutan'];
        
        $journalEntry = \App\Models\JournalEntry::create([
            'tanggal' => '2026-04-30',
            'ref_type' => 'depreciation',
            'ref_id' => $aset->id,
            'memo' => "Penyusutan Aset {$aset->nama_aset} ({$aset->metode_penyusutan}) - Apr 2026",
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
        
        echo "  ✅ Jurnal baru berhasil dibuat (Entry ID: {$journalEntry->id})\n";
        echo "     Debit: COA {$aset->expense_coa_id} = Rp " . number_format($monthlyDepreciation, 0, ',', '.') . "\n";
        echo "     Kredit: COA {$aset->accum_depr_coa_id} = Rp " . number_format($monthlyDepreciation, 0, ',', '.') . "\n";
    }
} else {
    echo "\n⚠️  Belum bisa save, COA belum lengkap atau masih sama\n";
}
