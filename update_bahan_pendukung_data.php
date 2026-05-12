<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Updating bahan pendukung data to match spreadsheet...\n\n";

// Data dari spreadsheet yang diinginkan
$targetData = [
    'Air' => [
        'kategori' => 'air',
        'satuan' => 'L',
        'harga' => 1000,
        'stok' => 50,
        'stok_minimum' => 5,
        'sub_satuan_1' => 'ml',
        'sub_satuan_1_nilai' => 1000,
        'sub_satuan_2' => 'ml',
        'sub_satuan_2_nilai' => 500,
        'sub_satuan_3' => 'ml',
        'sub_satuan_3_nilai' => 250
    ],
    'Minyak Goreng' => [
        'kategori' => 'minyak',
        'satuan' => 'L',
        'harga' => 14000,
        'stok' => 50,
        'stok_minimum' => 1,
        'sub_satuan_1' => 'ml',
        'sub_satuan_1_nilai' => 1000,
        'sub_satuan_2' => 'ml',
        'sub_satuan_2_nilai' => 500,
        'sub_satuan_3' => 'ml',
        'sub_satuan_3_nilai' => 250
    ],
    'Gas 30 Kg' => [
        'kategori' => 'gas',
        'satuan' => 'Tabung',
        'harga' => 240000,
        'stok' => 50,
        'stok_minimum' => 1,
        'sub_satuan_1' => 'kg',
        'sub_satuan_1_nilai' => 30,
        'sub_satuan_2' => 'kg',
        'sub_satuan_2_nilai' => 15,
        'sub_satuan_3' => 'kg',
        'sub_satuan_3_nilai' => 5
    ],
    'Ketumbar Bubuk' => [
        'kategori' => 'bumbu',
        'satuan' => 'Bungkus',
        'harga' => 15000,
        'stok' => 50,
        'stok_minimum' => 0,
        'sub_satuan_1' => 'gram',
        'sub_satuan_1_nilai' => 1000,
        'sub_satuan_2' => 'gram',
        'sub_satuan_2_nilai' => 500,
        'sub_satuan_3' => 'gram',
        'sub_satuan_3_nilai' => 250
    ],
    'Cabe Merah' => [
        'kategori' => 'bumbu',
        'satuan' => 'Kg',
        'harga' => 100000,
        'stok' => 50,
        'stok_minimum' => 0,
        'sub_satuan_1' => 'gram',
        'sub_satuan_1_nilai' => 1000,
        'sub_satuan_2' => 'gram',
        'sub_satuan_2_nilai' => 500,
        'sub_satuan_3' => 'gram',
        'sub_satuan_3_nilai' => 250
    ],
    'Cabe Hijau' => [
        'kategori' => 'bumbu',
        'satuan' => 'Kg',
        'harga' => 120000,
        'stok' => 50,
        'stok_minimum' => 0,
        'sub_satuan_1' => 'gram',
        'sub_satuan_1_nilai' => 1000,
        'sub_satuan_2' => 'gram',
        'sub_satuan_2_nilai' => 500,
        'sub_satuan_3' => 'gram',
        'sub_satuan_3_nilai' => 250
    ],
    'Lada Hitam' => [
        'kategori' => 'bumbu',
        'satuan' => 'Bungkus',
        'harga' => 15000,
        'stok' => 50,
        'stok_minimum' => 0,
        'sub_satuan_1' => 'gram',
        'sub_satuan_1_nilai' => 1000,
        'sub_satuan_2' => 'gram',
        'sub_satuan_2_nilai' => 500,
        'sub_satuan_3' => 'gram',
        'sub_satuan_3_nilai' => 250
    ],
    'Bawang Putih' => [
        'kategori' => 'bumbu',
        'satuan' => 'Kg',
        'harga' => 28000,
        'stok' => 50,
        'stok_minimum' => 0,
        'sub_satuan_1' => 'gram',
        'sub_satuan_1_nilai' => 1000,
        'sub_satuan_2' => 'gram',
        'sub_satuan_2_nilai' => 500,
        'sub_satuan_3' => 'gram',
        'sub_satuan_3_nilai' => 250
    ],
    'Tepung Maizena' => [
        'kategori' => 'bumbu',
        'satuan' => 'Bungkus',
        'harga' => 9000,
        'stok' => 50,
        'stok_minimum' => 0,
        'sub_satuan_1' => 'gram',
        'sub_satuan_1_nilai' => 1000,
        'sub_satuan_2' => 'gram',
        'sub_satuan_2_nilai' => 500,
        'sub_satuan_3' => 'gram',
        'sub_satuan_3_nilai' => 250
    ],
    'Merica Bubuk' => [
        'kategori' => 'bumbu',
        'satuan' => 'Bungkus',
        'harga' => 2000,
        'stok' => 50,
        'stok_minimum' => 0,
        'sub_satuan_1' => 'gram',
        'sub_satuan_1_nilai' => 1000,
        'sub_satuan_2' => 'gram',
        'sub_satuan_2_nilai' => 500,
        'sub_satuan_3' => 'gram',
        'sub_satuan_3_nilai' => 250
    ],
    'Listrik' => [
        'kategori' => 'listrik',
        'satuan' => 'Watt',
        'harga' => 3000,
        'stok' => 50,
        'stok_minimum' => 0,
        'sub_satuan_1' => 'watt',
        'sub_satuan_1_nilai' => 1000,
        'sub_satuan_2' => 'watt',
        'sub_satuan_2_nilai' => 500,
        'sub_satuan_3' => 'watt',
        'sub_satuan_3_nilai' => 250
    ],
    'Bawang Merah' => [
        'kategori' => 'bumbu',
        'satuan' => 'KG',
        'harga' => 25000,
        'stok' => 50,
        'stok_minimum' => 0,
        'sub_satuan_1' => 'gram',
        'sub_satuan_1_nilai' => 1000,
        'sub_satuan_2' => 'gram',
        'sub_satuan_2_nilai' => 500,
        'sub_satuan_3' => 'gram',
        'sub_satuan_3_nilai' => 250
    ],
    'Kemasan' => [
        'kategori' => 'lainnya',
        'satuan' => 'PCS',
        'harga' => 2000,
        'stok' => 50,
        'stok_minimum' => 0,
        'sub_satuan_1' => 'pcs',
        'sub_satuan_1_nilai' => 100,
        'sub_satuan_2' => 'pcs',
        'sub_satuan_2_nilai' => 50,
        'sub_satuan_3' => 'pcs',
        'sub_satuan_3_nilai' => 25
    ]
];

try {
    // Get default COA
    $defaultCoa = \DB::table('coas')->where('kode_akun', '122119')->first();
    $coaId = $defaultCoa ? $defaultCoa->kode_akun : '122119';
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($targetData as $namaBahan => $data) {
        try {
            // Find existing record
            $existing = \DB::table('bahan_pendukungs')
                ->where('nama_bahan', $namaBahan)
                ->first();
            
            if (!$existing) {
                echo "Warning: $namaBahan not found, skipping...\n";
                continue;
            }
            
            // Get satuan IDs
            $satuanId = getSatuanId($data['satuan']);
            $subSatuan1Id = getSatuanId($data['sub_satuan_1']);
            $subSatuan2Id = getSatuanId($data['sub_satuan_2']);
            $subSatuan3Id = getSatuanId($data['sub_satuan_3']);
            
            // Get kategori ID
            $kategoriId = getKategoriId($data['kategori']);
            
            // Update the record
            \DB::table('bahan_pendukungs')
                ->where('id', $existing->id)
                ->update([
                    'harga_satuan' => $data['harga'],
                    'stok' => $data['stok'],
                    'stok_minimum' => $data['stok_minimum'],
                    'kategori' => $data['kategori'],
                    'kategori_id' => $kategoriId,
                    'satuan_id' => $satuanId,
                    'sub_satuan_1_id' => $subSatuan1Id,
                    'sub_satuan_1_konversi' => 1.0000,
                    'sub_satuan_1_nilai' => $data['sub_satuan_1_nilai'],
                    'sub_satuan_2_id' => $subSatuan2Id,
                    'sub_satuan_2_konversi' => 1.0000,
                    'sub_satuan_2_nilai' => $data['sub_satuan_2_nilai'],
                    'sub_satuan_3_id' => $subSatuan3Id,
                    'sub_satuan_3_konversi' => 1.0000,
                    'sub_satuan_3_nilai' => $data['sub_satuan_3_nilai'],
                    'coa_pembelian_id' => $coaId,
                    'coa_persediaan_id' => $coaId,
                    'coa_hpp_id' => $coaId,
                    'updated_at' => now()
                ]);
            
            $successCount++;
            echo "✓ Updated: $namaBahan\n";
            
        } catch (\Exception $e) {
            $errorCount++;
            echo "✗ Error updating $namaBahan: " . $e->getMessage() . "\n";
        }
    }
    
    // Remove duplicates
    echo "\nRemoving duplicates...\n";
    removeDuplicates();
    
    echo "\n=== UPDATE COMPLETE ===\n";
    echo "Success: $successCount records\n";
    echo "Errors: $errorCount records\n\n";
    
    // Show final data
    showFinalData();
    
} catch (\Exception $e) {
    echo "Update failed: " . $e->getMessage() . "\n";
}

function getSatuanId($satuanName) {
    $satuan = \DB::table('satuans')
        ->where('nama', 'like', '%' . $satuanName . '%')
        ->first();
    
    if ($satuan) {
        return $satuan->id;
    }
    
    // Try to find by case-insensitive match
    $satuan = \DB::table('satuans')
        ->whereRaw('LOWER(nama) = ?', [strtolower($satuanName)])
        ->first();
    
    return $satuan ? $satuan->id : 2; // Default to ID 2 if not found
}

function getKategoriId($kategoriName) {
    $kategori = \DB::table('kategori_bahan_pendukung')
        ->whereRaw('LOWER(nama) = ?', [strtolower($kategoriName)])
        ->first();
    
    if ($kategori) {
        return $kategori->id;
    }
    
    // Use default category
    return \DB::table('kategori_bahan_pendukung')->value('id') ?? 1;
}

function removeDuplicates() {
    // Remove duplicate Minyak Goreng (keep the one with minyak category)
    $duplicateMinyak = \DB::table('bahan_pendukungs')
        ->where('nama_bahan', 'Minyak Goreng')
        ->where('kategori', 'lainnya')
        ->first();
    
    if ($duplicateMinyak) {
        \DB::table('bahan_pendukungs')->where('id', $duplicateMinyak->id)->delete();
        echo "✓ Removed duplicate Minyak Goreng (lainnya)\n";
    }
    
    // Remove duplicate Tepung Maizena (keep the one with bumbu category)
    $duplicateTepung = \DB::table('bahan_pendukungs')
        ->where('nama_bahan', 'Tepung Maizena')
        ->where('kategori', 'lainnya')
        ->first();
    
    if ($duplicateTepung) {
        \DB::table('bahan_pendukungs')->where('id', $duplicateTepung->id)->delete();
        echo "✓ Removed duplicate Tepung Maizena (lainnya)\n";
    }
    
    // Remove duplicate Bawang Merah (keep the one with lower ID)
    $duplicateBawang = \DB::table('bahan_pendukungs')
        ->where('nama_bahan', 'Bawang Merah')
        ->orderBy('id', 'desc')
        ->skip(1)
        ->first();
    
    if ($duplicateBawang) {
        \DB::table('bahan_pendukungs')->where('id', $duplicateBawang->id)->delete();
        echo "✓ Removed duplicate Bawang Merah\n";
    }
}

function showFinalData() {
    echo "Final data in bahan_pendukungs:\n";
    echo str_repeat("=", 120) . "\n";
    $allData = \DB::table('bahan_pendukungs')
        ->orderBy('nama_bahan')
        ->get(['kode_bahan', 'nama_bahan', 'harga_satuan', 'stok', 'stok_minimum', 'kategori']);
    
    foreach ($allData as $item) {
        echo sprintf("%-12s %-25s %-15s %-8s %-8s %-15s\n", 
            $item->kode_bahan, 
            $item->nama_bahan, 
            "RP" . number_format($item->harga_satuan, 0, ',', '.'), 
            $item->stok, 
            $item->stok_minimum,
            $item->kategori
        );
    }
    echo str_repeat("=", 120) . "\n";
    echo "Total records: " . $allData->count() . "\n";
}
