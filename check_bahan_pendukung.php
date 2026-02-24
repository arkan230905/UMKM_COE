<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Cek Struktur Bahan Pendukung ===\n";

// Check if table exists
if (!\Illuminate\Support\Facades\Schema::hasTable('bahan_pendukungs')) {
    echo "Tabel bahan_pendukungs tidak ada!\n";
    exit;
}

// Get columns
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('bahan_pendukungs');
echo "Kolom di tabel bahan_pendukungs:\n";
foreach ($columns as $col) {
    echo "- $col\n";
}

// Get sample data
echo "\n=== Sample Data Bahan Pendukung ===\n";
$bahanPendukungs = \App\Models\BahanPendukung::limit(3)->get();
foreach ($bahanPendukungs as $bp) {
    echo "ID: {$bp->id}, Nama: {$bp->nama_bahan}\n";
    echo "Satuan ID: " . ($bp->satuan_id ?? 'null') . "\n";
    echo "Sub Satuan 1 ID: " . ($bp->sub_satuan_1_id ?? 'null') . "\n";
    echo "Sub Satuan 2 ID: " . ($bp->sub_satuan_2_id ?? 'null') . "\n";
    echo "Sub Satuan 3 ID: " . ($bp->sub_satuan_3_id ?? 'null') . "\n";
    echo "Stok: " . ($bp->stok ?? 0) . "\n";
    echo "Harga: " . ($bp->harga_satuan ?? 0) . "\n";
    echo "---\n";
}

// Check stock movements for bahan pendukung
echo "\n=== Stock Movements untuk Bahan Pendukung ===\n";
$movements = \App\Models\StockMovement::where('item_type', 'bahan_pendukung')->limit(5)->get();
if ($movements->count() > 0) {
    foreach ($movements as $m) {
        echo "Tanggal: {$m->tanggal}, Item ID: {$m->item_id}, Type: {$m->ref_type}, Qty: {$m->qty}\n";
    }
} else {
    echo "Tidak ada stock movements untuk bahan pendukung\n";
}
