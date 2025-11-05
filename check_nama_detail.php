<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DETAIL CHECK NAMA PEGAWAI ===\n\n";

$pegawais = \App\Models\Pegawai::whereIn('nomor_induk_pegawai', ['EMP0002', 'EMP0003'])->get();

foreach ($pegawais as $p) {
    echo "NIP: {$p->nomor_induk_pegawai}\n";
    echo "Nama raw: '" . $p->nama . "'\n";
    echo "Nama length: " . strlen($p->nama) . "\n";
    echo "Nama is null: " . (is_null($p->nama) ? 'YES' : 'NO') . "\n";
    echo "Nama empty: " . (empty($p->nama) ? 'YES' : 'NO') . "\n";
    echo "Nama trim: '" . trim($p->nama) . "'\n";
    echo "Nama trim length: " . strlen(trim($p->nama)) . "\n";
    
    // Test kondisi
    if (!empty($p->nama)) {
        echo "RESULT: Akan tampil NAMA\n";
    } else {
        echo "RESULT: Akan tampil NIP\n";
    }
    
    // Cek bytes
    echo "Bytes: ";
    for ($i = 0; $i < min(strlen($p->nama), 20); $i++) {
        echo ord($p->nama[$i]) . " ";
    }
    echo "\n";
    echo "---\n\n";
}

// Cek via presensi
echo "\n=== VIA PRESENSI ===\n";
$presensis = \App\Models\Presensi::with('pegawai')->whereIn('pegawai_id', [2, 3])->get();

foreach ($presensis as $presensi) {
    echo "Presensi ID: {$presensi->id}\n";
    echo "Pegawai ID: {$presensi->pegawai_id}\n";
    if ($presensi->pegawai) {
        echo "Nama: '{$presensi->pegawai->nama}'\n";
        echo "Empty check: " . (empty($presensi->pegawai->nama) ? 'EMPTY (akan tampil NIP)' : 'NOT EMPTY (akan tampil NAMA)') . "\n";
    }
    echo "---\n";
}
