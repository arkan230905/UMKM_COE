<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate what controller does
$controller = new \App\Http\Controllers\ProduksiController();
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('getProductionCostBreakdown');
$method->setAccessible(true);

$p = \App\Models\Produksi::find(3);
$breakdown = $method->invoke($controller, $p);

echo "=== BREAKDOWN DATA ===" . PHP_EOL;
echo "BOP exists: " . (isset($breakdown['bop']) ? 'YES' : 'NO') . PHP_EOL;
if (isset($breakdown['bop'])) {
    echo "BOP count: " . count($breakdown['bop']) . PHP_EOL;
    echo PHP_EOL . "BOP Data:" . PHP_EOL;
    print_r($breakdown['bop']);
}

echo PHP_EOL . "=== HPP BOP DATA ===" . PHP_EOL;
$hppBops = \App\Models\HargaPokokProduksiBop::where('user_id', 2)
    ->with('bopProses')
    ->get();
    
foreach($hppBops as $hppBop) {
    echo "- HPP BOP ID: " . $hppBop->id . PHP_EOL;
    echo "  BOP Proses ID: " . $hppBop->bop_proses_id . PHP_EOL;
    if ($hppBop->bopProses) {
        echo "  Nama: " . $hppBop->bopProses->nama_bop_proses . PHP_EOL;
        echo "  Total BOP per Produk: " . $hppBop->bopProses->total_bop_per_produk . PHP_EOL;
        echo "  Komponen: " . json_encode($hppBop->bopProses->komponen_bop, JSON_PRETTY_PRINT) . PHP_EOL;
    }
}
