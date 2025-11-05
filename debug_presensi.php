<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG PRESENSI & PEGAWAI ===\n\n";

// Ambil data presensi dengan relasi pegawai
$presensis = \App\Models\Presensi::with('pegawai')->take(5)->get();

echo "Total presensi: " . $presensis->count() . "\n\n";

foreach ($presensis as $presensi) {
    echo "--- Presensi ID: {$presensi->id} ---\n";
    echo "Pegawai ID: {$presensi->pegawai_id}\n";
    echo "Tanggal: {$presensi->tgl_presensi}\n";
    echo "Status: {$presensi->status}\n";
    
    if ($presensi->pegawai) {
        echo "\nData Pegawai:\n";
        echo "  - ID: {$presensi->pegawai->id}\n";
        echo "  - NIP: {$presensi->pegawai->nomor_induk_pegawai}\n";
        echo "  - Nama: '" . ($presensi->pegawai->nama ?? 'NULL') . "'\n";
        echo "  - Nama kosong? " . (empty($presensi->pegawai->nama) ? 'YA' : 'TIDAK') . "\n";
        echo "  - Nama null? " . (is_null($presensi->pegawai->nama) ? 'YA' : 'TIDAK') . "\n";
        
        // Test operator
        echo "\nTest Operator:\n";
        echo "  - Dengan ??: " . ($presensi->pegawai->nama ?? $presensi->pegawai->nomor_induk_pegawai) . "\n";
        echo "  - Dengan ?:: " . ($presensi->pegawai->nama ?: $presensi->pegawai->nomor_induk_pegawai) . "\n";
    } else {
        echo "\nPegawai: RELASI TIDAK DITEMUKAN!\n";
    }
    echo "\n";
}

// Cek struktur tabel pegawais
echo "\n=== STRUKTUR KOLOM PEGAWAIS ===\n";
$columns = \DB::select("SHOW COLUMNS FROM pegawais");
foreach ($columns as $col) {
    if ($col->Field === 'nama' || $col->Field === 'nomor_induk_pegawai') {
        echo "Kolom: {$col->Field}\n";
        echo "  Type: {$col->Type}\n";
        echo "  Null: {$col->Null}\n";
        echo "  Default: " . ($col->Default ?? 'NULL') . "\n\n";
    }
}
