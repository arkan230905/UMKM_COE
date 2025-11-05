<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG OUTPUT LANGSUNG ===\n\n";

$presensi = \App\Models\Presensi::with('pegawai')->first();

echo "Presensi ID: {$presensi->id}\n";
echo "Pegawai ID: {$presensi->pegawai_id}\n\n";

echo "=== PEGAWAI OBJECT ===\n";
var_dump($presensi->pegawai);

echo "\n\n=== ATTRIBUTES ===\n";
var_dump($presensi->pegawai->getAttributes());

echo "\n\n=== NAMA FIELD ===\n";
echo "pegawai->nama: '{$presensi->pegawai->nama}'\n";
echo "pegawai['nama']: '{$presensi->pegawai['nama']}'\n";
echo "pegawai->getAttribute('nama'): '{$presensi->pegawai->getAttribute('nama')}'\n";

echo "\n\n=== RAW DATABASE ===\n";
$raw = \DB::table('pegawais')->where('id', $presensi->pegawai_id)->first();
echo "Raw nama: '{$raw->nama}'\n";
echo "Raw NIP: '{$raw->nomor_induk_pegawai}'\n";

echo "\n\n=== BLADE SIMULATION ===\n";
$output = $presensi->pegawai->nama;
echo "Output yang akan di-render di blade: '{$output}'\n";
echo "Tipe data: " . gettype($output) . "\n";
echo "Length: " . strlen($output) . "\n";

// Test apakah ada magic method
echo "\n\n=== MAGIC METHODS ===\n";
if (method_exists($presensi->pegawai, '__get')) {
    echo "Ada method __get()\n";
}
if (method_exists($presensi->pegawai, '__toString')) {
    echo "Ada method __toString(): " . $presensi->pegawai->__toString() . "\n";
}
