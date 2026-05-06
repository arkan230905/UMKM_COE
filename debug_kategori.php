<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging kategori relasi...\n\n";

// Check kategori data
echo "=== KATEGORI DATA ===\n";
$kategoris = App\Models\KategoriPegawai::all();
foreach ($kategoris as $kategori) {
    echo "Kategori ID: " . $kategori->id . ", Nama: " . $kategori->nama . ", User ID: " . $kategori->user_id . "\n";
}

echo "\n=== JABATAN DATA ===\n";
$jabatans = App\Models\Jabatan::where('user_id', 1)->get();
foreach ($jabatans as $jabatan) {
    echo "Jabatan ID: " . $jabatan->id . ", Nama: " . $jabatan->nama . ", Kategori ID: " . $jabatan->kategori_id . ", Kategori Field: " . $jabatan->kategori . "\n";
    
    // Test the relation
    $kategoriRelation = $jabatan->kategori;
    echo "  Relation result: " . ($kategoriRelation ? $kategoriRelation->nama : 'NULL') . "\n";
    
    // Test direct query
    $kategoriDirect = App\Models\KategoriPegawai::find($jabatan->kategori_id);
    echo "  Direct query result: " . ($kategoriDirect ? $kategoriDirect->nama : 'NULL') . "\n";
    
    echo "\n";
}

echo "=== PEGAWAI DATA ===\n";
$pegawais = App\Models\Pegawai::where('user_id', 1)->get();
foreach ($pegawais as $pegawai) {
    echo "Pegawai ID: " . $pegawai->id . ", Nama: " . $pegawai->nama . ", Jabatan ID: " . $pegawai->jabatan_id . ", Kategori ID: " . $pegawai->kategori_id . "\n";
    
    // Test the relation
    $kategoriRelation = $pegawai->kategoriPegawai;
    echo "  Relation result: " . ($kategoriRelation ? $kategoriRelation->nama : 'NULL') . "\n";
    
    echo "\n";
}
