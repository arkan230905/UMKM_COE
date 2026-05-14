<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ANALISIS NERACA SALDO TIDAK BALANCE ===\n\n";

// Ambil semua journal entries dan lines
$startDate = '2026-01-01';
$endDate = '2026-04-30';

echo "Periode: {$startDate} s/d {$endDate}\n\n";

// Hitung total debit dan kredit dari journal_lines
$totalDebit = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
    ->sum('journal_lines.debit');

$totalKredit = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
    ->sum('journal_lines.credit');

echo "TOTAL MUTASI DARI JOURNAL_LINES:\n";
echo "  Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "  Total Kredit: Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "  Selisih: Rp " . number_format($totalKredit - $totalDebit, 0, ',', '.') . "\n\n";

if ($totalDebit != $totalKredit) {
    echo "❌ JOURNAL ENTRIES TIDAK BALANCE!\n";
    echo "   Setiap journal entry harus balance (debit = kredit)\n\n";
    
    // Cari journal entries yang tidak balance
    echo "Mencari journal entries yang tidak balance...\n\n";
    
    $entries = \App\Models\JournalEntry::whereBetween('tanggal', [$startDate, $endDate])
        ->with('lines.coa')
        ->get();
    
    $unbalancedEntries = [];
    
    foreach ($entries as $entry) {
        $entryDebit = $entry->lines->sum('debit');
        $entryKredit = $entry->lines->sum('credit');
        
        if ($entryDebit != $entryKredit) {
            $unbalancedEntries[] = [
                'id' => $entry->id,
                'tanggal' => $entry->tanggal,
                'memo' => $entry->memo,
                'ref_type' => $entry->ref_type,
                'debit' => $entryDebit,
                'kredit' => $entryKredit,
                'selisih' => $entryKredit - $entryDebit,
                'lines' => $entry->lines
            ];
        }
    }
    
    if (!empty($unbalancedEntries)) {
        echo "Ditemukan " . count($unbalancedEntries) . " journal entries yang tidak balance:\n\n";
        
        foreach ($unbalancedEntries as $ue) {
            echo "Entry ID: {$ue['id']}\n";
            echo "Tanggal: {$ue['tanggal']}\n";
            echo "Ref Type: {$ue['ref_type']}\n";
            echo "Memo: {$ue['memo']}\n";
            echo "Total Debit: Rp " . number_format($ue['debit'], 0, ',', '.') . "\n";
            echo "Total Kredit: Rp " . number_format($ue['kredit'], 0, ',', '.') . "\n";
            echo "Selisih: Rp " . number_format($ue['selisih'], 0, ',', '.') . "\n";
            echo "Lines:\n";
            
            foreach ($ue['lines'] as $line) {
                $coa = $line->coa;
                if ($coa) {
                    echo "  - {$coa->kode_akun} {$coa->nama_akun}\n";
                    echo "    Debit: Rp " . number_format($line->debit, 0, ',', '.') . " | Kredit: Rp " . number_format($line->credit, 0, ',', '.') . "\n";
                }
            }
            echo "\n" . str_repeat('-', 60) . "\n\n";
        }
    } else {
        echo "✅ Semua journal entries sudah balance\n\n";
    }
} else {
    echo "✅ JOURNAL ENTRIES SUDAH BALANCE\n\n";
}

// Cek saldo awal
echo "\n=== CEK SALDO AWAL ===\n\n";

$totalSaldoAwalDebit = 0;
$totalSaldoAwalKredit = 0;

$coas = \App\Models\Coa::all();

foreach ($coas as $coa) {
    $saldoAwal = (float) $coa->saldo_awal;
    
    if ($saldoAwal != 0) {
        // Tentukan apakah akun normal debit atau kredit
        $isDebitNormal = in_array($coa->tipe_akun, ['Asset', 'Expense']);
        
        if ($isDebitNormal) {
            if ($saldoAwal > 0) {
                $totalSaldoAwalDebit += $saldoAwal;
            } else {
                $totalSaldoAwalKredit += abs($saldoAwal);
            }
        } else {
            if ($saldoAwal > 0) {
                $totalSaldoAwalKredit += $saldoAwal;
            } else {
                $totalSaldoAwalDebit += abs($saldoAwal);
            }
        }
    }
}

echo "Total Saldo Awal Debit: Rp " . number_format($totalSaldoAwalDebit, 0, ',', '.') . "\n";
echo "Total Saldo Awal Kredit: Rp " . number_format($totalSaldoAwalKredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format(abs($totalSaldoAwalDebit - $totalSaldoAwalKredit), 0, ',', '.') . "\n\n";

if ($totalSaldoAwalDebit != $totalSaldoAwalKredit) {
    echo "❌ SALDO AWAL TIDAK BALANCE!\n";
    echo "   Ini adalah penyebab utama Neraca Saldo tidak balance\n\n";
    
    echo "Saldo awal yang tidak 0:\n";
    foreach ($coas as $coa) {
        if ($coa->saldo_awal != 0) {
            echo "  - {$coa->kode_akun} {$coa->nama_akun}: Rp " . number_format($coa->saldo_awal, 0, ',', '.') . " ({$coa->tipe_akun})\n";
        }
    }
} else {
    echo "✅ SALDO AWAL SUDAH BALANCE\n";
}

echo "\n" . str_repeat('=', 60) . "\n\n";

echo "KESIMPULAN:\n";
echo "Neraca Saldo tidak balance karena:\n";

if ($totalDebit != $totalKredit) {
    echo "1. ❌ Ada journal entries yang tidak balance (debit ≠ kredit)\n";
}

if ($totalSaldoAwalDebit != $totalSaldoAwalKredit) {
    echo "2. ❌ Saldo awal tidak balance\n";
    echo "   Selisih: Rp " . number_format(abs($totalSaldoAwalDebit - $totalSaldoAwalKredit), 0, ',', '.') . "\n";
}

echo "\nSOLUSI:\n";
if ($totalSaldoAwalDebit != $totalSaldoAwalKredit) {
    $selisih = $totalSaldoAwalKredit - $totalSaldoAwalDebit;
    if ($selisih > 0) {
        echo "- Perlu menambah saldo awal DEBIT sebesar Rp " . number_format($selisih, 0, ',', '.') . "\n";
        echo "  ATAU\n";
        echo "- Perlu mengurangi saldo awal KREDIT sebesar Rp " . number_format($selisih, 0, ',', '.') . "\n";
    } else {
        echo "- Perlu menambah saldo awal KREDIT sebesar Rp " . number_format(abs($selisih), 0, ',', '.') . "\n";
        echo "  ATAU\n";
        echo "- Perlu mengurangi saldo awal DEBIT sebesar Rp " . number_format(abs($selisih), 0, ',', '.') . "\n";
    }
    echo "\nBiasanya selisih ini adalah MODAL AWAL yang belum dicatat.\n";
}
