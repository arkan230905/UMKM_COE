<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BahanBaku;
use App\Models\BomJobBBB;
use App\Models\BomJobCosting;

echo "=== CEK DATA BAHAN YANG DIHAPUS ===\n";

// 1. Cek BOM Ketempling (ID 3)
$bomJobCosting = BomJobCosting::where('produk_id', 3)->first();
if ($bomJobCosting) {
    echo "Produk: {$bomJobCosting->produk->nama_produk}\n";
    
    $bomJobBBBs = BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
    echo "Jumlah detail BBB: " . count($bomJobBBBs) . "\n";
    
    foreach ($bomJobBBBs as $detail) {
        echo "- Detail ID {$detail->id}:\n";
        echo "  Bahan Baku ID: " . $detail->bahan_baku_id . "\n";
        echo "  Nama Bahan: " . ($detail->bahanBaku->nama_bahan ?? 'N/A') . "\n";
        echo "  Status: " . ($detail->harga_satuan == 0 ? 'DIHAPUS' : 'AKTIF') . "\n";
        echo "  Harga Satuan: Rp " . number_format($detail->harga_satuan, 2, ',', '.') . "\n";
        echo "  Subtotal: Rp " . number_format($detail->subtotal, 2, ',', '.') . "\n";
        echo "  Catatan Hapus: " . ($detail->catatan_hapus ?? 'Tidak ada') . "\n";
        echo "  Nama Terhapus: " . ($detail->nama_bahan_terhapus ?? 'Tidak ada') . "\n";
        echo "  Harga Terakhir: Rp " . number_format($detail->harga_terakhir ?? 0, 2, ',', '.') . "\n";
        echo "  ---\n";
    }
    
    echo "\nTotal BBB: Rp " . number_format($bomJobCosting->total_bbb, 2, ',', '.') . "\n";
    echo "Total HPP: Rp " . number_format($bomJobCosting->total_hpp, 2, ',', '.') . "\n";
} else {
    echo "BOM Job Costing tidak ditemukan!\n";
}

echo "\n=== SELESAI ===\n";
