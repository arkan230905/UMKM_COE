<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Account;
use App\Models\Coa;
use App\Models\ExpensePayment;
use App\Models\Penggajian;

echo "=== DEBUG JURNAL KAS & BANK ===\n\n";

// 1. Cek Pembayaran Beban Terakhir
echo "1. PEMBAYARAN BEBAN TERAKHIR:\n";
$lastExpense = ExpensePayment::orderBy('created_at', 'desc')->first();
if ($lastExpense) {
    echo "   ID: {$lastExpense->id}\n";
    echo "   Tanggal: {$lastExpense->tanggal}\n";
    echo "   Nominal: Rp " . number_format($lastExpense->nominal, 0, ',', '.') . "\n";
    echo "   Akun Kas/Bank: {$lastExpense->coa_kasbank}\n";
    
    // Cek jurnalnya
    $journal = JournalEntry::where('ref_type', 'expense_payment')
        ->where('ref_id', $lastExpense->id)
        ->first();
    
    if ($journal) {
        echo "   ✅ Jurnal ADA (ID: {$journal->id})\n";
        $lines = JournalLine::where('journal_entry_id', $journal->id)->get();
        echo "   Jurnal Lines:\n";
        foreach ($lines as $line) {
            $account = Account::find($line->account_id);
            echo "      - Akun: {$account->code} ({$account->name})\n";
            echo "        Debit: Rp " . number_format($line->debit, 0, ',', '.') . "\n";
            echo "        Kredit: Rp " . number_format($line->credit, 0, ',', '.') . "\n";
        }
    } else {
        echo "   ❌ JURNAL TIDAK ADA!\n";
    }
} else {
    echo "   Tidak ada data pembayaran beban\n";
}

echo "\n";

// 2. Cek Penggajian Terakhir
echo "2. PENGGAJIAN TERAKHIR:\n";
$lastPayroll = Penggajian::orderBy('created_at', 'desc')->first();
if ($lastPayroll) {
    echo "   ID: {$lastPayroll->id}\n";
    echo "   Tanggal: {$lastPayroll->tanggal_penggajian}\n";
    echo "   Total Gaji: Rp " . number_format($lastPayroll->total_gaji, 0, ',', '.') . "\n";
    echo "   Akun Kas/Bank: " . ($lastPayroll->coa_kasbank ?? 'TIDAK ADA') . "\n";
    
    // Cek jurnalnya
    $journal = JournalEntry::where('ref_type', 'penggajian')
        ->where('ref_id', $lastPayroll->id)
        ->first();
    
    if ($journal) {
        echo "   ✅ Jurnal ADA (ID: {$journal->id})\n";
        $lines = JournalLine::where('journal_entry_id', $journal->id)->get();
        echo "   Jurnal Lines:\n";
        foreach ($lines as $line) {
            $account = Account::find($line->account_id);
            echo "      - Akun: {$account->code} ({$account->name})\n";
            echo "        Debit: Rp " . number_format($line->debit, 0, ',', '.') . "\n";
            echo "        Kredit: Rp " . number_format($line->credit, 0, ',', '.') . "\n";
        }
    } else {
        echo "   ❌ JURNAL TIDAK ADA!\n";
    }
} else {
    echo "   Tidak ada data penggajian\n";
}

echo "\n";

// 3. Cek Saldo Akun Kas/Bank
echo "3. SALDO AKUN KAS & BANK:\n";
$kasBankCodes = ['1101', '1102', '1103', '101', '102'];
foreach ($kasBankCodes as $code) {
    $coa = Coa::where('kode_akun', $code)->first();
    if (!$coa) continue;
    
    $account = Account::where('code', $code)->first();
    if (!$account) {
        echo "   ❌ Akun {$code} ({$coa->nama_akun}) - TIDAK ADA DI ACCOUNTS!\n";
        continue;
    }
    
    // Hitung saldo dari jurnal
    $saldoAwal = $coa->saldo_awal ?? 0;
    $mutasi = JournalLine::where('account_id', $account->id)
        ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
        ->first();
    
    $totalDebit = $mutasi->total_debit ?? 0;
    $totalCredit = $mutasi->total_credit ?? 0;
    $saldoAkhir = $saldoAwal + $totalDebit - $totalCredit;
    
    echo "   {$code} ({$coa->nama_akun}):\n";
    echo "      Saldo Awal: Rp " . number_format($saldoAwal, 0, ',', '.') . "\n";
    echo "      Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
    echo "      Total Kredit: Rp " . number_format($totalCredit, 0, ',', '.') . "\n";
    echo "      Saldo Akhir: Rp " . number_format($saldoAkhir, 0, ',', '.') . "\n";
}

echo "\n=== SELESAI ===\n";
