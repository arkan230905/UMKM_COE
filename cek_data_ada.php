<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK DATA YANG ADA ===" . PHP_EOL;

// Cek semua data bom_job_btkl untuk BomJobCosting ID 5
$data = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
    ->where('bom_job_costing_id', 5)
    ->get();

echo "Jumlah data BTKL untuk BomJobCosting ID 5: " . $data->count() . PHP_EOL;

foreach ($data as $item) {
    echo "- ID: {$item->id}" . PHP_EOL;
    echo "  BomJobCosting ID: {$item->bom_job_costing_id}" . PHP_EOL;
    echo "  BTKL ID: {$item->btkl_id}" . PHP_EOL;
    echo "  Nama Proses: " . ($item->nama_proses ?? 'NULL') . PHP_EOL;
    echo "  Durasi Jam: " . ($item->durasi_jam ?? 'NULL') . PHP_EOL;
    echo "  Tarif per Jam: " . ($item->tarif_per_jam ?? 'NULL') . PHP_EOL;
    echo "  Kapasitas per Jam: " . ($item->kapasitas_per_jam ?? 'NULL') . PHP_EOL;
    echo "  Subtotal: " . ($item->subtotal ?? 'NULL') . PHP_EOL;
    echo PHP_EOL;
}

echo PHP_EOL;

// Cek semua data bom_job_bop untuk BomJobCosting ID 5
$data = \Illuminate\Support\Facades\DB::table('bom_job_bop')
    ->where('bom_job_costing_id', 5)
    ->get();

echo "Jumlah data BOP untuk BomJobCosting ID 5: " . $data->count() . PHP_EOL;

foreach ($data as $item) {
    echo "- ID: {$item->id}" . PHP_EOL;
    echo "  BomJobCosting ID: {$item->bom_job_costing_id}" . PHP_EOL;
    echo "  BOP ID: " . ($item->bop_id ?? 'NULL') . PHP_EOL;
    echo "  Nama BOP: " . ($item->nama_bop ?? 'NULL') . PHP_EOL;
    echo "  Jumlah: " . ($item->jumlah ?? 'NULL') . PHP_EOL;
    echo "  Tarif: " . ($item->tarif ?? 'NULL') . PHP_EOL;
    echo "  Subtotal: " . ($item->subtotal ?? 'NULL') . PHP_EOL;
    echo PHP_EOL;
}
