<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING TEPUNG CONVERSION FACTORS ===\n";

$tepungTerigu = DB::table('bahan_pendukungs')->where('id', 16)->first();
echo "TEPUNG TERIGU (ID 16):\n";
echo "Sub Satuan 1 Nilai: " . ($tepungTerigu->sub_satuan_1_nilai ?? 'NULL') . "\n";
echo "Sub Satuan 2 Nilai: " . ($tepungTerigu->sub_satuan_2_nilai ?? 'NULL') . "\n\n";

$tepungMaizena = DB::table('bahan_pendukungs')->where('id', 17)->first();
echo "TEPUNG MAIZENA (ID 17):\n";
echo "Sub Satuan 1 Nilai: " . ($tepungMaizena->sub_satuan_1_nilai ?? 'NULL') . "\n";
echo "Sub Satuan 2 Nilai: " . ($tepungMaizena->sub_satuan_2_nilai ?? 'NULL') . "\n\n";

// Calculate correct prices
echo "=== CORRECT CALCULATIONS ===\n";
echo "Tepung Terigu: Rp 50,000 per bungkus\n";
if ($tepungTerigu->sub_satuan_1_nilai) {
    $pricePerGram = 50000 / $tepungTerigu->sub_satuan_1_nilai;
    echo "Conversion: 1 bungkus = {$tepungTerigu->sub_satuan_1_nilai} gram\n";
    echo "Price per gram: Rp " . number_format($pricePerGram, 2) . "\n";
    echo "For 50 gram: Rp " . number_format(50 * $pricePerGram, 2) . "\n\n";
}

echo "Tepung Maizena: Rp 50,000 per bungkus\n";
if ($tepungMaizena->sub_satuan_1_nilai) {
    $pricePerGram = 50000 / $tepungMaizena->sub_satuan_1_nilai;
    echo "Conversion: 1 bungkus = {$tepungMaizena->sub_satuan_1_nilai} gram\n";
    echo "Price per gram: Rp " . number_format($pricePerGram, 2) . "\n";
    echo "For 50 gram: Rp " . number_format(50 * $pricePerGram, 2) . "\n";
}