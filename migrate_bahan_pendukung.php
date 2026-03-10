<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Migrating data from bahan_pendukung to bahan_pendukungs...\n\n";

try {
    // Get data from source table
    $sourceData = \DB::table('bahan_pendukung')->get();
    
    echo "Found {$sourceData->count()} records to migrate\n\n";
    
    // Get default values for required fields
    $defaultSatuan = \DB::table('satuans')->first();
    $defaultKategori = \DB::table('kategori_bahan_pendukung')->first();
    $defaultCoa = \DB::table('coas')->where('kode_akun', '122119')->first(); // Default COA
    
    if (!$defaultSatuan) {
        echo "Warning: No default satuan found, using satuan_id = 1\n";
        $defaultSatuanId = 1;
    } else {
        $defaultSatuanId = $defaultSatuan->id;
    }
    
    if (!$defaultKategori) {
        echo "Warning: No default kategori found, using kategori_id = 1\n";
        $defaultKategoriId = 1;
        $defaultKategoriName = 'lainnya';
    } else {
        $defaultKategoriId = $defaultKategori->id;
        $defaultKategoriName = $defaultKategori->nama ?? 'lainnya';
    }
    
    if (!$defaultCoa) {
        echo "Warning: No default COA found, using coa_id = 122119\n";
        $defaultCoaId = '122119';
    } else {
        $defaultCoaId = $defaultCoa->kode_akun;
    }
    
    echo "Using defaults:\n";
    echo "- Satuan ID: $defaultSatuanId\n";
    echo "- Kategori ID: $defaultKategoriId ($defaultKategoriName)\n";
    echo "- COA ID: $defaultCoaId\n\n";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($sourceData as $index => $item) {
        try {
            // Generate kode bahan
            $lastKode = \DB::table('bahan_pendukungs')->orderBy('id', 'desc')->value('kode_bahan');
            if ($lastKode) {
                $nextNumber = ((int) substr($lastKode, 4)) + 1;
            } else {
                $nextNumber = 1;
            }
            $kodeBahan = 'BPD-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            
            // Map jenis to kategori
            $kategoriName = strtolower($item->jenis);
            $kategoriId = $defaultKategoriId;
            
            // Try to find matching category
            $matchingKategori = \DB::table('kategori_bahan_pendukung')
                ->where('nama', 'like', '%' . $item->jenis . '%')
                ->first();
            
            if ($matchingKategori) {
                $kategoriId = $matchingKategori->id;
                $kategoriName = $matchingKategori->nama;
            }
            
            // Find matching satuan
            $satuanId = $defaultSatuanId;
            $matchingSatuan = \DB::table('satuans')
                ->where('nama', 'like', '%' . $item->satuan . '%')
                ->first();
            
            if ($matchingSatuan) {
                $satuanId = $matchingSatuan->id;
            }
            
            // Insert into bahan_pendukungs
            \DB::table('bahan_pendukungs')->insert([
                'kode_bahan' => $kodeBahan,
                'nama_bahan' => $item->nama,
                'deskripsi' => "Bahan pendukung jenis {$item->jenis}",
                'satuan_id' => $satuanId,
                'sub_satuan_1_id' => $satuanId,
                'sub_satuan_1_konversi' => 1.0000,
                'sub_satuan_1_nilai' => 100.0000,
                'sub_satuan_2_id' => $satuanId,
                'sub_satuan_2_konversi' => 1.0000,
                'sub_satuan_2_nilai' => 10.0000,
                'sub_satuan_3_id' => $satuanId,
                'sub_satuan_3_konversi' => 1.0000,
                'sub_satuan_3_nilai' => 33.3000,
                'coa_pembelian_id' => $defaultCoaId,
                'coa_persediaan_id' => $defaultCoaId,
                'coa_hpp_id' => $defaultCoaId,
                'harga_satuan' => $item->harga,
                'stok' => $item->stok,
                'stok_minimum' => $item->stok_minimum,
                'kategori' => $kategoriName,
                'kategori_id' => $kategoriId,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $successCount++;
            echo ($index + 1) . ". {$item->nama} -> {$kodeBahan} ✓\n";
            
        } catch (\Exception $e) {
            $errorCount++;
            echo "Error migrating {$item->nama}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== MIGRATION COMPLETE ===\n";
    echo "Success: $successCount records\n";
    echo "Errors: $errorCount records\n\n";
    
    // Show final count
    $finalCount = \DB::table('bahan_pendukungs')->count();
    echo "Total records in bahan_pendukungs: $finalCount\n\n";
    
    // Show sample of migrated data
    echo "Sample migrated data:\n";
    echo str_repeat("=", 80) . "\n";
    $sampleData = \DB::table('bahan_pendukungs')
        ->orderBy('id', 'desc')
        ->limit(5)
        ->get(['kode_bahan', 'nama_bahan', 'harga_satuan', 'stok', 'kategori']);
    
    foreach ($sampleData as $item) {
        echo sprintf("%-12s %-25s %-10s %-8s %-15s\n", 
            $item->kode_bahan, 
            $item->nama_bahan, 
            "RP" . number_format($item->harga_satuan, 0, ',', '.'), 
            $item->stok, 
            $item->kategori
        );
    }
    echo str_repeat("=", 80) . "\n";
    
} catch (\Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
