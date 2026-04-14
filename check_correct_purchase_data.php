<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Correct Purchase Data ===\n";

// 1. Check pembelian_details table structure
echo "1. Pembelian Details Table Structure:\n";
$columns = \DB::select('DESCRIBE pembelian_details');
foreach ($columns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}

// 2. Check actual purchase data
echo "\n2. Actual Purchase Data:\n";
$detailRaw = \DB::table('pembelian_details')
    ->where('pembelian_id', 2)
    ->where('bahan_baku_id', 1)
    ->first();

if ($detailRaw) {
    echo "Raw data from pembelian_details:\n";
    foreach ((array)$detailRaw as $key => $value) {
        echo "- {$key}: {$value}\n";
    }
}

// 3. Check satuan information
echo "\n3. Satuan Information:\n";
if ($detailRaw && $detailRaw->satuan_id) {
    $satuan = \App\Models\Satuan::find($detailRaw->satuan_id);
    if ($satuan) {
        echo "Purchase satuan: {$satuan->nama} (ID: {$satuan->id})\n";
    }
}

// 4. Check bahan baku master data
echo "\n4. Bahan Baku Master Data:\n";
$ayamPotong = \App\Models\BahanBaku::find(1);
if ($ayamPotong) {
    echo "Nama: {$ayamPotong->nama}\n";
    echo "Satuan Utama ID: {$ayamPotong->satuan_utama_id}\n";
    
    // Get satuan names
    $satuanUtama = \App\Models\Satuan::find($ayamPotong->satuan_utama_id);
    echo "Satuan Utama: " . ($satuanUtama->nama ?? 'Unknown') . "\n";
    
    if ($ayamPotong->sub_satuan_1_id) {
        $subSatuan1 = \App\Models\Satuan::find($ayamPotong->sub_satuan_1_id);
        echo "Sub Satuan 1: " . ($subSatuan1->nama ?? 'Unknown') . " (Nilai: {$ayamPotong->sub_satuan_1_nilai})\n";
    }
    
    if ($ayamPotong->sub_satuan_2_id) {
        $subSatuan2 = \App\Models\Satuan::find($ayamPotong->sub_satuan_2_id);
        echo "Sub Satuan 2: " . ($subSatuan2->nama ?? 'Unknown') . " (Nilai: {$ayamPotong->sub_satuan_2_nilai})\n";
    }
}

// 5. Check if there's conversion table
echo "\n5. Checking Conversion Table:\n";
try {
    $conversions = \DB::table('pembelian_detail_konversi')->get();
    echo "Pembelian detail konversi records: " . $conversions->count() . "\n";
    foreach ($conversions as $conv) {
        echo "- ID: {$conv->id}, Detail ID: {$conv->pembelian_detail_id}\n";
        foreach ((array)$conv as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
    }
} catch (Exception $e) {
    echo "No conversion table or error: " . $e->getMessage() . "\n";
}

echo "\n=== Analysis ===\n";
echo "Based on user input:\n";
echo "- Purchase: 50 ekor\n";
echo "- Conversion: 50 ekor = 40 kg\n";
echo "- Main unit should be: 40 kg\n";
echo "- Sub unit should be: 120 potong (40 kg × 3 potong/kg)\n";

if ($detailRaw) {
    echo "\nCurrent system shows:\n";
    echo "- Purchase quantity: {$detailRaw->jumlah}\n";
    echo "- Purchase satuan ID: {$detailRaw->satuan_id}\n";
    
    if ($detailRaw->satuan_id == 7) { // Ekor
        echo "✅ Purchase is in 'ekor' (correct)\n";
        echo "❌ But system needs to convert 50 ekor → 40 kg for stock\n";
    }
}