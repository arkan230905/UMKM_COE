<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CEK PEGAWAI SPESIFIK ===\n\n";

$nips = ['EMP0002', 'EMP0003'];

foreach ($nips as $nip) {
    $pegawai = \App\Models\Pegawai::where('nomor_induk_pegawai', $nip)->first();
    
    if ($pegawai) {
        echo "NIP: {$nip}\n";
        echo "ID: {$pegawai->id}\n";
        echo "Nama: '{$pegawai->nama}'\n";
        echo "Nama is null: " . (is_null($pegawai->nama) ? 'YES' : 'NO') . "\n";
        echo "Nama is empty: " . (empty($pegawai->nama) ? 'YES' : 'NO') . "\n";
        echo "Nama length: " . strlen($pegawai->nama ?? '') . "\n";
        
        // Cek raw dari database
        $raw = \DB::table('pegawais')->where('nomor_induk_pegawai', $nip)->first();
        echo "Raw DB nama: '{$raw->nama}'\n";
        echo "Raw DB nama is null: " . (is_null($raw->nama) ? 'YES' : 'NO') . "\n";
        
        echo "---\n\n";
    } else {
        echo "Pegawai dengan NIP {$nip} TIDAK DITEMUKAN!\n\n";
    }
}

// Cek presensi terbaru
echo "=== PRESENSI TERBARU ===\n\n";
$presensis = \App\Models\Presensi::with('pegawai')->orderBy('id', 'desc')->take(3)->get();

foreach ($presensis as $p) {
    echo "Presensi ID: {$p->id}\n";
    echo "Pegawai ID: {$p->pegawai_id}\n";
    if ($p->pegawai) {
        echo "Pegawai NIP: {$p->pegawai->nomor_induk_pegawai}\n";
        echo "Pegawai Nama: '{$p->pegawai->nama}'\n";
        echo "Nama empty?: " . (empty($p->pegawai->nama) ? 'YES (akan tampil NIP)' : 'NO (akan tampil NAMA)') . "\n";
    }
    echo "---\n";
}
