<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Checking COA pembelian data in master bahan baku and bahan pendukung...\n\n";

// Check Bahan Baku with COA pembelian
echo "=== BAHAN BAKU ===\n";
$bahanBakus = \App\Models\BahanBaku::with('coaPembelian')->get();

foreach ($bahanBakus as $bb) {
    echo "ID: {$bb->id} - {$bb->nama_bahan}\n";
    echo "  COA Pembelian ID: " . ($bb->coa_pembelian_id ?? 'NULL') . "\n";
    
    if ($bb->coa_pembelian_id && $bb->coaPembelian) {
        echo "  COA Pembelian: {$bb->coaPembelian->nama_akun} ({$bb->coaPembelian->kode_akun})\n";
    } else {
        echo "  COA Pembelian: NOT FOUND\n";
    }
    echo "\n";
}

echo "\n=== BAHAN PENDUKUNG ===\n";
$bahanPendukungs = \App\Models\BahanPendukung::with('coaPembelian')->get();

foreach ($bahanPendukungs as $bp) {
    echo "ID: {$bp->id} - {$bp->nama_bahan}\n";
    echo "  COA Pembelian ID: " . ($bp->coa_pembelian_id ?? 'NULL') . "\n";
    
    if ($bp->coa_pembelian_id && $bp->coaPembelian) {
        echo "  COA Pembelian: {$bp->coaPembelian->nama_akun} ({$bp->coaPembelian->kode_akun})\n";
    } else {
        echo "  COA Pembelian: NOT FOUND\n";
    }
    echo "\n";
}

// Check specific item from purchase ID 10
echo "\n=== CHECKING ITEM FROM PURCHASE ID 10 ===\n";
$purchaseDetail = \App\Models\PembelianDetail::where('pembelian_id', 10)
    ->with(['bahanBaku.coaPembelian', 'bahanPendukung.coaPembelian'])
    ->first();

if ($purchaseDetail) {
    if ($purchaseDetail->bahanBaku) {
        echo "Bahan Baku: {$purchaseDetail->bahanBaku->nama_bahan}\n";
        echo "COA Pembelian ID: " . ($purchaseDetail->bahanBaku->coa_pembelian_id ?? 'NULL') . "\n";
        if ($purchaseDetail->bahanBaku->coaPembelian) {
            echo "COA Pembelian: {$purchaseDetail->bahanBaku->coaPembelian->nama_akun} ({$purchaseDetail->bahanBaku->coaPembelian->kode_akun})\n";
        }
    } elseif ($purchaseDetail->bahanPendukung) {
        echo "Bahan Pendukung: {$purchaseDetail->bahanPendukung->nama_bahan}\n";
        echo "COA Pembelian ID: " . ($purchaseDetail->bahanPendukung->coa_pembelian_id ?? 'NULL') . "\n";
        if ($purchaseDetail->bahanPendukung->coaPembelian) {
            echo "COA Pembelian: {$purchaseDetail->bahanPendukung->coaPembelian->nama_akun} ({$purchaseDetail->bahanPendukung->coaPembelian->kode_akun})\n";
        }
    }
} else {
    echo "Purchase detail not found\n";
}

echo "\nDone.\n";
