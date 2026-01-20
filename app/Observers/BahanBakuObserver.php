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
}
