<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Cleaning duplicate records...\n\n";

try {
    // Remove duplicate Minyak Goreng (keep the one with lower ID)
    $minyakRecords = \DB::table('bahan_pendukungs')
        ->where('nama_bahan', 'Minyak Goreng')
        ->orderBy('id')
        ->get();
    
    if ($minyakRecords->count() > 1) {
        // Keep the first one (lowest ID), delete the rest
        $keepId = $minyakRecords->first()->id;
        $deleteIds = $minyakRecords->skip(1)->pluck('id');
        
        foreach ($deleteIds as $id) {
            \DB::table('bahan_pendukungs')->where('id', $id)->delete();
            echo "✓ Removed duplicate Minyak Goreng (ID: $id)\n";
        }
    }
    
    // Remove duplicate Tepung Maizena (keep the one with lower ID)
    $tepungRecords = \DB::table('bahan_pendukungs')
        ->where('nama_bahan', 'Tepung Maizena')
        ->orderBy('id')
        ->get();
    
    if ($tepungRecords->count() > 1) {
        // Keep the first one (lowest ID), delete the rest
        $keepId = $tepungRecords->first()->id;
        $deleteIds = $tepungRecords->skip(1)->pluck('id');
        
        foreach ($deleteIds as $id) {
            \DB::table('bahan_pendukungs')->where('id', $id)->delete();
            echo "✓ Removed duplicate Tepung Maizena (ID: $id)\n";
        }
    }
    
    echo "\n=== CLEANING COMPLETE ===\n";
    
    // Show final data
    echo "\nFinal data in bahan_pendukungs:\n";
    echo str_repeat("=", 120) . "\n";
    $allData = \DB::table('bahan_pendukungs')
        ->orderBy('nama_bahan')
        ->get(['kode_bahan', 'nama_bahan', 'harga_satuan', 'stok', 'stok_minimum', 'kategori']);
    
    foreach ($allData as $item) {
        echo sprintf("%-12s %-25s %-15s %-8s %-8s %-15s\n", 
            $item->kode_bahan, 
            $item->nama_bahan, 
            "RP" . number_format($item->harga_satuan, 0, ',', '.'), 
            $item->stok, 
            $item->stok_minimum,
            $item->kategori
        );
    }
    echo str_repeat("=", 120) . "\n";
    echo "Total records: " . $allData->count() . "\n";
    
    echo "\n✅ Data bahan pendukung sekarang sesuai dengan spreadsheet!\n";
    echo "Silakan refresh browser untuk melihat perubahan.\n";
    
} catch (\Exception $e) {
    echo "Cleaning failed: " . $e->getMessage() . "\n";
}
