<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Updating stok from pembelian...\n";

// Reset stok ke 0
Illuminate\Support\Facades\DB::table('bahan_bakus')->update(['stok' => 0]);
echo "Reset all stok to 0\n";

// Update stok dari pembelian details
$details = Illuminate\Support\Facades\DB::table('pembelian_details')->get();
foreach ($details as $d) {
    Illuminate\Support\Facades\DB::table('bahan_bakus')
        ->where('id', $d->bahan_baku_id)
        ->increment('stok', $d->jumlah);
}
echo "Updated stok from pembelian details\n";

// Tampilkan hasil stok
$bahanBakus = Illuminate\Support\Facades\DB::table('bahan_bakus')
    ->select('nama_bahan', 'stok')
    ->get();

echo "\nCurrent stok:\n";
foreach ($bahanBakus as $b) {
    echo $b->nama_bahan . ': ' . $b->stok . "\n";
}

echo "\nDone!\n";
