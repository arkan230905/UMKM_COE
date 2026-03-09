<?php

namespace App\Observers;

use App\Models\BahanBaku;
use App\Models\BomDetail;
use App\Models\BomJobCosting;
use App\Models\Produk;
use App\Support\UnitConverter;
use Illuminate\Support\Facades\Log;

/**
 * Observer untuk auto-update biaya bahan dan BOM saat harga bahan baku berubah
 */
class BahanBakuObserver
{
    /**
     * Handle the BahanBaku "updated" event.
     * Trigger saat harga_satuan atau harga_rata_rata berubah
     */
    public function updated(BahanBaku $bahanBaku)
    {
        // Cek apakah harga berubah
        if ($bahanBaku->isDirty('harga_satuan') || $bahanBaku->isDirty('harga_rata_rata')) {
            $hargaLama = $bahanBaku->getOriginal('harga_satuan');
            $hargaBaru = $bahanBaku->harga_satuan;
            
            Log::info('🔄 Harga Bahan Baku Berubah - Auto Update Triggered', [
                'bahan_baku_id' => $bahanBaku->id,
                'nama_bahan' => $bahanBaku->nama_bahan,
                'harga_lama' => $hargaLama,
                'harga_baru' => $hargaBaru
            ]);
            
            // Update semua BOM yang menggunakan bahan baku ini
            $this->updateBomDetails($bahanBaku);
        }
    }
    
    /**
     * Update semua BOM Detail yang menggunakan bahan baku ini
     */
    private function updateBomDetails(BahanBaku $bahanBaku)
    {
        $converter = new UnitConverter();
        $updatedProducts = [];
        
        // Cari semua BOM Detail yang menggunakan bahan baku ini
        $bomDetails = BomDetail::where('bahan_baku_id', $bahanBaku->id)
            ->with(['bom.produk'])
            ->get();
        
        foreach ($bomDetails as $detail) {
            // Update harga per satuan di BOM Detail
            $detail->harga_per_satuan = $bahanBaku->harga_satuan;
            
            // Recalculate total harga
            try {
                $satuanBase = is_object($bahanBaku->satuan) 
                    ? $bahanBaku->satuan->nama 
                    : ($bahanBaku->satuan ?? 'unit');
                
                $qtyBase = $converter->convert(
                    (float) $detail->jumlah,
                    $detail->satuan ?: $satuanBase,
                    $satuanBase
                );
                
                $detail->total_harga = $bahanBaku->harga_satuan * $qtyBase;
                $detail->save();
                
                Log::info('✅ BOM Detail Updated', [
                    'bom_detail_id' => $detail->id,
                    'produk' => $detail->bom->produk->nama_produk ?? 'N/A',
                    'jumlah' => $detail->jumlah,
                    'satuan' => $detail->satuan,
                    'harga_baru' => $bahanBaku->harga_satuan,
                    'total_harga' => $detail->total_harga
                ]);
                
                // Tandai produk untuk recalculate
                if ($detail->bom && $detail->bom->produk) {
                    $updatedProducts[$detail->bom->produk->id] = $detail->bom->produk;
                }
                
            } catch (\Exception $e) {
                Log::error('❌ Error updating BOM Detail', [
                    'bom_detail_id' => $detail->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Recalculate biaya bahan untuk setiap produk yang terpengaruh
        foreach ($updatedProducts as $produk) {
            $this->recalculateProductBiayaBahan($produk);
        }
        
        Log::info('🎯 Auto Update Complete', [
            'bahan_baku' => $bahanBaku->nama_bahan,
            'affected_products' => count($updatedProducts),
            'product_names' => array_map(fn($p) => $p->nama_produk, $updatedProducts)
        ]);
    }
    
    /**
     * Recalculate total biaya bahan untuk produk
     */
    private function recalculateProductBiayaBahan(Produk $produk)
    {
        $converter = new UnitConverter();
        $totalBiayaBahan = 0;
        
        // 1. Hitung biaya dari Bahan Baku (BomDetail)
        $bomDetails = BomDetail::with('bahanBaku.satuan')
            ->where('bom_id', function($query) use ($produk) {
                $query->select('id')->from('boms')->where('produk_id', $produk->id);
            })
            ->get();
        
        foreach ($bomDetails as $detail) {
            if (!$detail->bahanBaku) continue;
            
            $satuanBase = is_object($detail->bahanBaku->satuan) 
                ? $detail->bahanBaku->satuan->nama 
                : ($detail->bahanBaku->satuan ?? 'unit');
            
            try {
                $qtyBase = $converter->convert(
                    (float) $detail->jumlah,
                    $detail->satuan ?: $satuanBase,
                    $satuanBase
                );
                
                $totalBiayaBahan += $detail->bahanBaku->harga_satuan * $qtyBase;
            } catch (\Exception $e) {
                Log::error('Error calculating bahan baku cost', [
                    'produk_id' => $produk->id,
                    'detail_id' => $detail->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // 2. Hitung biaya dari Bahan Pendukung (BomJobBahanPendukung)
        $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)->first();
        
        if ($bomJobCosting) {
            $bomJobBahanPendukung = \App\Models\BomJobBahanPendukung::with('bahanPendukung.satuan')
                ->where('bom_job_costing_id', $bomJobCosting->id)
                ->get();
            
            foreach ($bomJobBahanPendukung as $jobPendukung) {
                if (!$jobPendukung->bahanPendukung) continue;
                
                $satuanBase = is_object($jobPendukung->bahanPendukung->satuan) 
                    ? $jobPendukung->bahanPendukung->satuan->nama 
                    : ($jobPendukung->bahanPendukung->satuan ?? 'unit');
                
                try {
                    $qtyBase = $converter->convert(
                        (float) $jobPendukung->jumlah,
                        $jobPendukung->satuan ?: $satuanBase,
                        $satuanBase
                    );
                    
                    $totalBiayaBahan += $jobPendukung->bahanPendukung->harga_satuan * $qtyBase;
                } catch (\Exception $e) {
                    Log::error('Error calculating bahan pendukung cost', [
                        'produk_id' => $produk->id,
                        'job_pendukung_id' => $jobPendukung->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // 3. Update BomJobCosting total_bbb
            $bomJobCosting->recalculate();
            
            Log::info('🔄 BomJobCosting Recalculated', [
                'bom_job_costing_id' => $bomJobCosting->id,
                'produk' => $produk->nama_produk,
                'total_bbb' => $bomJobCosting->total_bbb,
                'total_hpp' => $bomJobCosting->total_hpp
            ]);
        }
        
        // 4. Update biaya bahan dan harga_bom di produk
        $produk->update([
            'biaya_bahan' => $totalBiayaBahan
        ]);
        
        // Update harga_bom dengan HPP lengkap (BBB + Bahan Pendukung + BTKL + BOP)
        if ($bomJobCosting) {
            $produk->update([
                'harga_bom' => $bomJobCosting->total_hpp  // HPP lengkap
            ]);
            
            Log::info('💰 Harga BOM Updated with HPP', [
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'biaya_bahan' => $totalBiayaBahan,
                'harga_bom' => $bomJobCosting->total_hpp
            ]);
        } else {
            Log::info('💰 Biaya Bahan Updated', [
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'biaya_bahan' => $totalBiayaBahan
            ]);
        }
    }
    
    /**
     * Handle BahanBaku "deleting" event.
     * Memberikan keterangan dan menangani BOM yang terpengaruh saat bahan baku akan dihapus
     */
    public function deleting(BahanBaku $bahanBaku)
    {
        Log::info('🗑️ Bahan Baku Akan Dihapus - Processing Affected BOMs', [
            'bahan_baku_id' => $bahanBaku->id,
            'nama_bahan' => $bahanBaku->nama_bahan,
            'harga_terakhir' => $bahanBaku->harga_satuan,
            'satuan' => $bahanBaku->satuan->nama ?? 'Unknown'
        ]);
        
        // Proses semua BOM yang menggunakan bahan baku ini
        $this->handleDeletedBahanInBOMs($bahanBaku);
    }
    
    /**
     * Handle BahanBaku "restored" event (untuk soft delete recovery)
     */
    public function restored(BahanBaku $bahanBaku)
    {
        Log::info('♻️ Bahan Baku Dikembalikan - Restoring BOM Data', [
            'bahan_baku_id' => $bahanBaku->id,
            'nama_bahan' => $bahanBaku->nama_bahan,
            'harga' => $bahanBaku->harga_satuan
        ]);
        
        // Kembalikan data BOM yang terpengaruh
        $this->restoreBahanInBOMs($bahanBaku);
    }
    
    /**
     * Proses BOM yang terpengaruh saat bahan baku dihapus
     */
    private function handleDeletedBahanInBOMs(BahanBaku $bahanBaku)
    {
        $affectedProducts = [];
        
        // 1. Proses BomDetail (legacy BOM)
        $bomDetails = BomDetail::where('bahan_baku_id', $bahanBaku->id)
            ->with(['bom.produk'])
            ->get();
        
        foreach ($bomDetails as $detail) {
            if ($detail->bom && $detail->bom->produk) {
                $this->updateBOMDetailWithDeletedNote($detail, $bahanBaku);
                $affectedProducts[$detail->bom->produk->id] = $detail->bom->produk;
            }
        }
        
        // 2. Proses BomJobBBB (primary BOM)
        $bomJobBBBs = \App\Models\BomJobBBB::where('bahan_baku_id', $bahanBaku->id)
            ->with(['bomJobCosting.produk'])
            ->get();
        
        foreach ($bomJobBBBs as $bbb) {
            if ($bbb->bomJobCosting && $bbb->bomJobCosting->produk) {
                $this->updateBomJobBBBWithDeletedNote($bbb, $bahanBaku);
                $affectedProducts[$bbb->bomJobCosting->produk->id] = $bbb->bomJobCosting->produk;
            }
        }
        
        // 3. Recalculate biaya untuk semua produk yang terpengaruh
        foreach ($affectedProducts as $produk) {
            $this->recalculateProductBiayaAfterDeletion($produk, $bahanBaku);
        }
        
        Log::info('📋 BOM Processing Complete', [
            'bahan_baku' => $bahanBaku->nama_bahan,
            'affected_products' => count($affectedProducts),
            'product_names' => array_map(fn($p) => $p->nama_produk, $affectedProducts)
        ]);
    }
    
    /**
     * Update BomDetail dengan catatan penghapusan
     */
    private function updateBOMDetailWithDeletedNote($detail, BahanBaku $bahanBaku)
    {
        // Load relasi satuan untuk mendapatkan nama
        $bahanBaku->load('satuan');
        
        // Update detail dengan catatan bahwa bahan telah dihapus
        $detail->update([
            'nama_bahan_terhapus' => $bahanBaku->nama_bahan,
            'harga_terakhir' => $bahanBaku->harga_satuan,
            'satuan_terakhir' => $bahanBaku->satuan->nama ?? $bahanBaku->satuan,
            'catatan_hapus' => 'Bahan baku dihapus pada ' . now()->format('Y-m-d H:i:s'),
            'harga_per_satuan' => 0, // Set ke 0 agar tidak mempengaruhi perhitungan
            'total_harga' => 0 // Set ke 0 agar tidak mempengaruhi perhitungan
        ]);
        
        Log::info('📝 BOM Detail Updated with Deletion Note', [
            'bom_detail_id' => $detail->id,
            'produk' => $detail->bom->produk->nama_produk ?? 'N/A',
            'bahan_terhapus' => $bahanBaku->nama_bahan,
            'harga_terakhir' => $bahanBaku->harga_satuan,
            'satuan_terakhir' => $bahanBaku->satuan->nama ?? $bahanBaku->satuan
        ]);
    }
    
    /**
     * Update BomJobBBB dengan catatan penghapusan
     */
    private function updateBomJobBBBWithDeletedNote($bbb, BahanBaku $bahanBaku)
    {
        // Debug: Cek data bahan baku
        Log::info('🔍 Debug BahanBaku Data', [
            'bahan_baku_id' => $bahanBaku->id,
            'nama_bahan' => $bahanBaku->nama_bahan,
            'harga_satuan' => $bahanBaku->harga_satuan,
            'satuan_id' => $bahanBaku->satuan_id,
            'satuan_relation' => $bahanBaku->satuan,
            'satuan_nama' => $bahanBaku->satuan->nama ?? 'NULL'
        ]);
        
        // Load relasi satuan untuk mendapatkan nama
        $bahanBaku->load('satuan');
        
        // Debug: Cek setelah load
        Log::info('🔍 Debug BahanBaku After Load', [
            'satuan_relation' => $bahanBaku->satuan,
            'satuan_nama' => $bahanBaku->satuan->nama ?? 'NULL'
        ]);
        
        // Update BBB dengan catatan bahwa bahan telah dihapus
        $updateData = [
            'nama_bahan_terhapus' => $bahanBaku->nama_bahan,
            'harga_terakhir' => $bahanBaku->harga_satuan,
            'satuan_terakhir' => $bahanBaku->satuan->nama ?? $bahanBaku->satuan,
            'catatan_hapus' => 'Bahan baku dihapus pada ' . now()->format('Y-m-d H:i:s'),
            'harga_satuan' => 0, // Set ke 0 agar tidak mempengaruhi perhitungan
            'subtotal' => 0 // Set ke 0 agar tidak mempengaruhi perhitungan
        ];
        
        Log::info('🔍 Debug Update Data', $updateData);
        
        $bbb->update($updateData);
        
        Log::info('📝 BomJobBBB Updated with Deletion Note', [
            'bom_job_bbb_id' => $bbb->id,
            'produk' => $bbb->bomJobCosting->produk->nama_produk ?? 'N/A',
            'bahan_terhapus' => $bahanBaku->nama_bahan,
            'harga_terakhir' => $bahanBaku->harga_satuan,
            'satuan_terakhir' => $bahanBaku->satuan->nama ?? $bahanBaku->satuan
        ]);
    }
    
    /**
     * Recalculate biaya produk setelah penghapusan bahan
     */
    private function recalculateProductBiayaAfterDeletion(Produk $produk, BahanBaku $deletedBahan)
    {
        $converter = new UnitConverter();
        $totalBiayaBahan = 0;
        
        // 1. Hitung ulang biaya dari Bahan Baku yang masih ada
        $bomDetails = BomDetail::with('bahanBaku.satuan')
            ->where('bom_id', function($query) use ($produk) {
                $query->select('id')->from('boms')->where('produk_id', $produk->id);
            })
            ->where('harga_per_satuan', '>', 0) // Hanya yang masih memiliki harga
            ->get();
        
        foreach ($bomDetails as $detail) {
            if (!$detail->bahanBaku) continue;
            
            $satuanBase = is_object($detail->bahanBaku->satuan) 
                ? $detail->bahanBaku->satuan->nama 
                : ($detail->bahanBaku->satuan ?? 'unit');
            
            try {
                $qtyBase = $converter->convert(
                    (float) $detail->jumlah,
                    $detail->satuan ?: $satuanBase,
                    $satuanBase
                );
                
                $totalBiayaBahan += $detail->bahanBaku->harga_satuan * $qtyBase;
            } catch (\Exception $e) {
                Log::error('Error calculating remaining bahan baku cost', [
                    'produk_id' => $produk->id,
                    'detail_id' => $detail->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // 2. Hitung biaya dari BomJobBBB yang masih ada
        $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)->first();
        
        if ($bomJobCosting) {
            $bomJobBBBs = \App\Models\BomJobBBB::with('bahanBaku.satuan')
                ->where('bom_job_costing_id', $bomJobCosting->id)
                ->where('harga_satuan', '>', 0) // Hanya yang masih memiliki harga
                ->get();
            
            foreach ($bomJobBBBs as $bbb) {
                if (!$bbb->bahanBaku) continue;
                
                $satuanBase = is_object($bbb->bahanBaku->satuan) 
                    ? $bbb->bahanBaku->satuan->nama 
                    : ($bbb->bahanBaku->satuan ?? 'unit');
                
                try {
                    $qtyBase = $converter->convert(
                        (float) $bbb->jumlah,
                        $bbb->satuan ?: $satuanBase,
                        $satuanBase
                    );
                    
                    $totalBiayaBahan += $bbb->bahanBaku->harga_satuan * $qtyBase;
                } catch (\Exception $e) {
                    Log::error('Error calculating remaining BBB cost', [
                        'produk_id' => $produk->id,
                        'bbb_id' => $bbb->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // 3. Hitung biaya dari Bahan Pendukung
            $bomJobBahanPendukung = \App\Models\BomJobBahanPendukung::with('bahanPendukung.satuan')
                ->where('bom_job_costing_id', $bomJobCosting->id)
                ->where('harga_satuan', '>', 0) // Hanya yang masih memiliki harga
                ->get();
            
            foreach ($bomJobBahanPendukung as $jobPendukung) {
                if (!$jobPendukung->bahanPendukung) continue;
                
                $satuanBase = is_object($jobPendukung->bahanPendukung->satuan) 
                    ? $jobPendukung->bahanPendukung->satuan->nama 
                    : ($jobPendukung->bahanPendukung->satuan ?? 'unit');
                
                try {
                    $qtyBase = $converter->convert(
                        (float) $jobPendukung->jumlah,
                        $jobPendukung->satuan ?: $satuanBase,
                        $satuanBase
                    );
                    
                    $totalBiayaBahan += $jobPendukung->bahanPendukung->harga_satuan * $qtyBase;
                } catch (\Exception $e) {
                    Log::error('Error calculating remaining bahan pendukung cost', [
                        'produk_id' => $produk->id,
                        'job_pendukung_id' => $jobPendukung->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // 4. Update BomJobCosting
            $bomJobCosting->recalculate();
        }
        
        // 5. Update biaya bahan di produk
        $produk->update([
            'biaya_bahan' => $totalBiayaBahan
        ]);
        
        // 6. Update harga_bom dengan HPP lengkap
        if ($bomJobCosting) {
            $produk->update([
                'harga_bom' => $bomJobCosting->total_hpp
            ]);
            
            Log::info('💰 Harga BOM Updated After Deletion', [
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'bahan_dihapus' => $deletedBahan->nama_bahan,
                'biaya_bahan_baru' => $totalBiayaBahan,
                'harga_bom' => $bomJobCosting->total_hpp
            ]);
        }
        
        Log::info('✅ Product Biaya Recalculated After Deletion', [
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama_produk,
            'biaya_bahan' => $totalBiayaBahan
        ]);
    }
    
    /**
     * Kembalikan data BOM saat bahan baku di-restore
     */
    private function restoreBahanInBOMs(BahanBaku $bahanBaku)
    {
        // Implementasi restore logic (jika diperlukan)
        Log::info('🔄 Restore BOM data for restored bahan', [
            'bahan_baku_id' => $bahanBaku->id,
            'nama_bahan' => $bahanBaku->nama_bahan
        ]);
    }
}
