<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CEK JURNAL PENYUSUTAN APRIL 2026 ===\n\n";

$entries = \App\Models\JournalEntry::where('ref_type', 'depreciation')
    ->whereYear('tanggal', 2026)
    ->whereMonth('tanggal', 4)
    ->with(['lines.coa', 'lines' => function($q) {
        $q->orderBy('debit', 'desc');
    }])
    ->get();
    
echo "Total entries: " . $entries->count() . "\n\n";

foreach ($entries as $entry) {
    $aset = \App\Models\Aset::find($entry->ref_id);
    echo "Entry ID: {$entry->id}\n";
    echo "Aset: " . ($aset ? $aset->nama_aset : 'N/A') . " (ID: {$entry->ref_id})\n";
    echo "Tanggal: {$entry->tanggal}\n";
    echo "Memo: {$entry->memo}\n";
    echo "Lines:\n";
    
    foreach ($entry->lines as $line) {
        $coa = $line->coa;
        if ($coa) {
            $debit = number_format($line->debit, 0, ',', '.');
            $kredit = number_format($line->credit, 0, ',', '.');
            echo "  - {$coa->kode_akun} {$coa->nama_akun}\n";
            echo "    Debit: Rp {$debit} | Kredit: Rp {$kredit}\n";
        }
    }
    echo "\n" . str_repeat('-', 60) . "\n\n";
}

// Cek juga schedule yang di-generate untuk aset tertentu
echo "\n=== CEK SCHEDULE PENYUSUTAN ===\n\n";
$asets = \App\Models\Aset::whereNotNull('expense_coa_id')
    ->whereNotNull('accum_depr_coa_id')
    ->get();

foreach ($asets as $aset) {
    echo "Aset: {$aset->nama_aset} (ID: {$aset->id})\n";
    echo "Metode: {$aset->metode_penyusutan}\n";
    
    $service = new \App\Services\DepreciationCalculationService();
    $schedule = $service->generateMonthlySchedule($aset);
    
    if (!empty($schedule)) {
        echo "Schedule untuk April 2026:\n";
        foreach ($schedule as $row) {
            if ($row['tahun_bulan'] === 'Apr 2026') {
                echo "  - Periode: {$row['tahun_bulan']}\n";
                echo "  - Penyusutan: Rp " . number_format($row['penyusutan'], 0, ',', '.') . "\n";
                echo "  - Akumulasi: Rp " . number_format($row['akumulasi_penyusutan'], 0, ',', '.') . "\n";
                echo "  - Nilai Buku: Rp " . number_format($row['nilai_buku'], 0, ',', '.') . "\n";
                break;
            }
        }
    } else {
        echo "  Schedule kosong\n";
    }
    echo "\n";
}
