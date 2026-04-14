<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Purchase Conversion System ===\n";

// 1. Check actual pembelian data
echo "1. Actual Pembelian Data:\n";
$pembelian = \App\Models\Pembelian::with(['details.bahanBaku', 'details.satuan'])->find(2);
if ($pembelian) {
    echo "Pembelian #{$pembelian->id} - {$pembelian->nomor_pembelian}\n";
    echo "Tanggal: {$pembelian->tanggal}\n";
    
    foreach ($pembelian->details as $detail) {
        if ($detail->bahan_baku_id == 1) {
            echo "\nAyam Potong Detail:\n";
            echo "- Jumlah: {$detail->jumlah}\n";
            echo "- Satuan ID: {$detail->satuan_id}\n";
            echo "- Satuan Name: " . ($detail->satuan->nama ?? 'Unknown') . "\n";
            echo "- Harga Satuan: Rp " . number_format($detail->harga_satuan, 0, ',', '.') . "\n";
            echo "- Total: Rp " . number_format($detail->total, 0, ',', '.') . "\n";
            
            // Check if there's conversion data
            if (isset($detail->konversi_ke_satuan_utama)) {
                echo "- Konversi ke satuan utama: {$detail->konversi_ke_satuan_utama}\n";
            }
        }
    }
}

// 2. Check bahan baku master data
echo "\n2. Bahan Baku Master Data:\n";
$ayamPotong = \App\Models\BahanBaku::with(['satuanUtama', 'satuanSub1', 'satuanSub2'])->find(1);
if ($ayamPotong) {
    echo "Nama: {$ayamPotong->nama}\n";
    echo "Satuan Utama: " . ($ayamPotong->satuanUtama->nama ?? 'Unknown') . " (ID: {$ayamPotong->satuan_utama_id})\n";
    
    if ($ayamPotong->sub_satuan_1_id) {
        echo "Sub Satuan 1: " . ($ayamPotong->satuanSub1->nama ?? 'Unknown') . " (ID: {$ayamPotong->sub_satuan_1_id})\n";
        echo "Sub Satuan 1 Nilai: {$ayamPotong->sub_satuan_1_nilai}\n";
    }
    
    if ($ayamPotong->sub_satuan_2_id) {
        echo "Sub Satuan 2: " . ($ayamPotong->satuanSub2->nama ?? 'Unknown') . " (ID: {$ayamPotong->sub_satuan_2_id})\n";
        echo "Sub Satuan 2 Nilai: {$ayamPotong->sub_satuan_2_nilai}\n";
    }
}

// 3. Check all available satuan
echo "\n3. Available Satuan:\n";
$satuans = \App\Models\Satuan::all();
foreach ($satuans as $satuan) {
    echo "- ID: {$satuan->id}, Nama: {$satuan->nama}\n";
}

// 4. Check pembelian_detail table structure
echo "\n4. Pembelian Detail Table Structure:\n";
$columns = \DB::select('DESCRIBE pembelian_detail');
foreach ($columns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}

// 5. Check if there's conversion data in pembelian_detail
echo "\n5. Pembelian Detail Raw Data:\n";
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

echo "\n=== Analysis ===\n";
echo "User says: 50 ekor = 40 kg\n";
echo "This means: 1 ekor = 0.8 kg\n";
echo "If purchase is 50 ekor, it should be recorded as 40 kg in main unit\n";
echo "Then 40 kg × 3 potong/kg = 120 potong in sub-unit\n";