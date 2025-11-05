<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pegawais = \App\Models\Pegawai::take(10)->get(['id', 'nomor_induk_pegawai', 'nama']);

echo "=== DATA PEGAWAI ===\n\n";
foreach ($pegawais as $p) {
    echo "ID: {$p->id}\n";
    echo "NIP: {$p->nomor_induk_pegawai}\n";
    echo "Nama: " . ($p->nama ?? 'NULL/KOSONG') . "\n";
    echo "---\n";
}

// Cek presensi dengan pegawai
echo "\n=== DATA PRESENSI (5 terakhir) ===\n\n";
$presensis = \App\Models\Presensi::with('pegawai')->take(5)->get();
foreach ($presensis as $presensi) {
    echo "Presensi ID: {$presensi->id}\n";
    echo "Pegawai ID: {$presensi->pegawai_id}\n";
    if ($presensi->pegawai) {
        echo "Pegawai NIP: {$presensi->pegawai->nomor_induk_pegawai}\n";
        echo "Pegawai Nama: " . ($presensi->pegawai->nama ?? 'NULL/KOSONG') . "\n";
    } else {
        echo "Pegawai: TIDAK DITEMUKAN\n";
    }
    echo "---\n";
}
