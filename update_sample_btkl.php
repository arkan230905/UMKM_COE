<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== UPDATE BTKL WITH REALISTIC CAPACITY ===\n";

$btkl = \App\Models\Btkl::where('kode_proses', 'PROC-001')->first();
if ($btkl) {
    // Update to more realistic capacity
    $btkl->kapasitas_per_jam = 50; // More realistic for manual process
    $btkl->save();
    
    echo "Updated BTKL:\n";
    echo "- Kode: " . $btkl->kode_proses . "\n";
    echo "- Jabatan: " . $btkl->jabatan->nama . "\n";
    echo "- Tarif BTKL: Rp " . number_format($btkl->tarif_per_jam) . "\n";
    echo "- Kapasitas/Jam: " . number_format($btkl->kapasitas_per_jam) . " pcs\n";
    echo "- Biaya Per Produk: " . $btkl->biaya_per_produk_formatted . "\n";
    echo "- Kalkulasi: Rp " . number_format($btkl->tarif_per_jam) . " ÷ " . number_format($btkl->kapasitas_per_jam) . " = " . number_format($btkl->biaya_per_produk, 2) . "\n";
} else {
    echo "BTKL not found\n";
}

echo "\n=== CREATE ADDITIONAL SAMPLE BTKL ===\n";

// Create additional sample BTKL with different jabatan
$jabatan = \App\Models\Jabatan::where('nama', 'Pengemasan')->first();
if ($jabatan) {
    $jumlahPegawai = $jabatan->pegawais()->count();
    $tarifPerJam = $jabatan->tarif ?? 0;
    $tarifBtkl = $tarifPerJam * $jumlahPegawai;
    
    $newBtkl = \App\Models\Btkl::create([
        'kode_proses' => 'PROC-002',
        'jabatan_id' => $jabatan->id,
        'tarif_per_jam' => $tarifBtkl,
        'satuan' => 'Jam',
        'kapasitas_per_jam' => 80, // Different capacity
        'deskripsi_proses' => 'Proses pengemasan produk',
        'is_active' => true
    ]);
    
    echo "Created new BTKL:\n";
    echo "- Kode: " . $newBtkl->kode_proses . "\n";
    echo "- Jabatan: " . $newBtkl->jabatan->nama . "\n";
    echo "- Tarif BTKL: Rp " . number_format($newBtkl->tarif_per_jam) . "\n";
    echo "- Kapasitas/Jam: " . number_format($newBtkl->kapasitas_per_jam) . " pcs\n";
    echo "- Biaya Per Produk: " . $newBtkl->biaya_per_produk_formatted . "\n";
    echo "- Kalkulasi: Rp " . number_format($newBtkl->tarif_per_jam) . " ÷ " . number_format($newBtkl->kapasitas_per_jam) . " = " . number_format($newBtkl->biaya_per_produk, 2) . "\n";
}

echo "\n=== VERIFICATION ===\n";
$allBtkls = \App\Models\Btkl::with('jabatan.pegawais')->get();
echo "Total BTKL records: " . $allBtkls->count() . "\n";

foreach ($allBtkls as $btkl) {
    echo "- " . $btkl->kode_proses . ": " . $btkl->jabatan->nama . " - " . $btkl->biaya_per_produk_formatted . "\n";
}
