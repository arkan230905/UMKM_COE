<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST BOP PROSES ROUTE ===" . PHP_EOL;

// Test route exists
try {
    $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('master-data.bop-proses.create');
    if ($route) {
        echo "✅ Route master-data.bop-proses.create DITEMUKAN" . PHP_EOL;
        echo "URI: " . $route->uri() . PHP_EOL;
        echo "Action: " . $route->getActionName() . PHP_EOL;
    } else {
        echo "❌ Route master-data.bop-proses.create TIDAK DITEMUKAN" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== CEK DATA PROSES UNTUK BOP ===" . PHP_EOL;

// Test data yang akan dikirim ke view create
$availableProses = \App\Models\ProsesProduksi::whereDoesntHave('bopProses')
    ->where('kapasitas_per_jam', '>', 0)
    ->get();

echo "Jumlah proses yang available untuk BOP: " . $availableProses->count() . PHP_EOL;

foreach($availableProses as $proses) {
    echo "- ID: {$proses->id} | {$proses->kode_proses} | {$proses->nama_proses}" . PHP_EOL;
    echo "  Kapasitas: {$proses->kapasitas_per_jam} | Tarif: {$proses->tarif_btkl}" . PHP_EOL;
}
