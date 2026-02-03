<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK DATA JABATAN & PEGAWAI ===\n";

$jabatanBtkl = \App\Models\Jabatan::where('kategori', 'btkl')->with('pegawais')->get();
echo "Jabatan BTKL count: " . $jabatanBtkl->count() . "\n";

foreach ($jabatanBtkl as $jabatan) {
    echo "- " . $jabatan->nama . " (ID: " . $jabatan->id . "): " . $jabatan->pegawais->count() . " pegawai\n";
    foreach ($jabatan->pegawais as $pegawai) {
        echo "  - " . $pegawai->nama . "\n";
    }
}

echo "\n=== CEK STRUKTUR TABEL ===\n";
echo "BTKL table columns: " . implode(', ', \Illuminate\Support\Facades\Schema::getColumnListing('btkls')) . "\n";
echo "Pegawai table columns: " . implode(', ', array_slice(\Illuminate\Support\Facades\Schema::getColumnListing('pegawais'), 0, 5)) . "...\n";
echo "Jabatan table columns: " . implode(', ', array_slice(\Illuminate\Support\Facades\Schema::getColumnListing('jabatans'), 0, 5)) . "...\n";
