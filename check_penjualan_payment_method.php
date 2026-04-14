<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking penjualan payment methods and journals...\n\n";

$penjualans = App\Models\Penjualan::with('details')->get();

foreach ($penjualans as $penjualan) {
    echo "Penjualan ID: {$penjualan->id}\n";
    echo "Nomor: {$penjualan->nomor_penjualan}\n";
    echo "Payment Method: {$penjualan->payment_method}\n";
    echo "Sumber Dana: '{$penjualan->sumber_dana}'\n";
    
    // Check journals for this penjualan
    $journals = App\Models\JurnalUmum::where('tipe_referensi', 'penjualan')
        ->where('referensi', $penjualan->nomor_penjualan)
        ->with('coa')
        ->get();
    
    echo "Journals (" . $journals->count() . "):\n";
    foreach ($journals as $journal) {
        echo "  - COA: {$journal->coa->kode_akun} - {$journal->coa->nama_akun}\n";
        echo "    Debit: {$journal->debit} | Kredit: {$journal->kredit}\n";
        echo "    Keterangan: {$journal->keterangan}\n";
    }
    
    // Check if this is transfer and should go to bank
    if ($penjualan->payment_method === 'transfer') {
        echo "  PAYMENT METHOD: TRANSAKSI TRANSFER - Harusnya ke Bank!\n";
        
        // Find bank COA
        $bankCoa = \App\Models\Coa::where('kode_akun', 'like', '1%')->get();
        echo "  Available Bank COAs:\n";
        foreach ($bankCoa as $coa) {
            echo "    - {$coa->kode_akun} - {$coa->nama_akun}\n";
        }
    }
    
    echo "---\n";
}
?>
