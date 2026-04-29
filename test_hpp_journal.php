<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing HPP Journal for Sales Transactions...\n\n";

// Simulate user login
\Illuminate\Support\Facades\Auth::loginUsingId(1);

// Check if required COA exists
echo "=== CHECKING REQUIRED COA ===\n";

// Check COA HPP
$coaHpp = \App\Models\Coa::withoutGlobalScopes()
    ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost'])
    ->where('nama_akun', 'like', '%HPP%')
    ->where('user_id', 1)
    ->get();

echo "COA HPP Found: {$coaHpp->count()}\n";
foreach ($coaHpp as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun}\n";
}

// Check COA Persediaan Barang Jadi
$coaPersediaan = \App\Models\Coa::withoutGlobalScopes()
    ->whereIn('tipe_akun', ['Asset', 'Aset'])
    ->where('nama_akun', 'like', '%Barang Jadi%')
    ->where('user_id', 1)
    ->get();

echo "\nCOA Persediaan Barang Jadi Found: {$coaPersediaan->count()}\n";
foreach ($coaPersediaan as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun}\n";
}

// Check recent sales transactions
echo "\n=== CHECKING RECENT SALES ===\n";

$sales = \App\Models\Penjualan::where('user_id', 1)
    ->orderBy('created_at', 'desc')
    ->limit(3)
    ->get();

echo "Recent Sales Found: {$sales->count()}\n\n";

foreach ($sales as $sale) {
    echo "Sale ID: {$sale->id}\n";
    echo "Nomor: {$sale->nomor_penjualan}\n";
    echo "Tanggal: {$sale->tanggal}\n";
    echo "Total: " . number_format($sale->total ?? 0, 0, ',', '.') . "\n";
    echo "Grand Total: " . number_format($sale->grand_total ?? 0, 0, ',', '.') . "\n";
    
    // Check sale details
    if ($sale->details && $sale->details->count() > 0) {
        echo "Details:\n";
        foreach ($sale->details as $detail) {
            $produk = $detail->produk;
            $hppPerUnit = (float)($produk->hpp ?? $produk->harga_pokok ?? $produk->harga_bom ?? 0);
            $totalHPP = round($hppPerUnit * $detail->jumlah);
            
            echo "  Product: {$produk->nama_produk}\n";
            echo "  Qty: {$detail->jumlah}\n";
            echo "  HPP per unit: " . number_format($hppPerUnit, 0, ',', '.') . "\n";
            echo "  Total HPP: " . number_format($totalHPP, 0, ',', '.') . "\n";
        }
    }
    
    // Check if HPP journal exists
    echo "\nJournal Entries for this sale:\n";
    
    $hppJournals = \Illuminate\Support\Facades\DB::table('jurnal_umum as ju')
        ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
        ->where('ju.referensi', 'sale#' . $sale->id)
        ->where(function($q) {
            $q->where('coas.nama_akun', 'like', '%HPP%')
              ->orWhere('coas.nama_akun', 'like', '%Barang Jadi%');
        })
        ->select('ju.*', 'coas.nama_akun', 'coas.kode_akun')
        ->get();
    
    echo "HPP Journal Entries Found: {$hppJournals->count()}\n";
    foreach ($hppJournals as $journal) {
        echo "  {$journal->kode_akun} - {$journal->nama_akun}\n";
        echo "    Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
        echo "    Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
        echo "    Keterangan: {$journal->keterangan}\n";
    }
    
    if ($hppJournals->count() == 0) {
        echo "  NO HPP JOURNAL ENTRIES FOUND!\n";
    }
    
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

// Test creating HPP journal for a specific sale
echo "=== TESTING HPP JOURNAL CREATION ===\n";

$testSale = $sales->first();
if ($testSale) {
    echo "Testing HPP journal creation for Sale ID: {$testSale->id}\n";
    
    try {
        \App\Services\JournalService::createJournalFromPenjualan($testSale);
        echo "HPP journal creation attempted successfully\n";
        
        // Check if HPP journals were created
        $hppJournals = \Illuminate\Support\Facades\DB::table('jurnal_umum as ju')
            ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
            ->where('ju.referensi', 'sale#' . $testSale->id)
            ->where(function($q) {
                $q->where('coas.nama_akun', 'like', '%HPP%')
                  ->orWhere('coas.nama_akun', 'like', '%Barang Jadi%');
            })
            ->select('ju.*', 'coas.nama_akun', 'coas.kode_akun')
            ->get();
        
        echo "HPP Journal Entries After Creation: {$hppJournals->count()}\n";
        foreach ($hppJournals as $journal) {
            echo "  {$journal->kode_akun} - {$journal->nama_akun}\n";
            echo "    Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
            echo "    Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
        }
        
    } catch (Exception $e) {
        echo "Error creating HPP journal: " . $e->getMessage() . "\n";
    }
}

echo "\nHPP journal test completed!\n";
