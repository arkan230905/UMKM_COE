<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting to fix pegawai-jabatan relationships...\n";

// Get all pegawais for user_id = 1
$pegawais = App\Models\Pegawai::where('user_id', 1)->get();

foreach ($pegawais as $pegawai) {
    echo "Processing pegawai: " . $pegawai->nama . " (Current jabatan: " . $pegawai->jabatan . ")\n";
    
    // Find matching jabatan by nama
    $jabatan = App\Models\Jabatan::where('user_id', 1)
        ->where('nama', $pegawai->jabatan)
        ->first();
    
    if ($jabatan) {
        $pegawai->jabatan_id = $jabatan->id;
        $pegawai->save();
        echo "  ✓ Updated jabatan_id to: " . $jabatan->id . "\n";
    } else {
        echo "  ✗ No matching jabatan found for: " . $pegawai->jabatan . "\n";
    }
}

echo "\nFixing kategori_id in jabatans...\n";

// Get all jabatans for user_id = 1
$jabatans = App\Models\Jabatan::where('user_id', 1)->get();

foreach ($jabatans as $jabatan) {
    echo "Processing jabatan: " . $jabatan->nama . " (Current kategori: " . $jabatan->kategori . ")\n";
    
    // Find matching kategori from pegawais
    $pegawai = App\Models\Pegawai::where('user_id', 1)
        ->where('jabatan', $jabatan->nama)
        ->whereNotNull('kategori_id')
        ->first();
    
    if ($pegawai && $pegawai->kategori_id) {
        $jabatan->kategori_id = $pegawai->kategori_id;
        $jabatan->save();
        echo "  ✓ Updated kategori_id to: " . $pegawai->kategori_id . "\n";
    } else {
        echo "  ✗ No matching kategori_id found for: " . $jabatan->nama . "\n";
    }
}

echo "\nDone! Relationships have been fixed.\n";
