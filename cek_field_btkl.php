<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK FIELD BOM_JOB_BTKL ===" . PHP_EOL;

// Cek data yang sudah ada
$existingData = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
    ->leftJoin('btkls', 'bom_job_btkl.btkl_id', '=', 'btkls.id')
    ->leftJoin('jabatans', 'bom_job_btkl.jabatan_id', '=', 'jabatans.id')
    ->where('bom_job_btkl.bom_job_costing_id', 5)
    ->first();

if ($existingData) {
    echo "Data yang sudah ada:" . PHP_EOL;
    echo "- ID: " . $existingData->id . PHP_EOL;
    echo "- BomJobCosting ID: " . $existingData->bom_job_costing_id . PHP_EOL;
    echo "- BTKL ID: " . $existingData->btkl_id . PHP_EOL;
    echo "- Jabatan ID: " . $existingData->jabatan_id . PHP_EOL;
    echo "- Kode Proses: " . $existingData->kode_proses . PHP_EOL;
    echo "- Nama Proses: " . $existingData->nama_proses . PHP_EOL;
    echo "- Durasi Jam: " . $existingData->durasi_jam . PHP_EOL;
    echo "- Tarif per Jam: " . $existingData->tarif_per_jam . PHP_EOL;
    echo "- Kapasitas per Jam: " . $existingData->kapasitas_per_jam . PHP_EOL;
    echo "- Subtotal: " . $existingData->subtotal . PHP_EOL;
    echo PHP_EOL;
} else {
    echo "Tidak ada data!" . PHP_EOL;
}

echo PHP_EOL;

// Cek field yang tersedia
echo "Field yang tersedia di tabel bom_job_btkl:" . PHP_EOL;
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_btkl');
foreach ($columns as $column) {
    echo "- {$column}" . PHP_EOL;
}
