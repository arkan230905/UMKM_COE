<?php

// Script untuk memeriksa data sub satuan di database

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\BahanBaku;
use App\Models\BahanPendukung;

echo "🔍 CHECKING SUB SATUAN DATA\n";
echo "===========================\n\n";

// Check Bahan Baku
echo "1. BAHAN BAKU DATA:\n";
echo "-------------------\n";

$bahanBakus = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])
    ->orderBy('nama_bahan')
    ->get();

if ($bahanBakus->count() > 0) {
    echo "✅ Found {$bahanBakus->count()} bahan baku records\n\n";
    
    foreach ($bahanBakus->take(5) as $bahan) {
        echo "📦 {$bahan->nama_bahan}\n";
        echo "   Harga: Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . "\n";
        echo "   Satuan Utama: " . ($bahan->satuan->nama ?? 'N/A') . "\n";
        
        $subSatuanCount = 0;
        if ($bahan->subSatuan1) {
            echo "   Sub Satuan 1: {$bahan->subSatuan1->nama} ({$bahan->sub_satuan_1_konversi} = {$bahan->sub_satuan_1_nilai})\n";
            $subSatuanCount++;
        }
        if ($bahan->subSatuan2) {
            echo "   Sub Satuan 2: {$bahan->subSatuan2->nama} ({$bahan->sub_satuan_2_konversi} = {$bahan->sub_satuan_2_nilai})\n";
            $subSatuanCount++;
        }
        if ($bahan->subSatuan3) {
            echo "   Sub Satuan 3: {$bahan->subSatuan3->nama} ({$bahan->sub_satuan_3_konversi} = {$bahan->sub_satuan_3_nilai})\n";
            $subSatuanCount++;
        }
        
        if ($subSatuanCount == 0) {
            echo "   ⚠️  Tidak ada sub satuan\n";
        } else {
            echo "   ✅ {$subSatuanCount} sub satuan tersedia\n";
        }
        echo "\n";
    }
} else {
    echo "❌ No bahan baku records found\n\n";
}

// Check Bahan Pendukung
echo "2. BAHAN PENDUKUNG DATA:\n";
echo "------------------------\n";

$bahanPendukungs = BahanPendukung::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])
    ->orderBy('nama_bahan')
    ->get();

if ($bahanPendukungs->count() > 0) {
    echo "✅ Found {$bahanPendukungs->count()} bahan pendukung records\n\n";
    
    foreach ($bahanPendukungs->take(5) as $bahan) {
        echo "🧪 {$bahan->nama_bahan}\n";
        echo "   Harga: Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . "\n";
        echo "   Satuan Utama: " . ($bahan->satuan->nama ?? 'N/A') . "\n";
        
        $subSatuanCount = 0;
        if ($bahan->subSatuan1) {
            echo "   Sub Satuan 1: {$bahan->subSatuan1->nama} ({$bahan->sub_satuan_1_konversi} = {$bahan->sub_satuan_1_nilai})\n";
            $subSatuanCount++;
        }
        if ($bahan->subSatuan2) {
            echo "   Sub Satuan 2: {$bahan->subSatuan2->nama} ({$bahan->sub_satuan_2_konversi} = {$bahan->sub_satuan_2_nilai})\n";
            $subSatuanCount++;
        }
        if ($bahan->subSatuan3) {
            echo "   Sub Satuan 3: {$bahan->subSatuan3->nama} ({$bahan->sub_satuan_3_konversi} = {$bahan->sub_satuan_3_nilai})\n";
            $subSatuanCount++;
        }
        
        if ($subSatuanCount == 0) {
            echo "   ⚠️  Tidak ada sub satuan\n";
        } else {
            echo "   ✅ {$subSatuanCount} sub satuan tersedia\n";
        }
        echo "\n";
    }
} else {
    echo "❌ No bahan pendukung records found\n\n";
}

// Generate sample JSON for testing
echo "3. SAMPLE JSON DATA:\n";
echo "--------------------\n";

$sampleBahan = $bahanBakus->first();
if ($sampleBahan) {
    $subSatuanData = [];
    if ($sampleBahan->subSatuan1) {
        $subSatuanData[] = [
            'id' => $sampleBahan->sub_satuan_1_id,
            'nama' => $sampleBahan->subSatuan1->nama,
            'konversi' => $sampleBahan->sub_satuan_1_konversi,
            'nilai' => $sampleBahan->sub_satuan_1_nilai
        ];
    }
    if ($sampleBahan->subSatuan2) {
        $subSatuanData[] = [
            'id' => $sampleBahan->sub_satuan_2_id,
            'nama' => $sampleBahan->subSatuan2->nama,
            'konversi' => $sampleBahan->sub_satuan_2_konversi,
            'nilai' => $sampleBahan->sub_satuan_2_nilai
        ];
    }
    if ($sampleBahan->subSatuan3) {
        $subSatuanData[] = [
            'id' => $sampleBahan->sub_satuan_3_id,
            'nama' => $sampleBahan->subSatuan3->nama,
            'konversi' => $sampleBahan->sub_satuan_3_konversi,
            'nilai' => $sampleBahan->sub_satuan_3_nilai
        ];
    }
    
    echo "Sample for {$sampleBahan->nama_bahan}:\n";
    echo json_encode($subSatuanData, JSON_PRETTY_PRINT) . "\n\n";
}

echo "📋 SUMMARY:\n";
echo "===========\n";
echo "Bahan Baku: {$bahanBakus->count()} records\n";
echo "Bahan Pendukung: {$bahanPendukungs->count()} records\n";

$bahanWithSubSatuan = $bahanBakus->filter(function($bahan) {
    return $bahan->subSatuan1 || $bahan->subSatuan2 || $bahan->subSatuan3;
})->count();

echo "Bahan Baku with Sub Satuan: {$bahanWithSubSatuan} records\n";

if ($bahanWithSubSatuan > 0) {
    echo "✅ Sub satuan data is available in database\n";
} else {
    echo "❌ No sub satuan data found - this is the problem!\n";
    echo "   Please add sub satuan data to bahan baku/pendukung\n";
}

?>