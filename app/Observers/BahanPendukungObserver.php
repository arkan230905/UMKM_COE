<?php

namespace App\Observers;

use App\Models\BahanPendukung;
use App\Models\BomJobBahanPendukung;
use App\Models\BomJobCosting;
use App\Models\BomDetail;
use App\Models\Produk;
use App\Support\UnitConverter;
use Illuminate\Support\Facades\Log;

/**
 * Observer untuk auto-update biaya bahan dan BOM saat harga bahan pendukung berubah
 */
class BahanPendukungObserver
{
    /**
     * Handle the BahanPendukung "updated" event.
     */
    public function updated(BahanPendukung $bahanPendukung)
    {
        // Cek apakah harga berubah
        if ($bahanPendukung->isDirty('harga_satuan')) {
            $hargaLama = $bahanPendukung->getOriginal('harga_satuan');
            $hargaBaru = $bahanPendukung->harga_satuan;
            
            Log::info('🔄 Harga Bahan Pendukung Berubah - Auto Update Triggered', [
                'bahan_pendukung_id' => $bahanPendukung->id,
                'nama_bahan' => $bahanPendukung->nama_bahan,
                'harga_lama' => $hargaLama,
                'harga_baru' => $hargaBaru
            ]);
            
            // Update semua BOM yang menggunakan bahan pendukung ini
            $this->updateBomJobBahanPendukung($bahanPendukung);
        }
    }
    
    /**
     * Update semua BomJobBahanPendukung yang menggunakan bahan pendukung ini
     */
    private function updateBomJobBahanPendukung(BahanPendukung $bahanPendukung)
    {
        $converter = new UnitConverter();
        $updatedProducts = [];
        
        // Cari semua BomJobBahanPendukung yang menggunakan bahan pendukung ini
        $bomJobBahanPendukungs = BomJobBahanPendukung::where('bahan_pendukung_id', $bahanPendukung->id)
            ->with(['bomJobCosting.produk'])
            ->get();
        
        foreach ($bomJobBahanPendukungs as $jobPendukung) {
            // Update harga satuan
            $jobPendukung->harga_satuan = $bahanPendukung->harga_satuan;
            
            // Recalculate subtotal
            try {
                $satuanBase = is_object($bahanPendukung->satuan) 
                    ? $bahanPendukung->satuan->nama 
                    : ($bahanPendukung->satuan ?? 'unit');
                
                $qtyBase = $converter->convert(
                    (float) $jobPendukung->jumlah,
                    $jobPendukung->satuan ?: $satuanBase,
                    $satuanBase
                );
                
                $jobPendukung->subtotal = $bahanPendukung->harga_satuan * $qtyBase;
                $jobPendukung->save();
                
                Log::info('✅ BomJobBahanPendukung Updated', [
                    'job_pendukung_id' => $jobPendukung->id,
                    'produk' => $jobPendukung->bomJobCosting->produk->nama_produk ?? 'N/A',
                    'jumlah' => $jobPendukung->jumlah,
                    'satuan' => $jobPendukung->satuan,
                    'harga_baru' => $bahanPendukung->harga_satuan,
                    'subtotal' => $jobPendukung->subtotal
                ]);
                
                // Tandai produk untuk recalculate
                if ($jobPendukung->bomJobCosting && $jobPendukung->bomJobCosting->produk) {
                    $updatedProducts[$jobPendukung->bomJobCosting->produk->id] = $jobPendukung->bomJobCosting->produk;
                }
                
            } catch (\Exception $e) {
                Log::error('❌ Error updating BomJobBahanPendukung', [
                    'job_pendukung_id' => $jobPendukung->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Recalculate biaya bahan untuk setiap produk yang terpengaruh
        foreach ($updatedProducts as $produk) {
            $this->recalculateProductBiayaBahan($produk);
        }
        
        Log::info('🎯 Auto Update Complete', [
            'bahan_pendukung' => $bahanPendukung->nama_bahan,
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
            $bomJobBahanPendukung = BomJobBahanPendukung::with('bahanPendukung.satuan')
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
            
            // Recalculate BomJobCosting
            $bomJobCosting->recalculate();
            
            Log::info('🔄 BomJobCosting Recalculated', [
                'bom_job_costing_id' => $bomJobCosting->id,
                'produk' => $produk->nama_produk,
                'total_bahan_pendukung' => $bomJobCosting->total_bahan_pendukung,
                'total_hpp' => $bomJobCosting->total_hpp
            ]);
        }
        
        // 3. Update biaya bahan dan harga_bom di produk
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
