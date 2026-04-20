<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JurnalUmum;
use App\Models\Penggajian;
use App\Models\ExpensePayment;

echo "=== CHECKING JURNAL DATA ===\n";

// Check penggajian entries
$penggajianCount = JurnalUmum::where('tipe_referensi', 'penggajian')->count();
echo "Penggajian entries in jurnal_umum: $penggajianCount\n";

// Check expense payment entries
$expenseCount = JurnalUmum::where('tipe_referensi', 'expense_payment')->count();
echo "Expense payment entries in jurnal_umum: $expenseCount\n";

// Check pembayaran beban entries
$bebanCount = JurnalUmum::where('tipe_referensi', 'pembayaran_beban')->count();
echo "Pembayaran beban entries in jurnal_umum: $bebanCount\n";

// Check total penggajian records
$totalPenggajian = Penggajian::count();
echo "Total penggajian records: $totalPenggajian\n";

// Check total expense payment records
$totalExpense = ExpensePayment::count();
echo "Total expense payment records: $totalExpense\n";

// Check recent penggajian entries
echo "\n=== RECENT PENGGAJIAN ENTRIES ===\n";
$recentPenggajian = JurnalUmum::where('tipe_referensi', 'penggajian')
    ->orderBy('tanggal', 'desc')
    ->limit(5)
    ->get(['tanggal', 'keterangan', 'debit', 'kredit']);

foreach ($recentPenggajian as $entry) {
    echo "Date: {$entry->tanggal}, Desc: {$entry->keterangan}, Debit: {$entry->debit}, Credit: {$entry->kredit}\n";
}

// Check recent expense payment entries
echo "\n=== RECENT EXPENSE PAYMENT ENTRIES ===\n";
$recentExpense = JurnalUmum::whereIn('tipe_referensi', ['expense_payment', 'pembayaran_beban'])
    ->orderBy('tanggal', 'desc')
    ->limit(5)
    ->get(['tanggal', 'keterangan', 'debit', 'kredit', 'tipe_referensi']);

foreach ($recentExpense as $entry) {
    echo "Date: {$entry->tanggal}, Type: {$entry->tipe_referensi}, Desc: {$entry->keterangan}, Debit: {$entry->debit}, Credit: {$entry->kredit}\n";
}

echo "\n=== DONE ===\n";