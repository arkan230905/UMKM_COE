<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking transactions...\n\n";

echo "Pembelian:\n";
$pembelian = App\Models\Pembelian::count();
echo "Total: $pembelian\n";

echo "\nPelunasan Utang:\n";
$pelunasan = App\Models\PelunasanUtang::count();
echo "Total: $pelunasan\n";

echo "\nPenggajian:\n";
$penggajian = App\Models\Penggajian::count();
echo "Total: $penggajian\n";

echo "\nPembayaran Beban:\n";
$pembayaranBeban = App\Models\PembayaranBeban::count();
echo "Total: $pembayaranBeban\n";

echo "\nPenjualan:\n";
$penjualan = App\Models\Penjualan::count();
echo "Total: $penjualan\n";
?>
