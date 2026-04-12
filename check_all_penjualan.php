<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking ALL penjualan data in database...\n\n";

$penjualans = App\Models\Penjualan::with('details')->orderBy('id')->get();

echo "Total penjualan records: " . $penjualans->count() . "\n\n";

foreach ($penjualans as $penjualan) {
    echo "ID: {$penjualan->id}\n";
    echo "Nomor: {$penjualan->nomor_penjualan}\n";
    echo "Tanggal: {$penjualan->tanggal}\n";
    echo "Payment Method: {$penjualan->payment_method}\n";
    echo "Sumber Dana: {$penjualan->sumber_dana}\n";
    echo "Total Harga (field): " . ($penjualan->total_harga ?? 'NULL') . "\n";
    
    echo "Details:\n";
    $totalFromDetails = 0;
    foreach ($penjualan->details as $detail) {
        echo "  - Produk ID: {$detail->produk_id} | Qty: {$detail->jumlah} | Harga: {$detail->harga_satuan} | Subtotal: {$detail->subtotal}\n";
        $totalFromDetails += $detail->subtotal;
    }
    echo "Calculated Total from Details: $totalFromDetails\n";
    
    // Check if journal exists
    $journalCount = App\Models\JurnalUmum::where('tipe_referensi', 'penjualan')
        ->where('referensi', $penjualan->nomor_penjualan)
        ->count();
    echo "Journal entries: $journalCount\n";
    
    echo "---\n";
}

echo "\nChecking journals by penjualan nomor:\n";
$journals = App\Models\JurnalUmum::where('tipe_referensi', 'penjualan')
    ->orderBy('referensi')
    ->get();
    
foreach ($journals as $journal) {
    echo "Journal: {$journal->referensi} | COA: {$journal->coa_id} | D: {$journal->debit} | K: {$journal->kredit}\n";
}
?>
