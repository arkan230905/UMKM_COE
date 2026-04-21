<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test HPP Journal Entries ===" . PHP_EOL;

// Test the new HPP journal entry creation
echo PHP_EOL . "Testing HPP Journal Entry Creation:" . PHP_EOL;

// Get a sample penjualan to test
$penjualan = \App\Models\Penjualan::with('details.produk')->first();
if (!$penjualan) {
    echo "No penjualan found for testing" . PHP_EOL;
    exit;
}

echo "Testing with Penjualan #" . $penjualan->id . PHP_EOL;
echo "Tanggal: " . $penjualan->tanggal . PHP_EOL;
echo "Total: " . number_format($penjualan->total, 0) . PHP_EOL;

if ($penjualan->details && $penjualan->details->count() > 0) {
    echo "Multi-item penjualan:" . PHP_EOL;
    foreach ($penjualan->details as $detail) {
        echo "- " . $detail->produk->nama_produk . " (Qty: " . $detail->jumlah . ")" . PHP_EOL;
    }
} else {
    echo "Single-item penjualan: " . ($penjualan->produk->nama_produk ?? 'Unknown') . PHP_EOL;
}

// Test the journal service method
echo PHP_EOL . "Creating journal entries with HPP breakdown..." . PHP_EOL;

try {
    \App\Services\JournalService::createJournalFromPenjualan($penjualan);
    echo "✅ Journal entries created successfully!" . PHP_EOL;
    
    // Check the created journal entries
    $journalEntries = \DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.ref_type', 'sale')
        ->where('journal_entries.ref_id', $penjualan->id)
        ->select('journal_lines.debit', 'journal_lines.credit', 'coas.kode_akun', 'coas.nama_akun', 'journal_lines.memo')
        ->get();
    
    echo PHP_EOL . "Journal Entries Created:" . PHP_EOL;
    foreach ($journalEntries as $entry) {
        $amount = $entry->debit > 0 ? $entry->debit : $entry->credit;
        $type = $entry->debit > 0 ? 'DEBIT' : 'KREDIT';
        echo sprintf(
            "%s %s %s - %s: %s",
            $entry->kode_akun,
            $entry->nama_akun,
            $type,
            number_format($amount, 0),
            $entry->memo
        ) . PHP_EOL;
    }
    
    // Verify balance
    $totalDebit = $journalEntries->sum('debit');
    $totalCredit = $journalEntries->sum('credit');
    echo PHP_EOL . "Balance Check:" . PHP_EOL;
    echo "Total Debit: " . number_format($totalDebit, 0) . PHP_EOL;
    echo "Total Credit: " . number_format($totalCredit, 0) . PHP_EOL;
    echo "Status: " . ($totalDebit == $totalCredit ? "BALANCED" : "NOT BALANCED") . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error creating journal entries: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== Expected HPP Components ===" . PHP_EOL;
echo "Each penjualan should now create journal entries for:" . PHP_EOL;
echo "1. Debit: Kas/Bank/Piutang (penerimaan penjualan)" . PHP_EOL;
echo "2. Credit: Pendapatan (penjualan)" . PHP_EOL;
echo "3. Debit: Material components (BOM details)" . PHP_EOL;
echo "4. Debit: BTKL (Biaya Tenaga Kerja Langsung)" . PHP_EOL;
echo "5. Debit: BOP (Biaya Overhead Pabrik)" . PHP_EOL;
echo "6. Credit: Persediaan Barang Jadi (HPP total)" . PHP_EOL;

echo PHP_EOL . "=== Benefits ===" . PHP_EOL;
echo "✅ Detailed HPP breakdown in journal entries" . PHP_EOL;
echo "✅ Material usage tracked per component" . PHP_EOL;
echo "✅ BTKL and BOP costs clearly shown" . PHP_EOL;
echo "✅ Better cost tracking and analysis" . PHP_EOL;
echo "✅ Complete audit trail for HPP calculation" . PHP_EOL;
