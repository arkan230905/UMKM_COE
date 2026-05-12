<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CEK PROSES PRODUKSI UNTUK BOP ===" . PHP_EOL;

// 1. Cek semua proses produksi
echo PHP_EOL . "1. Semua Proses Produksi:" . PHP_EOL;
$allProcesses = \App\Models\ProsesProduksi::all();
foreach($allProcesses as $p) {
    echo "ID: {$p->id} | {$p->kode_proses} | {$p->nama_proses}" . PHP_EOL;
    echo "  Kapasitas: {$p->kapasitas_per_jam} unit/jam" . PHP_EOL;
    echo "  Tarif BTKL: {$p->tarif_btkl}" . PHP_EOL;
    echo "  Sudah ada BOP: " . ($p->hasBop() ? 'YA' : 'TIDAK') . PHP_EOL;
    echo PHP_EOL;
}

// 2. Cek proses yang memenuhi kriteria untuk BOP
echo PHP_EOL . "2. Proses yang BERELIGIBIL untuk BOP (kapasitas > 0 dan belum ada BOP):" . PHP_EOL;
$availableProses = \App\Models\ProsesProduksi::whereDoesntHave('bopProses')
    ->where('kapasitas_per_jam', '>', 0)
    ->get();

if($availableProses->isEmpty()) {
    echo "TIDAK ADA proses yang memenuhi kriteria!" . PHP_EOL;
} else {
    foreach($availableProses as $p) {
        echo "ID: {$p->id} | {$p->kode_proses} | {$p->nama_proses}" . PHP_EOL;
        echo "  Kapasitas: {$p->kapasitas_per_jam} unit/jam" . PHP_EOL;
        echo "  Tarif BTKL: {$p->tarif_btkl}" . PHP_EOL;
        echo PHP_EOL;
    }
}

// 3. Cek proses yang sudah ada BOPnya
echo PHP_EOL . "3. Proses yang SUDAH ADA BOP:" . PHP_EOL;
$withBop = \App\Models\ProsesProduksi::has('bopProses')->get();
foreach($withBop as $p) {
    echo "ID: {$p->id} | {$p->kode_proses} | {$p->nama_proses}" . PHP_EOL;
    echo "  BOP ID: {$p->bopProses->id}" . PHP_EOL;
    echo "  Total BOP/jam: {$p->bopProses->total_bop_per_jam}" . PHP_EOL;
    echo PHP_EOL;
}

// 4. Cek proses dengan kapasitas 0 atau kosong
echo PHP_EOL . "4. Proses dengan KAPASITAS 0 atau KOSONG:" . PHP_EOL;
$noKapasitas = \App\Models\ProsesProduksi::where(function($query) {
    $query->whereNull('kapasitas_per_jam')
          ->orWhere('kapasitas_per_jam', '<=', 0);
})->get();

foreach($noKapasitas as $p) {
    echo "ID: {$p->id} | {$p->kode_proses} | {$p->nama_proses}" . PHP_EOL;
    echo "  Kapasitas: " . ($p->kapasitas_per_jam ?? 'NULL') . PHP_EOL;
    echo "  Tarif BTKL: {$p->tarif_btkl}" . PHP_EOL;
    echo PHP_EOL;
}

echo PHP_EOL . "=== RINGKASAN ===" . PHP_EOL;
echo "Total proses: " . $allProcesses->count() . PHP_EOL;
echo "Proses eligible untuk BOP: " . $availableProses->count() . PHP_EOL;
echo "Proses sudah ada BOP: " . $withBop->count() . PHP_EOL;
echo "Proses tanpa kapasitas: " . $noKapasitas->count() . PHP_EOL;
