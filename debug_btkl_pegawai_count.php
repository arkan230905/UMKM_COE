<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "DEBUG BTKL PEGAWAI COUNT\n";
echo "========================\n";

// Check jabatan BTKL
$jabatanBtkl = \App\Models\Jabatan::where('kategori', 'btkl')
    ->with(['pegawais' => function($query) {
        $query->whereNotNull('jabatan_id');
    }])
    ->orderBy('nama')
    ->get();

echo "Total jabatan BTKL: " . $jabatanBtkl->count() . "\n\n";

foreach ($jabatanBtkl as $jabatan) {
    echo "Jabatan: {$jabatan->nama} (ID: {$jabatan->id})\n";
    echo "  Kategori: {$jabatan->kategori}\n";
    echo "  Tarif: " . ($jabatan->tarif ?? 0) . "\n";
    echo "  Jumlah pegawai (loaded): " . $jabatan->pegawais->count() . "\n";
    
    // Check pegawai count directly from database
    $directCount = \App\Models\Pegawai::where('jabatan_id', $jabatan->id)->count();
    echo "  Jumlah pegawai (direct query): {$directCount}\n";
    
    // Show pegawai details
    if ($jabatan->pegawais->count() > 0) {
        echo "  Daftar pegawai:\n";
        foreach ($jabatan->pegawais as $pegawai) {
            echo "    - {$pegawai->nama_pegawai} (ID: {$pegawai->id})\n";
        }
    }
    
    echo "\n";
}

// Check all pegawai to see their jabatan
echo "ALL PEGAWAI WITH JABATAN:\n";
echo "==========================\n";
$allPegawais = \App\Models\Pegawai::with('jabatanRelasi')->whereNotNull('jabatan_id')->get();

foreach ($allPegawais as $pegawai) {
    echo "Pegawai: {$pegawai->nama} (ID: {$pegawai->id})\n";
    echo "  Jabatan ID: {$pegawai->jabatan_id}\n";
    echo "  Jabatan: " . ($pegawai->jabatanRelasi ? $pegawai->jabatanRelasi->nama : 'N/A') . "\n";
    echo "  Jabatan Kategori: " . ($pegawai->jabatanRelasi ? $pegawai->jabatanRelasi->kategori : 'N/A') . "\n";
    echo "\n";
}

echo "Debug completed.\n";
