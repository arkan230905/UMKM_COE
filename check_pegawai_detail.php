<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CEK DETAIL PEGAWAI ===\n\n";

$pegawais = DB::table('pegawais')->get();

foreach ($pegawais as $pegawai) {
    echo "ID: {$pegawai->id}\n";
    echo "Nama: {$pegawai->nama}\n";
    echo "Jabatan (text): " . ($pegawai->jabatan ?? 'NULL') . "\n";
    echo "Jabatan ID: " . ($pegawai->jabatan_id ?? 'NULL') . "\n";
    echo str_repeat("-", 50) . "\n";
}
