<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking Transaction Details for Kas & Bank Accounts:\n";
echo "====================================================\n";

$helper = new \App\Helpers\AccountHelper();
$akunKasBank = $helper::getKasBankAccounts();

$startDate = now()->startOfMonth()->format('Y-m-d');
$endDate = now()->endOfMonth()->format('Y-m-d');

echo "Period: $startDate to $endDate\n\n";

foreach ($akunKasBank as $akun) {
    echo "ACCOUNT: " . $akun->kode_akun . " - " . $akun->nama_akun . "\n";
    echo str_repeat("=", 60) . "\n";
    
    // Get all journal lines for this account
    $journalLines = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
        ->where('journal_lines.coa_id', $akun->id)
        ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
        ->select(
            'journal_entries.tanggal',
            'journal_entries.memo',
            'journal_entries.ref_type',
            'journal_entries.ref_id',
            'journal_lines.debit',
            'journal_lines.credit',
            'journal_lines.memo as line_memo'
        )
        ->orderBy('journal_entries.tanggal')
        ->orderBy('journal_entries.id')
        ->get();
    
    if ($journalLines->isEmpty()) {
        echo "No transactions found for this account.\n\n";
        continue;
    }
    
    $totalDebit = 0;
    $totalCredit = 0;
    
    echo "TRANSACTION DETAILS:\n";
    echo "Date       | Type    | Amount        | Description\n";
    echo str_repeat("-", 60) . "\n";
    
    foreach ($journalLines as $line) {
        $type = $line->debit > 0 ? 'MASUK' : 'KELUAR';
        $amount = $line->debit > 0 ? $line->debit : $line->credit;
        $totalDebit += $line->debit;
        $totalCredit += $line->credit;
        
        // Get more detailed description based on ref_type
        $description = $line->memo;
        $detailInfo = '';
        
        switch ($line->ref_type) {
            case 'sale':
                $sale = \App\Models\Penjualan::find($line->ref_id);
                if ($sale) {
                    $detailInfo = " (Penjualan: " . ($sale->nomor_penjualan ?? 'N/A') . ", Method: " . ($sale->payment_method ?? 'N/A') . ")";
                }
                break;
                
            case 'purchase':
                $purchase = \App\Models\Pembelian::find($line->ref_id);
                if ($purchase) {
                    $detailInfo = " (Pembelian: " . ($purchase->nomor_pembelian ?? 'N/A') . ", Method: " . ($purchase->payment_method ?? 'N/A') . ")";
                }
                break;
                
            case 'expense_payment':
                $expense = \App\Models\ExpensePayment::with('bebanOperasional')->find($line->ref_id);
                if ($expense) {
                    $bebanName = $expense->bebanOperasional->nama_beban ?? 'N/A';
                    $detailInfo = " (Beban: $bebanName, Method: " . ($expense->metode_bayar ?? 'N/A') . ")";
                }
                break;
        }
        
        printf("%-10s | %-7s | %12s | %s%s\n", 
            $line->tanggal, 
            $type, 
            'Rp ' . number_format($amount, 0, ',', '.'),
            $description,
            $detailInfo
        );
    }
    
    echo str_repeat("-", 60) . "\n";
    echo "SUMMARY:\n";
    echo "Total Masuk (Debit):  Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
    echo "Total Keluar (Credit): Rp " . number_format($totalCredit, 0, ',', '.') . "\n";
    echo "Net Change:           Rp " . number_format($totalDebit - $totalCredit, 0, ',', '.') . "\n";
    
    // Calculate balance
    $laporanController = new \App\Http\Controllers\LaporanKasBankController();
    $reflection = new ReflectionClass($laporanController);
    
    $getSaldoAwalMethod = $reflection->getMethod('getSaldoAwal');
    $getSaldoAwalMethod->setAccessible(true);
    $saldoAwal = $getSaldoAwalMethod->invoke($laporanController, $akun, $startDate);
    
    $saldoAkhir = $saldoAwal + $totalDebit - $totalCredit;
    
    echo "Saldo Awal:           Rp " . number_format($saldoAwal, 0, ',', '.') . "\n";
    echo "Saldo Akhir:          Rp " . number_format($saldoAkhir, 0, ',', '.') . "\n";
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
}

// Summary of all accounts
echo "OVERALL SUMMARY:\n";
echo "================\n";

$grandTotalMasuk = 0;
$grandTotalKeluar = 0;

foreach ($akunKasBank as $akun) {
    $laporanController = new \App\Http\Controllers\LaporanKasBankController();
    $reflection = new ReflectionClass($laporanController);
    
    $getTransaksiMasukMethod = $reflection->getMethod('getTransaksiMasuk');
    $getTransaksiMasukMethod->setAccessible(true);
    $transaksiMasuk = $getTransaksiMasukMethod->invoke($laporanController, $akun, $startDate, $endDate);
    
    $getTransaksiKeluarMethod = $reflection->getMethod('getTransaksiKeluar');
    $getTransaksiKeluarMethod->setAccessible(true);
    $transaksiKeluar = $getTransaksiKeluarMethod->invoke($laporanController, $akun, $startDate, $endDate);
    
    $grandTotalMasuk += $transaksiMasuk;
    $grandTotalKeluar += $transaksiKeluar;
    
    echo $akun->kode_akun . " - " . $akun->nama_akun . ":\n";
    echo "  Masuk: Rp " . number_format($transaksiMasuk, 0, ',', '.') . "\n";
    echo "  Keluar: Rp " . number_format($transaksiKeluar, 0, ',', '.') . "\n";
}

echo "\nGrand Total:\n";
echo "Total Masuk:  Rp " . number_format($grandTotalMasuk, 0, ',', '.') . "\n";
echo "Total Keluar: Rp " . number_format($grandTotalKeluar, 0, ',', '.') . "\n";
echo "Net Cash Flow: Rp " . number_format($grandTotalMasuk - $grandTotalKeluar, 0, ',', '.') . "\n";