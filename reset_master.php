<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Reset master data Ayam Kampung
$ayam = \App\Models\BahanBaku::find(2);
$ayam->stok = 0;
$ayam->harga_satuan = 0;
$ayam->save();

echo "Master data Ayam Kampung direset:\n";
echo "- Stok: 0 Ekor\n";
echo "- Harga: Rp 0\n";

// Verify movements
echo "\nMovements yang ada:\n";
$movements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 2)
    ->orderBy('tanggal', 'asc')
    ->get();

foreach ($movements as $m) {
    echo "- {$m->tanggal}: {$m->direction} {$m->qty} {$m->satuan} = Rp " . number_format($m->total_cost, 0) . "\n";
}
