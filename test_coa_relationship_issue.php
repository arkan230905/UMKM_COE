<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Testing COA relationship issue in detail...\n\n";

// Get the detail with relationships loaded
$detail = \App\Models\PembelianDetail::with(['bahanBaku.coaPembelian'])->find(9);

if (!$detail) {
    echo "Detail ID 9 not found!\n";
    exit;
}

echo "Detail ID: {$detail->id}\n";
echo "Bahan Baku ID: {$detail->bahan_baku_id}\n";

if ($detail->bahanBaku) {
    echo "Bahan Baku: {$detail->bahanBaku->nama_bahan}\n";
    echo "COA Pembelian ID: {$detail->bahanBaku->coa_pembelian_id}\n";
    
    // Check if coaPembelian is loaded
    if ($detail->bahanBaku->relationLoaded('coaPembelian')) {
        echo "coaPembelian relation: LOADED\n";
        if ($detail->bahanBaku->coaPembelian) {
            echo "COA Pembelian: {$detail->bahanBaku->coaPembelian->nama_akun} ({$detail->bahanBaku->coaPembelian->kode_akun})\n";
        } else {
            echo "COA Pembelian: NULL (relation loaded but no data)\n";
        }
    } else {
        echo "coaPembelian relation: NOT LOADED\n";
    }
    
    // Test accessing the relationship
    echo "\n=== TESTING RELATIONSHIP ACCESS ===\n";
    $coaPembelian = $detail->bahanBaku->coaPembelian;
    if ($coaPembelian) {
        echo "SUCCESS: {$coaPembelian->nama_akun} ({$coaPembelian->kode_akun})\n";
    } else {
        echo "FAILED: coaPembelian is null\n";
        
        // Try to understand why
        echo "\n=== DEBUGGING RELATIONSHIP ===\n";
        
        // Check the relationship definition again
        $bahanBaku = $detail->bahanBaku;
        echo "Bahan Baku coa_pembelian_id: {$bahanBaku->coa_pembelian_id}\n";
        
        // Test if COA with that ID exists
        $coa = \App\Models\Coa::find($bahanBaku->coa_pembelian_id);
        if ($coa) {
            echo "COA exists: {$coa->nama_akun} ({$coa->kode_akun})\n";
        } else {
            echo "COA with ID {$bahanBaku->coa_pembelian_id} does not exist\n";
        }
        
        // Check the relationship foreign key issue
        echo "\nRelationship uses foreign_key 'coa_pembelian_id' and owner_key 'kode_akun'\n";
        echo "This means it's looking for COA where kode_akun = {$bahanBaku->coa_pembelian_id}\n";
        
        $coaByKode = \App\Models\Coa::where('kode_akun', $bahanBaku->coa_pembelian_id)->first();
        if ($coaByKode) {
            echo "COA found by kode_akun: {$coaByKode->nama_akun} ({$coaByKode->kode_akun})\n";
        } else {
            echo "No COA found with kode_akun = {$bahanBaku->coa_pembelian_id}\n";
        }
    }
}

echo "\nDone.\n";
