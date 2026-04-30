<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "SINKRONKAN NERACA SALDO DENGAN DATA BUKU BESAR\n";
echo "============================================\n";

echo "\n=== ANALISIS DATA BUKU BESAR (JOURNAL LINES) ===\n";

// Get all journal lines for April 2026
$journalLines = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_entries.user_id', 1)
    ->whereMonth('journal_entries.tanggal', 4)
    ->whereYear('journal_entries.tanggal', 2026)
    ->select(
        'coas.kode_akun',
        'coas.nama_akun',
        'coas.tipe_akun',
        \Illuminate\Support\Facades\DB::raw('SUM(journal_lines.debit) as total_debit'),
        \Illuminate\Support\Facades\DB::raw('SUM(journal_lines.kredit) as total_kredit')
    )
    ->groupBy('coas.kode_akun', 'coas.nama_akun', 'coas.tipe_akun')
    ->orderBy('coas.kode_akun')
    ->get();

echo "Data buku besar April 2026:\n";
echo "Kode Akun\tNama Akun\t\t\tTotal Debit\tTotal Kredit\tSaldo\n";
echo "================================================================\n";

$totalDebit = 0;
$totalKredit = 0;
$coaBalances = [];

foreach ($journalLines as $line) {
    $debit = $line->total_debit ?? 0;
    $kredit = $line->total_kredit ?? 0;
    
    // Calculate saldo based on account type
    if ($line->tipe_akun == 'Aset') {
        $saldo = $debit - $kredit; // Assets: Debit - Credit
    } elseif ($line->tipe_akun == 'Kewajiban') {
        $saldo = $kredit - $debit; // Liabilities: Credit - Debit
    } elseif ($line->tipe_akun == 'Equity' || $line->tipe_akun == 'Pendapatan') {
        $saldo = $kredit - $debit; // Equity/Revenue: Credit - Debit
    } else { // Biaya/Expense
        $saldo = $debit - $kredit; // Expenses: Debit - Credit
    }
    
    $coaBalances[$line->kode_akun] = [
        'nama' => $line->nama_akun,
        'tipe' => $line->tipe_akun,
        'debit' => $debit,
        'kredit' => $kredit,
        'saldo' => $saldo
    ];
    
    $totalDebit += $debit;
    $totalKredit += $kredit;
    
    printf("%-8s\t%-30s\t%10s\t%10s\t%10s\n", 
        $line->kode_akun, 
        substr($line->nama_akun, 0, 30), 
        number_format($debit, 0, ',', '.'), 
        number_format($kredit, 0, ',', '.'), 
        number_format($saldo, 0, ',', '.')
    );
}

echo "\n================================================================\n";
echo "TOTAL\t\t\t\t" . number_format($totalDebit, 0, ',', '.') . "\t" . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format(abs($totalDebit - $totalKredit), 0, ',', '.') . "\n";
echo "Status: " . ($totalDebit == $totalKredit ? "SEIMBANG" : "TIDAK SEIMBANG") . "\n";

echo "\n=== UPDATE COA SALDO AWAL SESUAI BUKU BESAR ===\n";

// Get all COA accounts
$allCoas = \App\Models\Coa::where('user_id', 1)->get();

foreach ($allCoas as $coa) {
    $kodeAkun = $coa->kode_akun;
    
    if (isset($coaBalances[$kodeAkun])) {
        $newSaldo = $coaBalances[$kodeAkun]['saldo'];
        $currentSaldo = $coa->saldo_awal ?? 0;
        
        if ($currentSaldo != $newSaldo) {
            echo "Updating {$kodeAkun} - {$coa->nama_akun}:\n";
            echo "  Current: Rp " . number_format($currentSaldo, 0, ',', '.') . "\n";
            echo "  New: Rp " . number_format($newSaldo, 0, ',', '.') . "\n";
            
            $coa->update([
                'saldo_awal' => $newSaldo,
                'updated_at' => now(),
            ]);
            
            echo "  Status: UPDATED\n";
        } else {
            echo "No change needed for {$kodeAkun} - {$coa->nama_akun}\n";
        }
    } else {
        // COA not in journal lines, set to 0
        if (($coa->saldo_awal ?? 0) != 0) {
            echo "Setting {$kodeAkun} - {$coa->nama_akun} to 0 (no journal activity):\n";
            echo "  Current: Rp " . number_format($coa->saldo_awal ?? 0, 0, ',', '.') . "\n";
            echo "  New: Rp 0\n";
            
            $coa->update([
                'saldo_awal' => 0,
                'updated_at' => now(),
            ]);
            
            echo "  Status: UPDATED\n";
        }
    }
}

echo "\n=== VERIFICATION NERACA SALDO ===\n";

// Calculate expected neraca saldo from updated COA
$expectedDebit = 0;
$expectedKredit = 0;

foreach ($allCoas as $coa) {
    $saldo = $coa->saldo_awal ?? 0;
    
    if ($coa->tipe_akun == 'Aset') {
        $expectedDebit += $saldo; // Assets show as debit
    } elseif ($coa->tipe_akun == 'Kewajiban') {
        $expectedKredit += $saldo; // Liabilities show as credit
    } elseif ($coa->tipe_akun == 'Equity' || $coa->tipe_akun == 'Pendapatan') {
        $expectedKredit += $saldo; // Equity/Revenue show as credit
    } else { // Biaya/Expense
        $expectedDebit += $saldo; // Expenses show as debit
    }
}

echo "Expected Neraca Saldo:\n";
echo "Total Debit: Rp " . number_format($expectedDebit, 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($expectedKredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format(abs($expectedDebit - $expectedKredit), 0, ',', '.') . "\n";
echo "Status: " . ($expectedDebit == $expectedKredit ? "SEIMBANG" : "TIDAK SEIMBANG") . "\n";

echo "\n=== VERIFICATION LAPORAN POSISI KEUANGAN ===\n";

// Calculate neraca posisi keuangan
$totalAset = 0;
$totalKewajiban = 0;
$totalEkuitas = 0;

foreach ($allCoas as $coa) {
    $saldo = $coa->saldo_awal ?? 0;
    
    if ($coa->tipe_akun == 'Aset') {
        $totalAset += $saldo;
    } elseif ($coa->tipe_akun == 'Kewajiban') {
        $totalKewajiban += $saldo;
    } elseif ($coa->tipe_akun == 'Equity' || $coa->tipe_akun == 'Pendapatan') {
        $totalEkuitas += $saldo;
    }
    // Biaya/Expense tidak termasuk dalam neraca posisi keuangan
}

echo "Laporan Posisi Keuangan:\n";
echo "Total Aset: Rp " . number_format($totalAset, 0, ',', '.') . "\n";
echo "Total Kewajiban: Rp " . number_format($totalKewajiban, 0, ',', '.') . "\n";
echo "Total Ekuitas: Rp " . number_format($totalEkuitas, 0, ',', '.') . "\n";
echo "Total Kewajiban & Ekuitas: Rp " . number_format($totalKewajiban + $totalEkuitas, 0, ',', '.') . "\n";

$neracaSelisih = $totalAset - ($totalKewajiban + $totalEkuitas);
echo "Selisih: Rp " . number_format(abs($neracaSelisih), 0, ',', '.') . "\n";
echo "Status: " . ($neracaSelisih == 0 ? "SEIMBANG" : "TIDAK SEIMBANG") . "\n";

echo "\n=== FINAL STATUS ===\n";
echo "Neraca Saldo: " . ($expectedDebit == $expectedKredit ? "SEIMBANG" : "TIDAK SEIMBANG") . "\n";
echo "Laporan Posisi Keuangan: " . ($neracaSelisih == 0 ? "SEIMBANG" : "TIDAK SEIMBANG") . "\n";
echo "Data Source: Buku Besar (Journal Lines)\n";

if ($expectedDebit == $expectedKredit && $neracaSelisih == 0) {
    echo "\nSUCCESS: Semua laporan neraca sudah seimbang sempurna!\n";
    echo "Data neraca saldo sekarang sesuai dengan buku besar\n";
    echo "Data laporan posisi keuangan juga seimbang\n";
    echo "Semua data sudah sejalur dari jurnal umum -> buku besar -> neraca\n";
} else {
    echo "\nWARNING: Masih ada ketidakseimbangan\n";
    echo "Perlu investigasi lebih lanjut\n";
}

echo "\nSinkronisasi neraca saldo dengan buku besar completed!\n";
