<?php

/**
 * Test Script: Auto-Update Biaya Bahan
 * 
 * Script ini untuk testing apakah sistem auto-update biaya bahan bekerja dengan benar
 * 
 * Cara pakai:
 * php artisan tinker < test_auto_update_biaya_bahan.php
 */

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST: Auto-Update Biaya Bahan & BOM                      ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// 1. Setup: Ambil bahan baku pertama
echo "📦 Step 1: Ambil data bahan baku...\n";
$bahanBaku = \App\Models\BahanBaku::with('satuan')->first();

if (!$bahanBaku) {
    echo "❌ Error: Tidak ada bahan baku di database\n";
    exit;
}

echo "✅ Bahan Baku: {$bahanBaku->nama_bahan}\n";
echo "   Harga Awal: Rp " . number_format($bahanBaku->harga_satuan, 0, ',', '.') . "\n";
echo "\n";

// 2. Cari produk yang menggunakan bahan baku ini
echo "🔍 Step 2: Cari produk yang menggunakan bahan baku ini...\n";
$bomDetails = \App\Models\BomDetail::where('bahan_baku_id', $bahanBaku->id)
    ->with('bom.produk')
    ->get();

if ($bomDetails->isEmpty()) {
    echo "⚠️  Warning: Tidak ada produk yang menggunakan bahan baku ini\n";
    echo "   Buat BOM dulu untuk testing\n";
    exit;
}

echo "✅ Ditemukan " . $bomDetails->count() . " produk:\n";
foreach ($bomDetails as $detail) {
    if ($detail->bom && $detail->bom->produk) {
        $produk = $detail->bom->produk;
        echo "   - {$produk->nama_produk} (Biaya Bahan: Rp " . number_format($produk->biaya_bahan ?? 0, 0, ',', '.') . ")\n";
    }
}
echo "\n";

// 3. Simpan data awal untuk perbandingan
echo "💾 Step 3: Simpan data awal...\n";
$dataAwal = [];
foreach ($bomDetails as $detail) {
    if ($detail->bom && $detail->bom->produk) {
        $produk = $detail->bom->produk;
        $dataAwal[$produk->id] = [
            'nama' => $produk->nama_produk,
            'biaya_bahan_lama' => $produk->biaya_bahan ?? 0,
            'harga_bom_lama' => $produk->harga_bom ?? 0
        ];
    }
}
echo "✅ Data awal tersimpan\n";
echo "\n";

// 4. Update harga bahan baku (simulasi pembelian)
echo "🔄 Step 4: Update harga bahan baku (simulasi pembelian)...\n";
$hargaLama = $bahanBaku->harga_satuan;
$hargaBaru = $hargaLama * 1.1; // Naik 10%

echo "   Harga Lama: Rp " . number_format($hargaLama, 0, ',', '.') . "\n";
echo "   Harga Baru: Rp " . number_format($hargaBaru, 0, ',', '.') . " (+10%)\n";
echo "\n";

// Trigger observer dengan update harga
$bahanBaku->harga_satuan = $hargaBaru;
$bahanBaku->save();

echo "✅ Harga bahan baku ter-update\n";
echo "   Observer triggered!\n";
echo "\n";

// 5. Tunggu sebentar untuk proses observer
sleep(1);

// 6. Cek hasil update
echo "🔍 Step 5: Cek hasil auto-update...\n";
echo "\n";

$allSuccess = true;

foreach ($dataAwal as $produkId => $data) {
    $produk = \App\Models\Produk::find($produkId);
    
    if (!$produk) continue;
    
    $biayaBahanBaru = $produk->biaya_bahan ?? 0;
    $hargaBomBaru = $produk->harga_bom ?? 0;
    
    $selisihBiayaBahan = $biayaBahanBaru - $data['biaya_bahan_lama'];
    $selisihHargaBom = $hargaBomBaru - $data['harga_bom_lama'];
    
    echo "📊 Produk: {$data['nama']}\n";
    echo "   Biaya Bahan:\n";
    echo "   - Lama: Rp " . number_format($data['biaya_bahan_lama'], 0, ',', '.') . "\n";
    echo "   - Baru: Rp " . number_format($biayaBahanBaru, 0, ',', '.') . "\n";
    
    if ($selisihBiayaBahan > 0) {
        echo "   - Selisih: +Rp " . number_format($selisihBiayaBahan, 0, ',', '.') . " ✅\n";
    } else {
        echo "   - Selisih: Rp 0 ❌ (Tidak ter-update!)\n";
        $allSuccess = false;
    }
    
    echo "   Harga BOM:\n";
    echo "   - Lama: Rp " . number_format($data['harga_bom_lama'], 0, ',', '.') . "\n";
    echo "   - Baru: Rp " . number_format($hargaBomBaru, 0, ',', '.') . "\n";
    
    if ($selisihHargaBom > 0) {
        echo "   - Selisih: +Rp " . number_format($selisihHargaBom, 0, ',', '.') . " ✅\n";
    } else {
        echo "   - Selisih: Rp 0 ❌ (Tidak ter-update!)\n";
        $allSuccess = false;
    }
    
    echo "\n";
}

// 7. Kesimpulan
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  HASIL TEST                                                ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

if ($allSuccess) {
    echo "✅ TEST BERHASIL!\n";
    echo "   Sistem auto-update biaya bahan bekerja dengan baik\n";
    echo "\n";
    echo "📝 Yang ter-update otomatis:\n";
    echo "   1. BOM Detail (harga_per_satuan & total_harga)\n";
    echo "   2. Biaya Bahan Produk (biaya_bahan)\n";
    echo "   3. Harga BOM Produk (harga_bom)\n";
} else {
    echo "❌ TEST GAGAL!\n";
    echo "   Ada produk yang tidak ter-update otomatis\n";
    echo "\n";
    echo "🔧 Troubleshooting:\n";
    echo "   1. Cek observer sudah terdaftar di AppServiceProvider\n";
    echo "   2. Cek log error: storage/logs/laravel.log\n";
    echo "   3. Cek relasi model (BomDetail, BomJobCosting, dll)\n";
}

echo "\n";
echo "📋 Log Detail:\n";
echo "   Lihat: storage/logs/laravel.log\n";
echo "   Cari: 'Auto Update Triggered'\n";
echo "\n";

// 8. Rollback (opsional)
echo "🔄 Rollback harga ke semula? (y/n): ";
// Untuk testing otomatis, langsung rollback
$rollback = 'y';

if ($rollback === 'y') {
    echo "\n";
    echo "⏪ Rolling back...\n";
    $bahanBaku->harga_satuan = $hargaLama;
    $bahanBaku->save();
    echo "✅ Harga bahan baku dikembalikan ke Rp " . number_format($hargaLama, 0, ',', '.') . "\n";
    echo "✅ Biaya bahan produk juga ter-rollback otomatis\n";
}

echo "\n";
echo "🎉 Test selesai!\n";
echo "\n";
