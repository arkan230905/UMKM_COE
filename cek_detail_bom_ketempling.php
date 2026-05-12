<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK DETAIL BOM KETAMPLING ===" . PHP_EOL;

// 1. Cek BomJobCosting untuk ketempling
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();
if (!$bomJobCosting) {
    echo "BomJobCosting untuk ketempling tidak ditemukan!" . PHP_EOL;
    exit;
}

echo "BomJobCosting ID: {$bomJobCosting->id}" . PHP_EOL;
echo "Produk: {$bomJobCosting->produk->nama_produk}" . PHP_EOL;
echo "Total BBB: Rp " . number_format($bomJobCosting->total_bbb, 2, ',', '.') . PHP_EOL;
echo "Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
echo "Total BOP: Rp " . number_format($bomJobCosting->total_bop, 2, ',', '.') . PHP_EOL;
echo PHP_EOL;

// 2. Cek detail BBB (Bahan Baku)
echo "DETAIL BBB (Bahan Baku):" . PHP_EOL;
$bbbDetails = \App\Models\BomJobBBB::with('bahanBaku')
    ->where('bom_job_costing_id', $bomJobCosting->id)
    ->get();

if ($bbbDetails->isEmpty()) {
    echo "Tidak ada data BBB" . PHP_EOL;
} else {
    foreach ($bbbDetails as $index => $bbb) {
        echo ($index + 1) . ". ID: {$bbb->id}" . PHP_EOL;
        echo "   Bahan Baku ID: {$bbb->bahan_baku_id}" . PHP_EOL;
        echo "   Nama Bahan: ";
        
        if ($bbb->bahanBaku) {
            echo $bbb->bahanBaku->nama_bahan;
        } else {
            echo "NULL (bahanBaku relation null)";
        }
        echo PHP_EOL;
        
        echo "   Nama Terhapus: " . ($bbb->nama_bahan_terhapus ?? 'NULL') . PHP_EOL;
        echo "   Jumlah: {$bbb->jumlah} {$bbb->satuan}" . PHP_EOL;
        echo "   Harga: Rp " . number_format($bbb->harga_satuan, 2, ',', '.') . PHP_EOL;
        echo "   Subtotal: Rp " . number_format($bbb->subtotal, 2, ',', '.') . PHP_EOL;
        echo "   Status: " . ($bbb->harga_satuan > 0 ? 'AKTIF' : 'TERHAPUS') . PHP_EOL;
        echo PHP_EOL;
    }
}

// 3. Cek detail BTKL
echo "DETAIL BTKL:" . PHP_EOL;
$btklDetails = \App\Models\BomJobBTKL::with('btkl')
    ->where('bom_job_costing_id', $bomJobCosting->id)
    ->get();

foreach ($btklDetails as $index => $btkl) {
    echo ($index + 1) . ". ID: {$btkl->id}" . PHP_EOL;
    echo "   BTKL ID: {$btkl->btkl_id}" . PHP_EOL;
    echo "   Nama Proses: {$btkl->nama_proses}" . PHP_EOL;
    echo "   BTKL Relation: ";
    
    if ($btkl->btkl) {
        echo $btkl->btkl->nama_btkl;
    } else {
        echo "NULL";
    }
    echo PHP_EOL;
    echo "   Tarif: Rp " . number_format($btkl->tarif_per_jam, 2, ',', '.') . "/jam" . PHP_EOL;
    echo "   Subtotal: Rp " . number_format($btkl->subtotal, 2, ',', '.') . PHP_EOL;
    echo PHP_EOL;
}

// 4. Cek detail BOP
echo "DETAIL BOP:" . PHP_EOL;
$bopDetails = \App\Models\BomJobBOP::with('bop')
    ->where('bom_job_costing_id', $bomJobCosting->id)
    ->get();

foreach ($bopDetails as $index => $bop) {
    echo ($index + 1) . ". ID: {$bop->id}" . PHP_EOL;
    echo "   BOP ID: {$bop->bop_id}" . PHP_EOL;
    echo "   Nama BOP: {$bop->nama_bop}" . PHP_EOL;
    echo "   BOP Relation: ";
    
    if ($bop->bop) {
        echo $bop->bop->nama ?? 'NULL';
    } else {
        echo "NULL";
    }
    echo PHP_EOL;
    echo "   Tarif: Rp " . number_format($bop->tarif, 2, ',', '.') . PHP_EOL;
    echo "   Subtotal: Rp " . number_format($bop->subtotal, 2, ',', '.') . PHP_EOL;
    echo PHP_EOL;
}

// 5. Cek apakah ada data yang bermasalah
echo PHP_EOL . "CEK DATA BERMASALAH:" . PHP_EOL;

// Cek BBB dengan bahan_baku_id yang tidak valid
$problematicBBB = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)
    ->where(function($query) {
        $query->whereNull('bahan_baku_id')
              ->orWhere('bahan_baku_id', 0);
    })
    ->get();

if ($problematicBBB->count() > 0) {
    echo "❌ Ditemukan {$problematicBBB->count()} BBB dengan bahan_baku_id bermasalah" . PHP_EOL;
}

// Cek BTKL dengan btkl_id yang tidak valid  
$problematicBTKL = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)
    ->where(function($query) {
        $query->whereNull('btkl_id')
              ->orWhere('btkl_id', 0);
    })
    ->get();

if ($problematicBTKL->count() > 0) {
    echo "❌ Ditemukan {$problematicBTKL->count()} BTKL dengan btkl_id bermasalah" . PHP_EOL;
}

// Cek BOP dengan bop_id yang tidak valid
$problematicBOP = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)
    ->where(function($query) {
        $query->whereNull('bop_id')
              ->orWhere('bop_id', 0);
    })
    ->get();

if ($problematicBOP->count() > 0) {
    echo "❌ Ditemukan {$problematicBOP->count()} BOP dengan bop_id bermasalah" . PHP_EOL;
}

if ($problematicBBB->count() == 0 && $problematicBTKL->count() == 0 && $problematicBOP->count() == 0) {
    echo "✅ Tidak ada data yang bermasalah" . PHP_EOL;
}
