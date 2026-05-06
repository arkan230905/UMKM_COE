<?php

<<<<<<< HEAD
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get BOP Proses data
$bopProses = DB::table('bop_proses')
    ->select('id', 'nama_bop_proses', 'proses_produksi_id', 'bop_per_unit', 'is_active', 'komponen_bop')
    ->get();

echo "Total BOP Proses: " . $bopProses->count() . "\n\n";

foreach ($bopProses as $bop) {
    echo "ID: {$bop->id}\n";
    echo "Nama BOP Proses: {$bop->nama_bop_proses}\n";
    echo "Proses Produksi ID: " . ($bop->proses_produksi_id ?? 'NULL') . "\n";
    echo "BOP per Unit: {$bop->bop_per_unit}\n";
    echo "Is Active: {$bop->is_active}\n";
    echo "Komponen BOP: " . substr($bop->komponen_bop ?? 'NULL', 0, 100) . "\n";
    echo "---\n\n";
}
=======
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BopProses;

echo "🔍 Checking BOP Data Structure\n";
echo "==============================\n\n";

$bop7 = BopProses::find(7);
$bop8 = BopProses::find(8);

echo "BOP ID 7 (Pengukusan):\n";
echo "----------------------\n";
if ($bop7) {
    echo "Listrik per jam: " . ($bop7->listrik_per_jam ?? 'NULL') . "\n";
    echo "Gas BBM per jam: " . ($bop7->gas_bbm_per_jam ?? 'NULL') . "\n";
    echo "Penyusutan per jam: " . ($bop7->penyusutan_mesin_per_jam ?? 'NULL') . "\n";
    echo "Maintenance per jam: " . ($bop7->maintenance_per_jam ?? 'NULL') . "\n";
    echo "Gaji Mandor per jam: " . ($bop7->gaji_mandor_per_jam ?? 'NULL') . "\n";
    echo "Lain-lain per jam: " . ($bop7->lain_lain_per_jam ?? 'NULL') . "\n";
    echo "Total BOP per produk: " . ($bop7->total_bop_per_produk ?? 'NULL') . "\n";
    
    echo "\nAll attributes:\n";
    print_r($bop7->getAttributes());
}

echo "\n\nBOP ID 8 (Pengemasan):\n";
echo "----------------------\n";
if ($bop8) {
    echo "Listrik per jam: " . ($bop8->listrik_per_jam ?? 'NULL') . "\n";
    echo "Gas BBM per jam: " . ($bop8->gas_bbm_per_jam ?? 'NULL') . "\n";
    echo "Penyusutan per jam: " . ($bop8->penyusutan_mesin_per_jam ?? 'NULL') . "\n";
    echo "Maintenance per jam: " . ($bop8->maintenance_per_jam ?? 'NULL') . "\n";
    echo "Gaji Mandor per jam: " . ($bop8->gaji_mandor_per_jam ?? 'NULL') . "\n";
    echo "Lain-lain per jam: " . ($bop8->lain_lain_per_jam ?? 'NULL') . "\n";
    echo "Total BOP per produk: " . ($bop8->total_bop_per_produk ?? 'NULL') . "\n";
    
    echo "\nAll attributes:\n";
    print_r($bop8->getAttributes());
}

?>
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
