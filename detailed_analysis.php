<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Detailed Analysis - Finding the Truth ===\n";

// 1. Check initial stock properly
echo "1. Initial Stock Analysis:\n";
$initialMovements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'initial')
    ->get();

foreach ($initialMovements as $movement) {
    echo "- Initial stock movement ID {$movement->id}: {$movement->qty} kg\n";
}

// Also check if there's initial stock in other tables
$initialKartuStok = \DB::table('kartu_stok')
    ->where('item_type', 'bahan_baku')
    ->where('item_id', 1)
    ->where('ref_type', 'saldo_awal')
    ->get();

foreach ($initialKartuStok as $kartu) {
    echo "- Kartu stok saldo awal: {$kartu->qty_masuk} kg\n";
}

// 2. Check production details
echo "\n2. Production Details:\n";
$production = \App\Models\ProsesProduksi::find(2);
if ($production) {
    echo "Production #2 details:\n";
    echo "- ID: {$production->id}\n";
    echo "- Tanggal: {$production->tanggal}\n";
    
    // Check production details/materials used
    $productionDetails = \DB::table('proses_produksi_detail')
        ->where('proses_produksi_id', 2)
        ->get();
    
    foreach ($productionDetails as $detail) {
        echo "- Detail: ";
        foreach ((array)$detail as $key => $value) {
            echo "{$key}={$value} ";
        }
        echo "\n";
    }
}

// 3. Check retur details in database
echo "\n3. Retur Details in Database:\n";
$returDetails = \DB::table('purchase_returns')->get();
foreach ($returDetails as $retur) {
    echo "Retur #{$retur->id}:\n";
    foreach ((array)$retur as $key => $value) {
        echo "- {$key}: {$value}\n";
    }
    echo "\n";
}

// 4. Let's recalculate based on user's correct data
echo "4. Recalculation Based on User's Correct Data:\n";
echo "User's correct data:\n";
echo "- Initial stock: 50 kg (from bahan baku master data)\n";
echo "- Purchase: 50 ekor = 40 kg\n";
echo "- Total available: 90 kg = 270 potong\n";
echo "- Production used: 160 potong = 53.33 kg\n";
echo "- Retur: 10 ekor + 1 ekor = 11 ekor = 8.8 kg\n";
echo "- Total used: 53.33 + 8.8 = 62.13 kg\n";
echo "- Should remain: 90 - 62.13 = 27.87 kg = 83.6 potong\n";

echo "\nSystem currently shows: 39 kg = 117 potong\n";
echo "Difference: 39 - 27.87 = 11.13 kg = 33.4 potong\n";

// 5. Check what's wrong
echo "\n5. Identifying Issues:\n";

// Check if initial stock is wrong
$currentInitial = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'initial')
    ->sum('qty');
echo "Current initial stock in system: {$currentInitial} kg\n";
echo "Should be: 50 kg\n";

// Check production usage
$currentProduction = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'production')
    ->where('direction', 'out')
    ->sum('qty');
echo "Current production usage: {$currentProduction} kg\n";
echo "Should be: 53.33 kg (160 potong)\n";

// Check retur
$currentRetur = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'retur')
    ->where('direction', 'out')
    ->sum('qty');
echo "Current retur: {$currentRetur} kg\n";
echo "Should be: 8.8 kg (11 ekor)\n";

echo "\n=== ISSUES FOUND ===\n";
if ($currentInitial != 50) {
    echo "❌ Initial stock wrong: {$currentInitial} kg (should be 50 kg)\n";
}
if (abs($currentProduction - 53.33) > 1) {
    echo "❌ Production usage wrong: {$currentProduction} kg (should be 53.33 kg)\n";
}
if (abs($currentRetur - 8.8) > 1) {
    echo "❌ Retur amount wrong: {$currentRetur} kg (should be 8.8 kg)\n";
}