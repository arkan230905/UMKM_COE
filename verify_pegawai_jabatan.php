<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Verifying pegawai-jabatan relationships for user_id = 1...\n\n";

echo "=== JABATANS ===\n";
$jabatans = App\Models\Jabatan::where('user_id', 1)->get();

foreach ($jabatans as $jabatan) {
    echo "Jabatan: " . $jabatan->nama . " (ID: " . $jabatan->id . ")\n";
    echo "  Kategori: " . $jabatan->kategori . " (ID: " . $jabatan->kategori_id . ")\n";
    echo "  Tarif per jam: Rp " . number_format($jabatan->tarif_per_jam, 0, ',', '.') . "\n";
    
    // Get pegawais with proper filtering
    $pegawais = App\Models\Pegawai::where('user_id', 1)
        ->where('jabatan_id', $jabatan->id)
        ->get();
    
    echo "  Jumlah pegawai: " . $pegawais->count() . "\n";
    
    foreach ($pegawais as $pegawai) {
        echo "    - " . $pegawai->nama . " (ID: " . $pegawai->id . ")\n";
    }
    
    $expectedTarif = $jabatan->tarif_per_jam * $pegawais->count();
    echo "  Expected BTKL tarif: Rp " . number_format($expectedTarif, 0, ',', '.') . "\n";
    echo "\n";
}

echo "=== PEGAWAIS ===\n";
$pegawais = App\Models\Pegawai::where('user_id', 1)->get();

foreach ($pegawais as $pegawai) {
    echo "Pegawai: " . $pegawai->nama . " (ID: " . $pegawai->id . ")\n";
    echo "  Jabatan: " . $pegawai->jabatan . " (ID: " . $pegawai->jabatan_id . ")\n";
    echo "  Kategori: " . $pegawai->kategori . " (ID: " . $pegawai->kategori_id . ")\n";
    echo "  Tarif per jam: Rp " . number_format($pegawai->tarif_per_jam, 0, ',', '.') . "\n";
    echo "\n";
}

echo "=== TESTING RELATIONSHIP ===\n";
$jabatan = App\Models\Jabatan::where('user_id', 1)->first();
if ($jabatan) {
    echo "Testing jabatan: " . $jabatan->nama . "\n";
    
    // Test direct relationship
    $pegawaiCount = App\Models\Pegawai::where('user_id', 1)
        ->where('jabatan_id', $jabatan->id)
        ->count();
    
    echo "Direct count: " . $pegawaiCount . "\n";
    
    // Test relationship method
    $pegawaiCountRelation = $jabatan->pegawais()->count();
    echo "Relationship count: " . $pegawaiCountRelation . "\n";
    
    // Test with user filtering
    $pegawaiCountFiltered = $jabatan->pegawais()->where('user_id', 1)->count();
    echo "Relationship with user filter: " . $pegawaiCountFiltered . "\n";
}

echo "\nDone!\n";
