<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Final fix for jabatans kategori_id...\n\n";

// Get the original BTKL and BTKTL kategoris
$originalBTKL = App\Models\KategoriPegawai::where('nama', 'BTKL')->where('user_id', 1)->first();
$originalBTKTL = App\Models\KategoriPegawai::where('nama', 'BTKTL')->where('user_id', 1)->first();

echo "Found BTKL kategori: " . ($originalBTKL ? "ID " . $originalBTKL->id : "NULL") . "\n";
echo "Found BTKTL kategori: " . ($originalBTKTL ? "ID " . $originalBTKTL->id : "NULL") . "\n\n";

// Update all jabatans based on their kategori field
$jabatans = App\Models\Jabatan::all();

foreach ($jabatans as $jabatan) {
    echo "Processing jabatan: " . $jabatan->nama . " (user_id: " . $jabatan->user_id . ", kategori: " . $jabatan->kategori . ")\n";
    
    if ($jabatan->kategori === 'btkl' && $originalBTKL) {
        $jabatan->kategori_id = $originalBTKL->id;
        $jabatan->save();
        echo "  ✓ Set kategori_id to BTKL (ID: " . $originalBTKL->id . ")\n";
    } elseif ($jabatan->kategori === 'btktl' && $originalBTKTL) {
        $jabatan->kategori_id = $originalBTKTL->id;
        $jabatan->save();
        echo "  ✓ Set kategori_id to BTKTL (ID: " . $originalBTKTL->id . ")\n";
    } else {
        echo "  ⚠️  Cannot process - kategori: " . $jabatan->kategori . "\n";
    }
    
    // Also update pegawais for this jabatan
    $pegawais = App\Models\Pegawai::where('jabatan_id', $jabatan->id)->get();
    foreach ($pegawais as $pegawai) {
        echo "  - Updating pegawai: " . $pegawai->nama . "\n";
        $pegawai->kategori_id = $jabatan->kategori_id;
        $pegawai->save();
    }
    
    echo "\n";
}

echo "Done! All jabatans and pegawais have been updated.\n";

echo "\n=== FINAL VERIFICATION ===\n";
$jabatans = App\Models\Jabatan::where('user_id', 1)->get();

foreach ($jabatans as $jabatan) {
    $kategoriName = $jabatan->kategoriPegawai ? $jabatan->kategoriPegawai->nama : 'NULL';
    echo "Jabatan: " . $jabatan->nama . " - Kategori: " . $kategoriName . " (ID: " . $jabatan->kategori_id . ")\n";
    
    $pegawaiCount = App\Models\Pegawai::where('jabatan_id', $jabatan->id)->count();
    echo "  Pegawai count: " . $pegawaiCount . "\n";
}
