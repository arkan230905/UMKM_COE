<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK MASTER BTKL DETAIL ===" . PHP_EOL;

// Cek struktur tabel btkls
echo "Struktur tabel btkls:" . PHP_EOL;
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('btkls');
foreach ($columns as $column) {
    echo "- {$column}" . PHP_EOL;
}

echo PHP_EOL . "Data di tabel btkls:" . PHP_EOL;
$masterBTKL = \Illuminate\Support\Facades\DB::table('btkls')->get();
foreach ($masterBTKL as $btkl) {
    echo "ID: {$btkl->id}" . PHP_EOL;
    echo "  Kode: '" . ($btkl->kode ?? 'NULL') . "'" . PHP_EOL;
    echo "  Nama: '" . ($btkl->nama ?? 'NULL') . "'" . PHP_EOL;
    echo "  Tarif: " . ($btkl->tarif_per_jam ?? 'NULL') . PHP_EOL;
    echo "  Kapasitas: " . ($btkl->kapasitas_per_jam ?? 'NULL') . PHP_EOL;
    echo PHP_EOL;
}

// Cek juga apakah ada field lain yang mungkin
echo PHP_EOL . "Cek field lain:" . PHP_EOL;
try {
    $sample = \Illuminate\Support\Facades\DB::table('btkls')->first();
    if ($sample) {
        foreach ($sample as $key => $value) {
            echo "- {$key}: '{$value}'" . PHP_EOL;
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
