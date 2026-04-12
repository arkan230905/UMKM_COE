<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking penjualan details...\n\n";

$penjualans = App\Models\Penjualan::with('details')->get();

foreach ($penjualans as $penjualan) {
    echo "ID: {$penjualan->id}\n";
    echo "Nomor: {$penjualan->nomor_penjualan}\n";
    echo "Tanggal: {$penjualan->tanggal}\n";
    echo "Total Harga: {$penjualan->total_harga}\n";
    echo "Payment Method: {$penjualan->payment_method}\n";
    echo "Sumber Dana: {$penjualan->sumber_dana}\n";
    
    echo "Details:\n";
    foreach ($penjualan->details as $detail) {
        echo "  - Produk: {$detail->produk_id} | Qty: {$detail->jumlah} | Harga: {$detail->harga_satuan} | Subtotal: {$detail->subtotal}\n";
    }
    
    // Calculate total from details
    $totalFromDetails = $penjualan->details->sum('subtotal');
    echo "Calculated Total from Details: $totalFromDetails\n";
    echo "---\n";
}
?>
