<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking penjualan data...\n\n";

$penjualans = App\Models\Penjualan::get();

echo "Total penjualan: " . $penjualans->count() . "\n\n";

foreach ($penjualans as $penjualan) {
    echo "ID: {$penjualan->id}\n";
    echo "Nomor: {$penjualan->nomor_penjualan}\n";
    echo "Tanggal: {$penjualan->tanggal}\n";
    echo "Total: {$penjualan->total_harga}\n";
    echo "Payment Method: {$penjualan->payment_method}\n";
    echo "---\n";
}

echo "\nChecking jurnal for penjualan:\n";
$journalPenjualan = App\Models\JurnalUmum::where('tipe_referensi', 'penjualan')->count();
echo "Jurnal penjualan: $journalPenjualan\n";

echo "\nBy tipe referensi:\n";
$byType = App\Models\JurnalUmum::groupBy('tipe_referensi')->selectRaw('tipe_referensi, count(*) as count')->get();
foreach ($byType as $item) {
    echo "{$item->tipe_referensi}: {$item->count}\n";
}
?>
