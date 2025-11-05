<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST INSERT BAHAN BAKU ===\n\n";

// Cek struktur tabel
echo "Struktur tabel bahan_bakus:\n";
$columns = DB::select("PRAGMA table_info(bahan_bakus)");
foreach ($columns as $col) {
    echo "  - {$col->name} ({$col->type})\n";
}

echo "\n";

// Test insert
try {
    $bahanBaku = \App\Models\BahanBaku::create([
        'nama_bahan' => 'Test Daging',
        'satuan_id' => 1,
        'stok' => 10,
        'harga_satuan' => 15000
    ]);
    
    echo "✅ Insert berhasil!\n";
    echo "ID: {$bahanBaku->id}\n";
    echo "Nama: {$bahanBaku->nama_bahan}\n";
    echo "Satuan ID: {$bahanBaku->satuan_id}\n";
    echo "Stok: {$bahanBaku->stok}\n";
    echo "Harga: {$bahanBaku->harga_satuan}\n";
    
    // Hapus test data
    $bahanBaku->delete();
    echo "\n✓ Test data dihapus\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
