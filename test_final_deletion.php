<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BahanBaku;
use App\Models\BomJobBBB;
use App\Models\BomJobCosting;

echo "=== TEST PENGHAPUSAN BAHAN BAKU (FIXED) ===\n";

// 1. Buat bahan baru untuk test
$ayamTest = BahanBaku::create([
    'nama_bahan' => 'Ayam Test Final',
    'kode_bahan' => 'BB-TEST-FINAL',
    'satuan_id' => 3, // Kilogram
    'stok' => 10,
    'harga_satuan' => 25000,
    'deskripsi' => 'Test final untuk penghapusan'
]);

if ($ayamTest) {
    echo "Bahan yang dibuat: {$ayamTest->nama_bahan} (Rp " . number_format($ayamTest->harga_satuan, 2, ',', '.') . ")\n";
    
    // 2. Tambahkan ke BOM Ketempling
    $bomJobCosting = BomJobCosting::where('produk_id', 3)->first(); // Ketempling
    if ($bomJobCosting) {
        $bomJobBBB = BomJobBBB::create([
            'bom_job_costing_id' => $bomJobCosting->id,
            'bahan_baku_id' => $ayamTest->id,
            'jumlah' => 0.5,
            'satuan' => 'Kg',
            'harga_satuan' => 25000,
            'subtotal' => 12500
        ]);
        
        echo "Ditambahkan ke BOM Ketempling dengan subtotal: Rp 12.500\n";
        
        // Recalculate
        $bomJobCosting->recalculate();
        echo "Total BBB setelah tambah: Rp " . number_format($bomJobCosting->total_bbb, 2, ',', '.') . "\n";
    }
    
    // 3. Hapus bahan baku (ini akan trigger deleting observer)
    echo "\nMenghapus bahan baku...\n";
    $ayamTest->delete();
    
    // 4. Cek data setelah hapus
    echo "\nData setelah penghapusan:\n";
    $bomJobBBBsAfter = BomJobBBB::where('bahan_baku_id', $ayamTest->id)->get();
    
    foreach ($bomJobBBBsAfter as $detail) {
        echo "- BOM ID {$detail->id}:\n";
        echo "  Harga setelah: Rp " . number_format($detail->harga_satuan, 2, ',', '.') . "\n";
        echo "  Subtotal setelah: Rp " . number_format($detail->subtotal, 2, ',', '.') . "\n";
        echo "  Status: " . ($detail->harga_satuan == 0 ? 'DIHAPUS' : 'MASIH AKTIF') . "\n";
        echo "  Catatan: " . ($detail->catatan_hapus ?? 'Tidak ada') . "\n";
        echo "  Nama terhapus: " . ($detail->nama_bahan_terhapus ?? 'Tidak ada') . "\n";
        echo "  Harga terakhir: Rp " . number_format($detail->harga_terakhir ?? 0, 2, ',', '.') . "\n";
        echo "  Satuan terakhir: " . ($detail->satuan_terakhir ?? 'Tidak ada') . "\n";
    }
    
    // 5. Cek total BOM
    $bomJobCosting = BomJobCosting::find($detail->bom_job_costing_id);
    if ($bomJobCosting) {
        echo "\nTotal BOM setelah hapus:\n";
        echo "- Total BBB: Rp " . number_format($bomJobCosting->total_bbb, 2, ',', '.') . "\n";
        echo "- Total HPP: Rp " . number_format($bomJobCosting->total_hpp, 2, ',', '.') . "\n";
    }
} else {
    echo "Gagal membuat bahan baku!\n";
}

echo "\n=== SELESAI ===\n";
