<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pembelian;

$pembelians = Pembelian::select('id', 'nomor_pembelian', 'nomor_faktur', 'vendor_id')
    ->with('vendor:id,nama_vendor')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();

echo "Data Pembelian Terbaru:\n\n";

foreach ($pembelians as $pembelian) {
    echo "ID: {$pembelian->id}\n";
    echo "Nomor Pembelian: {$pembelian->nomor_pembelian}\n";
    echo "Nomor Faktur: " . ($pembelian->nomor_faktur ?: 'KOSONG') . "\n";
    echo "Vendor: " . ($pembelian->vendor->nama_vendor ?? 'N/A') . "\n";
    echo "---\n";
}