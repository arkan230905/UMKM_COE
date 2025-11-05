<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Simulate request
$request = \Illuminate\Http\Request::create('/master-data/presensi', 'GET');
$app->instance('request', $request);

echo "=== SIMULASI OUTPUT BLADE ===\n\n";

$presensis = \App\Models\Presensi::with('pegawai')->take(3)->get();

foreach ($presensis as $presensi) {
    echo "Presensi ID: {$presensi->id}\n";
    echo "Pegawai ID: {$presensi->pegawai_id}\n";
    
    if ($presensi->pegawai) {
        // Simulasi output blade
        $nama = $presensi->pegawai->nama ?? $presensi->pegawai->nomor_induk_pegawai;
        $nip = $presensi->pegawai->nomor_induk_pegawai;
        
        echo "Yang akan tampil di kolom PEGAWAI:\n";
        echo "  Baris 1 (nama): {$nama}\n";
        echo "  Baris 2 (nip): {$nip}\n";
    } else {
        echo "PEGAWAI TIDAK DITEMUKAN!\n";
    }
    echo "---\n";
}

// Test raw query
echo "\n=== RAW QUERY TEST ===\n";
$result = \DB::table('presensis')
    ->join('pegawais', 'presensis.pegawai_id', '=', 'pegawais.id')
    ->select('presensis.id as presensi_id', 'pegawais.nomor_induk_pegawai', 'pegawais.nama')
    ->limit(3)
    ->get();

foreach ($result as $row) {
    echo "Presensi: {$row->presensi_id} | NIP: {$row->nomor_induk_pegawai} | Nama: {$row->nama}\n";
}
