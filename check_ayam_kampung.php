<?php
require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKING AYAM KAMPUNG STOCK DATA ===\n\n";

// Check stock movements
echo "1. Stock Movements:\n";
$movements = DB::table('stock_movements')
    ->where('item_type', 'material')
    ->where('item_id', 2)
    ->orderBy('tanggal')
    ->orderBy('created_at')
    ->get();

foreach($movements as $m) {
    echo "  {$m->tanggal} | {$m->direction} | {$m->qty} {$m->satuan} | {$m->ref_type} | Cost: {$m->total_cost}\n";
}

// Check stock layers
echo "\n2. Stock Layers:\n";
$layers = DB::table('stock_layers')
    ->where('item_type', 'material')
    ->where('item_id', 2)
    ->orderBy('tanggal')
    ->get();

foreach($layers as $l) {
    echo "  {$l->tanggal} | {$l->remaining_qty} {$l->satuan} | {$l->ref_type} | Unit Cost: {$l->unit_cost}\n";
}

// Check master stock
echo "\n3. Master Stock:\n";
$master = DB::table('bahan_bakus')->where('id', 2)->first();
echo "  Master Stock: {$master->stok} Ekor\n";

// Check conversion ratios
echo "\n4. Conversion Ratios:\n";
echo "  Satuan ID: {$master->satuan_id}\n";
echo "  Sub Satuan 1: {$master->sub_satuan_1_id} = {$master->sub_satuan_1_konversi}\n";
echo "  Sub Satuan 2: {$master->sub_satuan_2_id} = {$master->sub_satuan_2_konversi}\n";
echo "  Sub Satuan 3: {$master->sub_satuan_3_id} = {$master->sub_satuan_3_konversi}\n";