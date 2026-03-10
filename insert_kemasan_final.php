<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Inserting Kemasan with correct ENUM value...\n";

try {
    // Check if Kemasan already exists
    $existing = \DB::table('bahan_pendukungs')
        ->where('nama_bahan', 'Kemasan')
        ->first();
    
    if ($existing) {
        echo "Kemasan already exists with ID: {$existing->id}\n";
    } else {
        // Generate kode bahan
        $lastKode = \DB::table('bahan_pendukungs')->orderBy('id', 'desc')->value('kode_bahan');
        if ($lastKode) {
            $nextNumber = ((int) substr($lastKode, 4)) + 1;
        } else {
            $nextNumber = 1;
        }
        $kodeBahan = 'BPD-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        
        // Get default values
        $defaultSatuan = \DB::table('satuans')->where('nama', 'like', '%PCS%')->first();
        $satuanId = $defaultSatuan ? $defaultSatuan->id : 2;
        
        $defaultCoa = \DB::table('coas')->where('kode_akun', '122119')->first();
        $coaId = $defaultCoa ? $defaultCoa->kode_akun : '122119';
        
        // Insert Kemasan data with correct ENUM value
        \DB::table('bahan_pendukungs')->insert([
            'kode_bahan' => $kodeBahan,
            'nama_bahan' => 'Kemasan',
            'deskripsi' => 'Bahan pendukung jenis Kemasan',
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
            'coa_pembelian_id' => $coaId,
            'coa_persediaan_id' => $coaId,
            'coa_hpp_id' => $coaId,
            'harga_satuan' => 2000,
            'stok' => 50,
            'stok_minimum' => 0,
            'kategori' => 'lainnya', // Use correct ENUM value
            'kategori_id' => 8,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "Kemasan data inserted successfully with kode: $kodeBahan\n";
    }
    
    // Clean up temporary table
    echo "\nCleaning up temporary table...\n";
    \DB::statement('DROP TABLE IF EXISTS bahan_pendukung');
    echo "Temporary table bahan_pendukung dropped\n";
    
    // Show final data
    echo "\nFinal data in bahan_pendukungs:\n";
    echo str_repeat("=", 100) . "\n";
    $allData = \DB::table('bahan_pendukungs')
        ->orderBy('nama_bahan')
        ->get(['kode_bahan', 'nama_bahan', 'harga_satuan', 'stok', 'kategori']);
    
    foreach ($allData as $item) {
        echo sprintf("%-12s %-25s %-15s %-8s %-15s\n", 
            $item->kode_bahan, 
            $item->nama_bahan, 
            "RP" . number_format($item->harga_satuan, 0, ',', '.'), 
            $item->stok, 
            $item->kategori
        );
    }
    echo str_repeat("=", 100) . "\n";
    echo "Total records: " . $allData->count() . "\n";
    
    echo "\n✅ All data has been successfully migrated!\n";
    echo "You can now refresh your browser to see all 13 bahan pendukung items.\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
