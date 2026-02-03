<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProsesProduksi;

echo "=== DEBUG: ProsesProduksi dengan kapasitas_per_jam > 0 ===" . PHP_EOL;

$proses = ProsesProduksi::where('kapasitas_per_jam', '>', 0)
    ->orderBy('kode_proses')
    ->get(['id', 'kode_proses', 'nama_proses', 'kapasitas_per_jam']);

echo "Total ProsesProduksi dengan kapasitas > 0: " . $proses->count() . PHP_EOL . PHP_EOL;

foreach ($proses as $p) {
    echo "- ID: {$p->id} | Kode: {$p->kode_proses} | Nama: {$p->nama_proses} | Kapasitas: {$p->kapasitas_per_jam}" . PHP_EOL;
}

echo PHP_EOL . "=== SEMUA ProsesProduksi (untuk perbandingan) ===" . PHP_EOL;

$allProses = ProsesProduksi::orderBy('kode_proses')
    ->get(['id', 'kode_proses', 'nama_proses', 'kapasitas_per_jam']);

echo "Total SEMUA ProsesProduksi: " . $allProses->count() . PHP_EOL . PHP_EOL;

foreach ($allProses as $p) {
    $kapasitas = $p->kapasitas_per_jam ?? 'NULL';
    echo "- ID: {$p->id} | Kode: {$p->kode_proses} | Nama: {$p->nama_proses} | Kapasitas: {$kapasitas}" . PHP_EOL;
}
