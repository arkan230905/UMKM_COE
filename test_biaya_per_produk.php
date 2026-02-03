<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST BIAYA PER PRODUK ===\n";

// Test existing BTKL
$btkls = \App\Models\Btkl::with('jabatan.pegawais')->get();

foreach ($btkls as $btkl) {
    echo "\n" . $btkl->kode_proses . " - " . $btkl->jabatan->nama . "\n";
    echo "- Tarif BTKL: Rp " . number_format($btkl->tarif_per_jam) . "\n";
    echo "- Kapasitas/Jam: " . number_format($btkl->kapasitas_per_jam) . " pcs\n";
    echo "- Biaya Per Produk: " . $btkl->biaya_per_produk_formatted . "\n";
    echo "- Kalkulasi: Rp " . number_format($btkl->tarif_per_jam) . " ÷ " . number_format($btkl->kapasitas_per_jam) . " = " . number_format($btkl->biaya_per_produk, 2) . "\n";
}

echo "\n=== TEST CONTROLLER INDEX ===\n";

// Test controller
$controller = new \App\Http\Controllers\MasterData\BtklController();
$result = $controller->index();
$data = $result->getData();

if (isset($data['btkls']) && $data['btkls']->count() > 0) {
    $btkl = $data['btkls']->first();
    echo "Sample BTKL from controller:\n";
    echo "- Kode: " . $btkl->kode_proses . "\n";
    echo "- Jabatan: " . $btkl->jabatan->nama . "\n";
    echo "- Tarif BTKL: " . $btkl->tarif_per_jam_formatted . "\n";
    echo "- Kapasitas: " . number_format($btkl->kapasitas_per_jam) . " pcs\n";
    echo "- Biaya Per Produk: " . $btkl->biaya_per_produk_formatted . "\n";
} else {
    echo "No BTKL data found\n";
}

echo "\n=== TEST DIFFERENT SCENARIOS ===\n";

// Test scenarios
$scenarios = [
    ['tarif' => 45000, 'kapasitas' => 100],
    ['tarif' => 45000, 'kapasitas' => 50],
    ['tarif' => 48000, 'kapasitas' => 200],
    ['tarif' => 50000, 'kapasitas' => 75],
];

foreach ($scenarios as $i => $scenario) {
    $biayaPerProduk = $scenario['tarif'] / $scenario['kapasitas'];
    echo "\nScenario " . ($i + 1) . ":\n";
    echo "- Tarif BTKL: Rp " . number_format($scenario['tarif']) . "\n";
    echo "- Kapasitas: " . $scenario['kapasitas'] . " pcs\n";
    echo "- Biaya Per Produk: Rp " . number_format($biayaPerProduk, 2) . "\n";
}
