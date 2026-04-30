<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FIXING ACCOUNTING FLOW - SALDO AWAL + TRANSAKSI = SALDO AKHIR\n";
echo "========================================================\n";

echo "\n=== STEP 1: SET SALDO AWAL YANG BENAR DI COA ===\n";

// Set correct opening balances for each COA
$openingBalances = [
    '111' => 98500000,   // Kas Bank
    '112' => 75644200,   // Kas
    '1141' => 800000,   // Pers. Bahan Baku Jagung
    '1151' => 186120,   // Pers. Bahan Pendukung Susu
    '1152' => 430000,   // Pers. Bahan Pendukung Keju
    '1153' => 172000,   // Pers. Bahan Pendukung Kemasan (Cup)
    '1161' => 376040,   // Pers. Barang Jadi Jasuke
    '210' => 44760,     // Hutang Usaha
    '211' => 54000,     // Hutang Gaji
    '212' => 110000,    // PPN Keluaran
    '310' => 176164000, // Modal Usaha
    '513' => 1000000,   // Beban Tunjangan
    '514' => 100000,    // Beban Asuransi
];

// Get all COA accounts
$allCoas = \App\Models\Coa::where('user_id', 1)->get();

foreach ($allCoas as $coa) {
    $kodeAkun = $coa->kode_akun;
    
    if (isset($openingBalances[$kodeAkun])) {
        $newSaldoAwal = $openingBalances[$kodeAkun];
        $currentSaldoAwal = $coa->saldo_awal ?? 0;
        
        echo "Setting saldo awal {$kodeAkun} - {$coa->nama_akun}:\n";
        echo "  Current: Rp " . number_format($currentSaldoAwal, 0, ',', '.') . "\n";
        echo "  New: Rp " . number_format($newSaldoAwal, 0, ',', '.') . "\n";
        
        $coa->update([
            'saldo_awal' => $newSaldoAwal,
            'updated_at' => now(),
        ]);
        
        echo "  Status: UPDATED\n";
    } else {
        // Set to 0 for accounts not in opening balances
        if (($coa->saldo_awal ?? 0) != 0) {
            echo "Setting saldo awal {$kodeAkun} - {$coa->nama_akun} to 0:\n";
            echo "  Current: Rp " . number_format($coa->saldo_awal ?? 0, 0, ',', '.') . "\n";
            echo "  New: Rp 0\n";
            
            $coa->update([
                'saldo_awal' => 0,
                'updated_at' => now(),
            ]);
            
            echo "  Status: SET TO 0\n";
        }
    }
}

echo "\n=== STEP 2: GET TRANSAKSI DARI JURNAL UMUM ===\n";

// Get all journal lines for April 2026
$journalLines = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('coas.user_id', 1)
    ->whereMonth('journal_entries.tanggal', 4)
    ->whereYear('journal_entries.tanggal', 2026)
    ->select(
        'coas.kode_akun',
        'coas.nama_akun',
        'coas.tipe_akun',
        \Illuminate\Support\Facades\DB::raw('SUM(journal_lines.debit) as total_debit'),
        \Illuminate\Support\Facades\DB::raw('SUM(journal_lines.credit) as total_credit')
    )
    ->groupBy('coas.kode_akun', 'coas.nama_akun', 'coas.tipe_akun')
    ->orderBy('coas.kode_akun')
    ->get();

echo "Transaksi Jurnal Umum April 2026:\n";
echo "Kode Akun\tNama Akun\t\t\tTotal Debit\tTotal Credit\n";
echo "================================================================\n";

$transactions = [];
$totalDebit = 0;
$totalCredit = 0;

foreach ($journalLines as $line) {
    $debit = $line->total_debit ?? 0;
    $credit = $line->total_credit ?? 0;
    
    $transactions[$line->kode_akun] = [
        'nama' => $line->nama_akun,
        'tipe' => $line->tipe_akun,
        'debit' => $debit,
        'credit' => $credit
    ];
    
    $totalDebit += $debit;
    $totalCredit += $credit;
    
    printf("%-8s\t%-30s\t%10s\t%10s\n", 
        $line->kode_akun, 
        substr($line->nama_akun, 0, 30), 
        number_format($debit, 0, ',', '.'), 
        number_format($credit, 0, ',', '.')
    );
}

echo "\n================================================================\n";
echo "TOTAL\t\t\t\t" . number_format($totalDebit, 0, ',', '.') . "\t" . number_format($totalCredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format(abs($totalDebit - $totalCredit), 0, ',', '.') . "\n";
echo "Status: " . ($totalDebit == $totalCredit ? "SEIMBANG" : "TIDAK SEIMBANG") . "\n";

echo "\n=== STEP 3: HITUNG SALDO AKHIR (NERACA SALDO) ===\n";

echo "Perhitungan Saldo Akhir:\n";
echo "Kode Akun\tNama Akun\t\t\tSaldo Awal\tDebit\tCredit\tSaldo Akhir\n";
echo "============================================================================\n";

$finalBalances = [];
$finalDebit = 0;
$finalCredit = 0;

foreach ($allCoas as $coa) {
    $kodeAkun = $coa->kode_akun;
    $saldoAwal = $coa->saldo_awal ?? 0;
    
    $debit = 0;
    $credit = 0;
    
    if (isset($transactions[$kodeAkun])) {
        $debit = $transactions[$kodeAkun]['debit'];
        $credit = $transactions[$kodeAkun]['credit'];
    }
    
    // Calculate saldo akhir berdasarkan tipe akun
    if ($coa->tipe_akun == 'Aset') {
        $saldoAkhir = $saldoAwal + $debit - $credit; // Assets: Saldo Awal + Debit - Credit
        if ($saldoAkhir != 0) {
            $finalDebit += abs($saldoAkhir); // Show as debit if positive
        }
    } elseif ($coa->tipe_akun == 'Kewajiban') {
        $saldoAkhir = $saldoAwal + $credit - $debit; // Liabilities: Saldo Awal + Credit - Debit
        if ($saldoAkhir != 0) {
            $finalCredit += abs($saldoAkhir); // Show as credit if positive
        }
    } elseif ($coa->tipe_akun == 'Equity' || $coa->tipe_akun == 'Pendapatan') {
        $saldoAkhir = $saldoAwal + $credit - $debit; // Equity/Revenue: Saldo Awal + Credit - Debit
        if ($saldoAkhir != 0) {
            $finalCredit += abs($saldoAkhir); // Show as credit if positive
        }
    } else { // Biaya/Expense
        $saldoAkhir = $saldoAwal + $debit - $credit; // Expenses: Saldo Awal + Debit - Credit
        if ($saldoAkhir != 0) {
            $finalDebit += abs($saldoAkhir); // Show as debit if positive
        }
    }
    
    $finalBalances[$kodeAkun] = [
        'nama' => $coa->nama_akun,
        'tipe' => $coa->tipe_akun,
        'saldo_akhir' => $saldoAkhir
    ];
    
    if ($saldoAkhir != 0) {
        printf("%-8s\t%-30s\t%10s\t%10s\t%10s\t%10s\n", 
            $kodeAkun, 
            substr($coa->nama_akun, 0, 30), 
            number_format($saldoAwal, 0, ',', '.'), 
            number_format($debit, 0, ',', '.'), 
            number_format($credit, 0, ',', '.'), 
            number_format($saldoAkhir, 0, ',', '.')
        );
    }
}

echo "\n============================================================================\n";
echo "TOTAL\t\t\t\t\t\t" . number_format($finalDebit, 0, ',', '.') . "\t" . number_format($finalCredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format(abs($finalDebit - $finalCredit), 0, ',', '.') . "\n";
echo "Status: " . ($finalDebit == $finalCredit ? "SEIMBANG" : "TIDAK SEIMBANG") . "\n";

echo "\n=== STEP 4: VERIFICATION LAPORAN POSISI KEUANGAN ===\n";

// Calculate neraca posisi keuangan
$totalAset = 0;
$totalKewajiban = 0;
$totalEkuitas = 0;

foreach ($finalBalances as $kodeAkun => $data) {
    $saldoAkhir = $data['saldo_akhir'];
    
    if ($data['tipe'] == 'Aset') {
        $totalAset += $saldoAkhir;
    } elseif ($data['tipe'] == 'Kewajiban') {
        $totalKewajiban += $saldoAkhir;
    } elseif ($data['tipe'] == 'Equity' || $data['tipe'] == 'Pendapatan') {
        $totalEkuitas += $saldoAkhir;
    }
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
echo "Neraca Saldo: " . ($finalDebit == $finalCredit ? "SEIMBANG" : "TIDAK SEIMBANG") . "\n";
echo "Laporan Posisi Keuangan: " . ($neracaSelisih == 0 ? "SEIMBANG" : "TIDAK SEIMBANG") . "\n";
echo "Alur Akuntansi: Saldo Awal COA + Transaksi Jurnal = Saldo Akhir Neraca Saldo\n";

if ($finalDebit == $finalCredit && $neracaSelisih == 0) {
    echo "\nSUCCESS: Alur akuntansi sudah benar!\n";
    echo "1. Saldo awal COA sudah diatur dengan benar\n";
    echo "2. Transaksi jurnal umum sudah seimbang\n";
    echo "3. Saldo akhir dihitung dengan benar (Saldo Awal + Debit - Credit)\n";
    echo "4. Neraca saldo seimbang sempurna\n";
    echo "5. Laporan posisi keuangan juga seimbang\n";
    
    echo "\nExpected Neraca Saldo Display:\n";
    echo "Total Debit: Rp " . number_format($finalDebit, 0, ',', '.') . "\n";
    echo "Total Credit: Rp " . number_format($finalCredit, 0, ',', '.') . "\n";
    echo "Status: SEIMBANG\n";
    
    echo "\nExpected Laporan Posisi Keuangan Display:\n";
    echo "Total Aset: Rp " . number_format($totalAset, 0, ',', '.') . "\n";
    echo "Total Kewajiban & Ekuitas: Rp " . number_format($totalKewajiban + $totalEkuitas, 0, ',', '.') . "\n";
    echo "Status: SEIMBANG\n";
} else {
    echo "\nWARNING: Masih ada ketidakseimbangan\n";
    echo "Perlu investigasi lebih lanjut\n";
}

echo "\nAccounting flow fix completed!\n";
