<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST TARIF OTOMATIS ===\n";

// Test jabatan data
$jabatanBtkl = \App\Models\Jabatan::where('kategori', 'btkl')->with('pegawais')->get();

foreach ($jabatanBtkl as $jabatan) {
    echo "\nJabatan: " . $jabatan->nama . "\n";
    echo "- Tarif Jabatan: Rp " . number_format($jabatan->tarif) . "\n";
    echo "- Jumlah Pegawai: " . $jabatan->pegawais->count() . "\n";
    echo "- Tarif BTKL (Otomatis): Rp " . number_format($jabatan->getTarifBtklAttribute()) . "\n";
    echo "- Kalkulasi: Rp " . number_format($jabatan->tarif) . " × " . $jabatan->pegawais->count() . " = Rp " . number_format($jabatan->getTarifBtklAttribute()) . "\n";
    
    // Test create BTKL with automatic calculation
    $testBtkl = \App\Models\Btkl::where('jabatan_id', $jabatan->id)->first();
    if ($testBtkl) {
        echo "- Existing BTKL: " . $testBtkl->kode_proses . " - Tarif: Rp " . number_format($testBtkl->tarif_per_jam) . "\n";
    }
}

echo "\n=== TEST CONTROLLER STORE ===\n";

// Test controller calculation
$jabatan = \App\Models\Jabatan::where('nama', 'Penggorengan')->first();
if ($jabatan) {
    $jumlahPegawai = $jabatan->pegawais()->count();
    $tarifPerJam = $jabatan->tarif ?? 0;
    $tarifBtkl = $tarifPerJam * $jumlahPegawai;
    
    echo "Penggorengan:\n";
    echo "- Tarif Jabatan: Rp " . number_format($tarifPerJam) . "\n";
    echo "- Jumlah Pegawai: " . $jumlahPegawai . "\n";
    echo "- Tarif BTKL: Rp " . number_format($tarifBtkl) . "\n";
}
