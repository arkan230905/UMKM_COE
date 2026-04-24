<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\BahanBaku;
use App\Models\Satuan;

echo "Adding Jagung bahan baku:\n";

// Check if Jagung already exists
$jagung = BahanBaku::where('nama_bahan', 'like', '%jagung%')->first();
if ($jagung) {
    echo "Jagung already exists: " . $jagung->nama_bahan . " (ID: " . $jagung->id . ")\n";
} else {
    // Get a suitable satuan (let's use KG)
    $satuanKg = Satuan::where('nama', 'like', '%kilogram%')
        ->orWhere('nama', 'like', '%kg%')
        ->first();
    
    if (!$satuanKg) {
        // Create KG satuan if it doesn't exist
        $satuanKg = Satuan::create([
            'nama' => 'Kilogram',
            'kode_satuan' => 'KG',
            'deskripsi' => 'Kilogram'
        ]);
        echo "Created KG satuan (ID: " . $satuanKg->id . ")\n";
    }
    
    // Create Jagung bahan baku
    $jagung = BahanBaku::create([
        'nama_bahan' => 'Jagung',
        'satuan_id' => $satuanKg->id,
        'harga_satuan' => 8000, // Default price
        'saldo_awal' => 0,
        'tanggal_saldo_awal' => now()->format('Y-m-d'),
        'stok_minimum' => 10,
        'deskripsi' => 'Jagung untuk bahan baku'
    ]);
    
    echo "✓ Created Jagung bahan baku (ID: " . $jagung->id . ")\n";
}

echo "\nAll Bahan Baku now:\n";
$all = BahanBaku::all(['id', 'nama_bahan', 'satuan_id']);
foreach($all as $bb) {
    echo "- " . $bb->id . ': ' . $bb->nama_bahan . " (Satuan ID: " . ($bb->satuan_id ?? 'null') . ")\n";
}