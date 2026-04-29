<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing JournalService post() method...\n\n";

// Simulate user login
\Illuminate\Support\Facades\Auth::loginUsingId(1);

// Get the sale
$sale = \App\Models\Penjualan::find(4);
if (!$sale) {
    echo "Sale ID 4 not found\n";
    exit;
}

echo "Testing post() method for Sale ID: {$sale->id}\n\n";

// Create the journal lines manually (including HPP)
$journalService = new \App\Services\JournalService();

// Get HPP lines
$reflection = new ReflectionClass($journalService);
$method = $reflection->getMethod('createHPPLinesFromPenjualan');
$method->setAccessible(true);

$hppLines = $method->invoke($journalService, $sale);
echo "HPP Lines: " . count($hppLines) . "\n";

// Create basic sales lines
$lines = [];
$totalAmount = $sale->grand_total ?? $sale->total ?? 0;

// Debit line (Kas)
$lines[] = [
    'code' => '112', // Kas
    'debit' => $totalAmount,
    'credit' => 0,
    'memo' => 'Penerimaan tunai penjualan'
];

// Credit line (Penjualan)
$subtotalProduk = 500000; // From sale details
$lines[] = [
    'code' => '41', // Penjualan
    'debit' => 0,
    'credit' => $subtotalProduk,
    'memo' => 'Pendapatan penjualan produk'
];

// Credit line (PPN)
$biayaPPN = 55000; // From sale
$lines[] = [
    'code' => '212', // PPN Keluaran
    'debit' => 0,
    'credit' => $biayaPPN,
    'memo' => 'PPN Keluaran'
];

// Add HPP lines
$lines = array_merge($lines, $hppLines);

echo "Total Lines: " . count($lines) . "\n";
foreach ($lines as $i => $line) {
    echo "  Line " . ($i + 1) . ": {$line['code']} - Debit: " . number_format($line['debit'], 0, ',', '.') . ", Credit: " . number_format($line['credit'], 0, ',', '.') . " - {$line['memo']}\n";
}

// Calculate totals
$totalDebit = array_sum(array_column($lines, 'debit'));
$totalCredit = array_sum(array_column($lines, 'credit'));
echo "\nTotal Debit: " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Credit: " . number_format($totalCredit, 0, ',', '.') . "\n";
echo "Balance: " . ($totalDebit == $totalCredit ? "YES" : "NO") . "\n\n";

// Test the post method
echo "=== TESTING post() METHOD ===\n";
try {
    $memo = 'Penjualan #' . ($sale->nomor_penjualan ?? $sale->id);
    $tanggal = $sale->tanggal instanceof \Carbon\Carbon ? 
               $sale->tanggal->format('Y-m-d') : 
               $sale->tanggal;
    
    echo "Memo: {$memo}\n";
    echo "Tanggal: {$tanggal}\n";
    echo "Ref Type: sale\n";
    echo "Ref ID: {$sale->id}\n\n";
    
    // Delete existing journals first
    $journalService->deleteByRef('sale', $sale->id);
    echo "Deleted existing journals\n";
    
    // Post new journal
    $entry = $journalService->post($tanggal, 'sale', $sale->id, $memo, $lines, 1);
    echo "Journal posted successfully! Entry ID: {$entry->id}\n";
    
    // Check if journals were created in JurnalUmum
    echo "\n=== CHECKING JURNAL UMUM ENTRIES ===\n";
    $jurnalUmumEntries = \Illuminate\Support\Facades\DB::table('jurnal_umum')
        ->leftJoin('coas', 'coas.id', '=', 'jurnal_umum.coa_id')
        ->where('jurnal_umum.referensi', 'sale#' . $sale->id)
        ->select('jurnal_umum.*', 'coas.nama_akun', 'coas.kode_akun')
        ->orderBy('jurnal_umum.id')
        ->get();
    
    echo "Jurnal Umum Entries Created: {$jurnalUmumEntries->count()}\n";
    foreach ($jurnalUmumEntries as $journal) {
        echo "  {$journal->kode_akun} - {$journal->nama_akun}\n";
        echo "    Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
        echo "    Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
        echo "    Keterangan: {$journal->keterangan}\n";
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error in post() method: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\nPost method test completed!\n";
