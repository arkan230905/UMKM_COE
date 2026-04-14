<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Purchase Data and Conversion ===\n";

// 1. Check actual pembelian data
echo "1. Actual Pembelian Data:\n";
$pembelian = \App\Models\Pembelian::with(['details.bahanBaku'])->find(2);
if ($pembelian) {
    echo "Pembelian #{$pembelian->id} - {$pembelian->nomor_pembelian}\n";
    echo "Tanggal: {$pembelian->tanggal}\n";
    
    foreach ($pembelian->details as $detail) {
        if ($detail->bahan_baku_id == 1) {
            echo "\nAyam Potong Detail:\n";
            echo "- Jumlah: {$detail->jumlah}\n";
            echo "- Satuan ID: {$detail->satuan_id}\n";
            echo "- Harga Satuan: Rp " . number_format($detail->harga_satuan, 0, ',', '.') . "\n";
            echo "- Total: Rp " . number_format($detail->total, 0, ',', '.') . "\n";
        }
    }
}

// 2. Check satuan data
echo "\n2. Available Satuan:\n";
$satuans = \App\Models\Satuan::all();
foreach ($satuans as $satuan) {
    echo "- ID: {$satuan->id}, Nama: {$satuan->nama}\n";
}

// 3. Check bahan baku master data
echo "\n3. Bahan Baku Master Data:\n";
$ayamPotong = \App\Models\BahanBaku::find(1);
if ($ayamPotong) {
    echo "Nama: {$ayamPotong->nama}\n";
    echo "Satuan Utama ID: {$ayamPotong->satuan_utama_id}\n";
    
    if ($ayamPotong->sub_satuan_1_id) {
        echo "Sub Satuan 1 ID: {$ayamPotong->sub_satuan_1_id}\n";
        echo "Sub Satuan 1 Nilai: {$ayamPotong->sub_satuan_1_nilai}\n";
    }
    
    if ($ayamPotong->sub_satuan_2_id) {
        echo "Sub Satuan 2 ID: {$ayamPotong->sub_satuan_2_id}\n";
        echo "Sub Satuan 2 Nilai: {$ayamPotong->sub_satuan_2_nilai}\n";
    }
}

// 4. Check pembelian_detail raw data
echo "\n4. Pembelian Detail Raw Data:\n";
$detailRaw = \DB::table('pembelian_detail')
    ->where('pembelian_id', 2)
    ->where('bahan_baku_id', 1)
    ->first();

if ($detailRaw) {
    echo "Raw data from database:\n";
    foreach ((array)$detailRaw as $key => $value) {
        echo "- {$key}: {$value}\n";
    }
}

// 5. Check current stock entries
echo "\n5. Current Stock Entries:\n";
echo "Kartu Stok:\n";
$kartuStok = \DB::table('kartu_stok')
    ->where('item_type', 'bahan_baku')
    ->where('item_id', 1)
    ->get();
foreach ($kartuStok as $entry) {
    echo "- ID: {$entry->id}, Type: {$entry->ref_type}, Qty: {$entry->qty_masuk}, Ref: {$entry->ref_id}\n";
}

echo "\nStock Movements:\n";
$stockMovements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->get();
foreach ($stockMovements as $movement) {
    echo "- ID: {$movement->id}, Type: {$movement->ref_type}, Qty: {$movement->qty}, Direction: {$movement->direction}, Ref: {$movement->ref_id}\n";
}

echo "\n=== Analysis ===\n";
echo "User correction: Purchase is 50 ekor, with conversion 50 ekor = 40 kg\n";
echo "This means:\n";
echo "- Purchase unit: ekor\n";
echo "- Main unit: kg\n";
echo "- Conversion: 50 ekor = 40 kg (1 ekor = 0.8 kg)\n";
echo "- Sub unit: potong (1 kg = 3 potong)\n";
echo "\nExpected result:\n";
echo "- Purchase: 50 ekor\n";
echo "- Main unit stock: 40 kg\n";
echo "- Sub unit stock: 120 potong (40 kg × 3 potong/kg)\n";