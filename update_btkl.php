<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== UPDATE EXISTING BTKL ===\n";

$btkl = \App\Models\Btkl::where('kode_proses', 'PROC-001')->first();
if ($btkl) {
    $jabatan = $btkl->jabatan;
    $jumlahPegawai = $jabatan->pegawais()->count();
    $tarifPerJam = $jabatan->tarif ?? 0;
    $tarifBtkl = $tarifPerJam * $jumlahPegawai;
    
    $btkl->tarif_per_jam = $tarifBtkl;
    $btkl->save();
    
    echo 'BTKL updated: ' . $btkl->kode_proses . ' - New Tarif: Rp ' . number_format($tarifBtkl) . "\n";
    echo 'Calculation: Rp ' . number_format($tarifPerJam) . ' × ' . $jumlahPegawai . ' pegawai = Rp ' . number_format($tarifBtkl) . "\n";
} else {
    echo "BTKL PROC-001 not found\n";
}

echo "\n=== VERIFICATION ===\n";
$updatedBtkl = \App\Models\Btkl::with('jabatan.pegawais')->where('kode_proses', 'PROC-001')->first();
if ($updatedBtkl) {
    echo "Final verification:\n";
    echo "- Kode: " . $updatedBtkl->kode_proses . "\n";
    echo "- Jabatan: " . $updatedBtkl->jabatan->nama . "\n";
    echo "- Tarif Jabatan: Rp " . number_format($updatedBtkl->jabatan->tarif) . "\n";
    echo "- Jumlah Pegawai: " . $updatedBtkl->jabatan->pegawais->count() . "\n";
    echo "- Tarif BTKL (Stored): Rp " . number_format($updatedBtkl->tarif_per_jam) . "\n";
}
