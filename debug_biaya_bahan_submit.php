<?php
/**
 * DEBUG SCRIPT - Test Biaya Bahan Submission
 * Jalankan: php debug_biaya_bahan_submit.php
 */

require_once 'vendor/autoload.php';

// Simulate Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Bom;
use App\Models\BomJobCosting;
use App\Models\BomDetail;
use App\Models\BomJobBahanPendukung;
use Illuminate\Support\Facades\DB;

echo "=== DEBUG BIAYA BAHAN SUBMISSION ===\n";

try {
    // Test data
    $produkId = 1; // Ganti dengan ID produk yang ada
    
    // Cek produk exists
    $produk = Produk::find($produkId);
    if (!$produk) {
        echo "❌ Produk dengan ID {$produkId} tidak ditemukan!\n";
        echo "Available products:\n";
        $produks = Produk::select('id', 'nama_produk')->limit(5)->get();
        foreach ($produks as $p) {
            echo "  - ID: {$p->id}, Nama: {$p->nama_produk}\n";
        }
        exit;
    }
    
    echo "✅ Produk ditemukan: {$produk->nama_produk}\n";
    
    // Cek bahan baku tersedia
    $bahanBaku = BahanBaku::first();
    if (!$bahanBaku) {
        echo "❌ Tidak ada bahan baku di database!\n";
        exit;
    }
    echo "✅ Bahan baku tersedia: {$bahanBaku->nama_bahan}\n";
    
    // Cek bahan pendukung tersedia
    $bahanPendukung = BahanPendukung::first();
    if (!$bahanPendukung) {
        echo "❌ Tidak ada bahan pendukung di database!\n";
        exit;
    }
    echo "✅ Bahan pendukung tersedia: {$bahanPendukung->nama_bahan}\n";
    
    // Simulate request data
    $requestData = [
        'bahan_baku' => [
            '1' => [
                'id' => $bahanBaku->id,
                'jumlah' => '2.5',
                'satuan' => 'kg'
            ]
        ],
        'bahan_pendukung' => [
            '1' => [
                'id' => $bahanPendukung->id,
                'jumlah' => '5',
                'satuan' => 'pcs'
            ]
        ]
    ];
    
    echo "\n=== TESTING SUBMISSION ===\n";
    
    DB::beginTransaction();
    
    $totalBiaya = 0;
    $savedCount = 0;
    
    // GET OR CREATE BOM
    $bom = Bom::where('produk_id', $produk->id)->first();
    if (!$bom) {
        $bom = new Bom();
        $bom->produk_id = $produk->id;
        $bom->nama_bom = 'BOM - ' . $produk->nama_produk;
        $bom->deskripsi = 'Bill of Materials untuk ' . $produk->nama_produk;
        $bom->total_biaya = 0;
        $bom->save();
        echo "✅ BOM created with ID: {$bom->id}\n";
    } else {
        echo "✅ BOM found with ID: {$bom->id}\n";
    }
    
    // GET OR CREATE BOM JOB COSTING
    $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)->first();
    if (!$bomJobCosting) {
        $bomJobCosting = new BomJobCosting();
        $bomJobCosting->produk_id = $produk->id;
        $bomJobCosting->save();
        echo "✅ BomJobCosting created with ID: {$bomJobCosting->id}\n";
    } else {
        echo "✅ BomJobCosting found with ID: {$bomJobCosting->id}\n";
    }
    
    // HAPUS DATA LAMA
    DB::table('bom_details')->where('bom_id', $bom->id)->delete();
    DB::table('bom_job_bahan_pendukungs')->where('bom_job_costing_id', $bomJobCosting->id)->delete();
    echo "✅ Old data deleted\n";
    
    // SIMPAN BAHAN BAKU
    foreach ($requestData['bahan_baku'] as $key => $item) {
        if (!empty($item['id']) && !empty($item['jumlah']) && $item['jumlah'] > 0 && !empty($item['satuan'])) {
            $bahanBaku = BahanBaku::find($item['id']);
            if ($bahanBaku) {
                $jumlah = (float)$item['jumlah'];
                $harga = (float)$bahanBaku->harga_satuan;
                $subtotal = $harga * $jumlah;
                $totalBiaya += $subtotal;
                
                DB::table('bom_details')->insert([
                    'bom_id' => $bom->id,
                    'bahan_baku_id' => $bahanBaku->id,
                    'jumlah' => $jumlah,
                    'satuan' => $item['satuan'],
                    'harga_per_satuan' => $harga,
                    'total_harga' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $savedCount++;
                echo "✅ Bahan Baku saved: {$bahanBaku->nama_bahan} - {$jumlah} {$item['satuan']} - Rp " . number_format($subtotal, 0, ',', '.') . "\n";
            }
        }
    }
    
    // SIMPAN BAHAN PENDUKUNG
    foreach ($requestData['bahan_pendukung'] as $key => $item) {
        if (!empty($item['id']) && !empty($item['jumlah']) && $item['jumlah'] > 0 && !empty($item['satuan'])) {
            $bahanPendukung = BahanPendukung::find($item['id']);
            if ($bahanPendukung) {
                $jumlah = (float)$item['jumlah'];
                $harga = (float)$bahanPendukung->harga_satuan;
                $subtotal = $harga * $jumlah;
                $totalBiaya += $subtotal;
                
                DB::table('bom_job_bahan_pendukungs')->insert([
                    'bom_job_costing_id' => $bomJobCosting->id,
                    'bahan_pendukung_id' => $bahanPendukung->id,
                    'jumlah' => $jumlah,
                    'satuan' => $item['satuan'],
                    'harga_satuan' => $harga,
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $savedCount++;
                echo "✅ Bahan Pendukung saved: {$bahanPendukung->nama_bahan} - {$jumlah} {$item['satuan']} - Rp " . number_format($subtotal, 0, ',', '.') . "\n";
            }
        }
    }
    
    // UPDATE TOTAL BIAYA
    DB::table('produks')->where('id', $produk->id)->update([
        'harga_bom' => $totalBiaya,
        'updated_at' => now()
    ]);
    
    DB::table('boms')->where('id', $bom->id)->update([
        'total_biaya' => $totalBiaya,
        'updated_at' => now()
    ]);
    
    echo "\n=== HASIL ===\n";
    echo "✅ Total items saved: {$savedCount}\n";
    echo "✅ Total biaya: Rp " . number_format($totalBiaya, 0, ',', '.') . "\n";
    
    // Verify data tersimpan
    $bomDetailsCount = DB::table('bom_details')->where('bom_id', $bom->id)->count();
    $bomJobBahanPendukungCount = DB::table('bom_job_bahan_pendukungs')->where('bom_job_costing_id', $bomJobCosting->id)->count();
    
    echo "✅ BOM Details count: {$bomDetailsCount}\n";
    echo "✅ BOM Job Bahan Pendukung count: {$bomJobBahanPendukungCount}\n";
    
    // Check product harga_bom updated
    $produkUpdated = Produk::find($produk->id);
    echo "✅ Product harga_bom updated: Rp " . number_format($produkUpdated->harga_bom, 0, ',', '.') . "\n";
    
    DB::commit();
    echo "\n🎉 SUCCESS! Data submission test completed successfully!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}