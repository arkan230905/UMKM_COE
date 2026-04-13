<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CHECKING PEMBELIAN CREDIT ISSUE\n";
echo "===============================\n\n";

// Check PB-20260412-0004 specifically
$pb4 = App\Models\Pembelian::where('nomor_pembelian', 'PB-20260412-0004')->first();

if ($pb4) {
    echo "Pembelian PB-20260412-0004:\n";
    echo "  Payment Method: {$pb4->payment_method}\n";
    echo "  Total Harga: " . number_format($pb4->total_harga, 0, ',', '.') . "\n";
    echo "  Bank ID: {$pb4->bank_id}\n";
    
    $journals = App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
        ->where('referensi', 'PB-20260412-0004')
        ->with('coa')
        ->get();
    
    echo "\nJournals for PB-20260412-0004:\n";
    foreach ($journals as $journal) {
        $type = $journal->debit > 0 ? 'DEBIT' : 'KREDIT';
        $amount = $journal->debit > 0 ? $journal->debit : $journal->kredit;
        echo "  {$journal->coa->kode_akun} | {$type} | Rp " . number_format($amount, 0, ',', '.') . "\n";
    }
    
    echo "\nISSUE: Pembelian credit seharusnya tidak mengurangi kas!\n";
    echo "Expected journals for pembelian credit:\n";
    echo "  1. Debit Persediaan\n";
    echo "  2. Debit PPN\n";
    echo "  3. Kredit Hutang Usaha (BUKAN Kas)\n";
    
    // Check if there's a hutang journal
    $hutangJournal = $journals->firstWhere('coa.kode_akun', '210');
    if ($hutangJournal) {
        echo "  Found Hutang journal: Rp " . number_format($hutangJournal->kredit, 0, ',', '.') . "\n";
    } else {
        echo "  MISSING: Hutang journal\n";
    }
    
} else {
    echo "Pembelian PB-20260412-0004 not found!\n";
}

echo "\nChecking all pembelian by payment method:\n";
echo "=====================================\n";

$pembelians = App\Models\Pembelian::get();
foreach ($pembelians as $p) {
    echo "\n{$p->nomor_pembelian} | {$p->payment_method} | " . number_format($p->total_harga, 0, ',', '.') . "\n";
    
    $journals = App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
        ->where('referensi', $p->nomor_pembelian)
        ->where('debit', '>', 0)
        ->orWhere(function($query) use ($p) {
            $query->where('tipe_referensi', 'pembelian')
                   ->where('referensi', $p->nomor_pembelian)
                   ->where('kredit', '>', 0);
        })->get();
    
    foreach ($journals as $journal) {
        $coa = \App\Models\Coa::find($journal->coa_id);
        $type = $journal->debit > 0 ? 'DEBIT' : 'KREDIT';
        $amount = $journal->debit > 0 ? $journal->debit : $journal->kredit;
        echo "  {$coa->kode_akun} | {$type} | Rp " . number_format($amount, 0, ',', '.') . "\n";
    }
}

?>
