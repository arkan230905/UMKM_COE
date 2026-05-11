<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pegawais = \App\Models\Pegawai::with('jabatanRelasi')->get(['id','nama','jabatan_id']);
echo "=== CEK PEGAWAI DAN JABATAN ===\n";
echo str_pad("ID", 5) . str_pad("Nama", 30) . str_pad("Jabatan ID", 15) . "Jabatan Nama\n";
echo str_repeat("-", 80) . "\n";

foreach($pegawais as $p) {
    $jabatanNama = $p->jabatanRelasi ? $p->jabatanRelasi->nama : 'NULL';
    echo str_pad($p->id, 5) . str_pad($p->nama, 30) . str_pad($p->jabatan_id ?? 'NULL', 15) . $jabatanNama . "\n";
}

echo "\n=== SUMMARY ===\n";
$withJabatan = $pegawais->whereNotNull('jabatan_id')->count();
$withoutJabatan = $pegawais->whereNull('jabatan_id')->count();
echo "Total Pegawai: " . $pegawais->count() . "\n";
echo "Dengan Jabatan: " . $withJabatan . "\n";
echo "Tanpa Jabatan: " . $withoutJabatan . "\n";
