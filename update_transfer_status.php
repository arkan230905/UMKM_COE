<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use App\Models\Pembelian;

echo "Updating transfer pembelian status to LUNAS...\n";

// Update semua pembelian dengan payment_method = transfer dan status = belum_lunas
$updated = Pembelian::where('payment_method', 'transfer')
    ->where('status', 'belum_lunas')
    ->update([
        'status' => 'lunas',
        'terbayar' => DB::raw('total_harga'),
        'sisa_pembayaran' => 0
    ]);

echo "Updated {$updated} pembelian records\n";

// Tampilkan hasil
$pembelians = Pembelian::where('payment_method', 'transfer')->get();
echo "\nTransfer Pembelian Status Summary:\n";
echo "=====================================\n";

foreach ($pembelians as $p) {
    echo "ID: {$p->id} | Status: {$p->status} | Terbayar: Rp " . number_format($p->terbayar, 0, ',', '.') . " | Sisa: Rp " . number_format($p->sisa_pembayaran, 0, ',', '.') . "\n";
}

echo "\nUpdate completed successfully!\n";
