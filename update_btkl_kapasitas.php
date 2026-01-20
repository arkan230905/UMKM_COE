<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== UPDATE KAPASITAS BTKL LAMA ===\n\n";

// Update semua BTKL yang belum memiliki kapasitas_per_jam
$btkls = \App\Models\Btkl::whereNull('kapasitas_per_jam')->get();

foreach ($btkls as $btkl) {
    // Set kapasitas default berdasarkan jenis proses
    $kapasitasDefault = 50; // Default untuk proses produksi
    
    // Atur kapasitas berdasarkan nama proses
    if (stripos($btkl->nama_proses, 'mixing') !== false) {
        $kapasitasDefault = 20; // Mixing lebih lambat
    } elseif (stripos($btkl->nama_proses, 'baking') !== false) {
        $kapasitasDefault = 30; // Baking sedang lebih lambat dari mixing
    } elseif (stripos($btkl->nama_proses, 'pengemasan') !== false) {
        $kapasitasDefault = 100; // Pengemasan paling cepat
    } elseif (stripos($btkl->nama_proses, 'pelabelan') !== false) {
        $kapasitasDefault = 80; // Pelabelan sedang
    }
    
    $btkl->update(['kapasitas_per_jam' => $kapasitasDefault]);
    
    echo "Updated BTKL ID: {$btkl->id} - {$btkl->nama_proses} - Kapasitas: {$kapasitasDefault} unit/jam\n";
}

echo "\nTotal updated: " . $btkls->count() . " BTKL\n";
echo "=== SELESAI ===\n";
