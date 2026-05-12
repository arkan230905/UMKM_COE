<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating BTKL kategori if not exists...\n";

// Check if BTKL kategori already exists for user_id = 1
$kategori = App\Models\KategoriPegawai::where('nama', 'BTKL')
    ->first();

if (!$kategori) {
    $kategori = new App\Models\KategoriPegawai();
    $kategori->nama = 'BTKL';
    $kategori->deskripsi = 'Kategori untuk Biaya Tenaga Kerja Langsung';
    $kategori->save();
    echo "✓ Created BTKL kategori with ID: " . $kategori->id . "\n";
} else {
    echo "✓ BTKL kategori already exists with ID: " . $kategori->id . "\n";
}

echo "\nUpdating jabatans with kategori_id...\n";

// Update all jabatans with kategori 'btkl' to use the BTKL kategori_id
$jabatans = App\Models\Jabatan::where('user_id', 1)
    ->where('kategori', 'btkl')
    ->get();

foreach ($jabatans as $jabatan) {
    $jabatan->kategori_id = $kategori->id;
    $jabatan->save();
    echo "✓ Updated jabatan '" . $jabatan->nama . "' with kategori_id: " . $kategori->id . "\n";
}

echo "\nUpdating pegawais with kategori_id...\n";

// Update all pegawais with kategori 'btkl' to use the BTKL kategori_id
$pegawais = App\Models\Pegawai::where('user_id', 1)
    ->where('kategori', 'btkl')
    ->get();

foreach ($pegawais as $pegawai) {
    $pegawai->kategori_id = $kategori->id;
    $pegawai->save();
    echo "✓ Updated pegawai '" . $pegawai->nama . "' with kategori_id: " . $kategori->id . "\n";
}

echo "\nDone! All BTKL relationships have been fixed.\n";

echo "\nVerification:\n";
echo "Jabatans with BTKL:\n";
$jabatansBTKL = App\Models\Jabatan::where('user_id', 1)
    ->where('kategori', 'btkl')
    ->with(['pegawais' => function($query) {
        $query->where('user_id', 1);
    }])
    ->get();

foreach ($jabatansBTKL as $jabatan) {
    echo "- " . $jabatan->nama . " (ID: " . $jabatan->id . ") - Pegawai count: " . $jabatan->pegawais->count() . "\n";
}
