<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CHECKING ACTUAL PENJUALAN VIEW DATA\n";
echo "===================================\n\n";

// Use the same query as the controller
$query = App\Models\Penjualan::with(['produk','details']);
$penjualans = $query->with(['produk','details','returs'])->orderBy('tanggal','desc')->get();

echo "Data yang akan ditampilkan di halaman transaksi/penjualan:\n";
echo "=====================================================\n";

foreach ($penjualans as $key => $penjualan) {
    echo "Index: {$key}\n";
    echo "ID: {$penjualan->id}\n";
    echo "Nomor Penjualan (DB): '{$penjualan->nomor_penjualan}'\n";
    echo "Tanggal: {$penjualan->tanggal}\n";
    echo "Payment Method: '{$penjualan->payment_method}'\n";
    echo "Sumber Dana: '{$penjualan->sumber_dana}'\n";
    
    echo "Details Count: " . $penjualan->details->count() . "\n";
    foreach ($penjualan->details as $detail) {
        echo "  - Produk: {$detail->produk_id} | Qty: {$detail->jumlah} | Harga: {$detail->harga_satuan} | Subtotal: {$detail->subtotal}\n";
    }
    
    $totalFromDetails = $penjualan->details->sum('subtotal');
    echo "Total from Details: {$totalFromDetails}\n";
    
    // Check if journal exists
    $journalCount = App\Models\JurnalUmum::where('tipe_referensi', 'penjualan')
        ->where('referensi', $penjualan->nomor_penjualan)
        ->count();
    echo "Journal entries: {$journalCount}\n";
    
    echo "---\n";
}

echo "\nCOMPARISON WITH USER REPORT:\n";
echo "===========================\n";
echo "User melihat di halaman:\n";
echo "- SJ-260412-001\n";
echo "- SJ-260412-002  \n";
echo "- SJ-260412-003\n\n";

echo "Database sebenarnya:\n";
foreach ($penjualans as $penjualan) {
    echo "- {$penjualan->nomor_penjualan}\n";
}

echo "\nISSUE: User melihat nomor yang BERBEDA dengan database!\n";
echo "Ini berarti ada masalah dengan view atau ada data dummy.\n";

?>
