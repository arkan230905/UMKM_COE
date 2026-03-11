<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking kategori field structure:\n";
$columnType = \DB::select("SHOW COLUMNS FROM bahan_pendukungs LIKE 'kategori'");
foreach ($columnType as $column) {
    echo "Field: {$column->Field}, Type: {$column->Type}\n";
}

echo "\nExisting kategori values:\n";
$kategoriValues = \DB::table('bahan_pendukungs')->distinct()->pluck('kategori');
foreach ($kategoriValues as $value) {
    echo "- '$value' (length: " . strlen($value) . ")\n";
}

echo "\nTrying to insert Kemasan with shorter category name...\n";

try {
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
    
    // Insert with very short category name
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
        'kategori' => 'lain', // Very short category name
        'kategori_id' => 8,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "Kemasan data inserted successfully with kode: $kodeBahan\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
