<?php

/**
 * Script to update bahan pendukung stock from 50 to 200 units
 * Run this script to update existing database records
 */

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Starting bahan pendukung stock update...\n";
    
    // Update all bahan pendukung records that currently have stock = 50 to stock = 200
    $updated = DB::table('bahan_pendukungs')
        ->where('stok', 50)
        ->update(['stok' => 200]);
    
    echo "Updated {$updated} bahan pendukung records from 50 to 200 stock.\n";
    
    // Show current stock levels for verification
    $bahanPendukungs = DB::table('bahan_pendukungs')
        ->select('id', 'nama_bahan', 'stok', 'satuan_id')
        ->get();
    
    echo "\nCurrent bahan pendukung stock levels:\n";
    echo "ID\tNama Bahan\t\tStok\n";
    echo "----------------------------------------\n";
    
    foreach ($bahanPendukungs as $bahan) {
        echo "{$bahan->id}\t{$bahan->nama_bahan}\t\t{$bahan->stok}\n";
    }
    
    echo "\nStock update completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error updating stock: " . $e->getMessage() . "\n";
}