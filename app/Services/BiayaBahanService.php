<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\Bom;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BiayaBahanService
{
    /**
     * Update BOM saat harga bahan baku berubah
     */
    public function updateBiayaBahanOnPriceChange($bahanBakuId, $hargaBaru)
    {
        try {
            DB::beginTransaction();
            
            $bahanBaku = BahanBaku::findOrFail($bahanBakuId);
            $hargaLama = $bahanBaku->harga_satuan;
            
            // Update BOM yang menggunakan bahan baku ini
            $updatedCount = 0;
            
            // Cari semua produk yang menggunakan bahan baku ini dalam BOM
            $boms = Bom::whereHas('details', function($query) use ($bahanBakuId) {
                $query->where('bahan_baku_id', $bahanBakuId);
            })->with('produk')->get();
            
            foreach ($boms as $bom) {
                // Update total biaya bahan di BOM
                $this->recalculateBomCost($bom->id);
                $updatedCount++;
                
                Log::info('BOM updated due to price change', [
                    'bom_id' => $bom->id,
                    'produk_id' => $bom->produk_id,
                    'produk_nama' => $bom->produk->nama_produk,
                    'bahan_baku_id' => $bahanBakuId,
                    'harga_lama' => $hargaLama,
                    'harga_baru' => $hargaBaru
                ]);
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'BOM berhasil diperbarui',
                'updated_count' => $updatedCount
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating BOM on price change: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal memperbarui BOM: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Recalculate BOM cost
     */
    private function recalculateBomCost($bomId)
    {
        $bom = Bom::find($bomId);
        if (!$bom) return;
        
        // Hitung ulang total biaya bahan
        $totalBiayaBahan = $this->calculateTotalBiayaBahan($bom->produk_id);
        
        // Update BOM
        $bom->update([
            'total_biaya' => $totalBiayaBahan
        ]);
        
        // Update produk
        $produk = Produk::find($bom->produk_id);
        if ($produk) {
            $totalHpp = $totalBiayaBahan + ($bom->total_bbb ?? 0) + ($bom->total_hpp ?? 0);
            
            $produk->update([
                'biaya_bahan' => $totalBiayaBahan,
                'harga_bom' => $totalHpp
            ]);
        }
    }
    
    /**
     * Hitung total biaya bahan untuk produk
     */
    private function calculateTotalBiayaBahan($produkId)
    {
        $totalBiaya = 0;
        
        // Ambil semua BOM untuk produk ini
        $bom = Bom::where('produk_id', $produkId)->first();
        if (!$bom) return 0;
        
        // Hitung dari BOM Details
        if (class_exists('App\Models\BomDetail')) {
            $bomDetails = \App\Models\BomDetail::where('bom_id', $bom->id)
                ->join('bahan_bakus', 'bom_details.bahan_baku_id', '=', 'bahan_bakus.id')
                ->select('bom_details.*', 'bahan_bakus.harga_satuan')
                ->get();
            
            foreach ($bomDetails as $detail) {
                if ($detail->bahan_baku) {
                    $totalBiaya += $detail->bahan_baku->harga_satuan * $detail->jumlah;
                }
            }
        } else {
            // Fallback: gunakan total_biaya dari BOM
            $totalBiaya = $bom->total_biaya ?? 0;
        }
        
        return $totalBiaya;
    }
    
    /**
     * Rekapitulasi perubahan harga
     */
    public function getHargaChangeReport($bahanBakuId)
    {
        $bahanBaku = BahanBaku::find($bahanBakuId);
        
        if (!$bahanBaku) {
            return null;
        }
        
        $report = [
            'bahan_baku' => $bahanBaku,
            'harga_satuan' => $bahanBaku->harga_satuan,
            'boms' => [],
            'total_dampak_produk' => 0
        ];
        
        // Cari BOM yang menggunakan bahan baku ini
        $boms = Bom::whereHas('details', function($query) use ($bahanBakuId) {
            $query->where('bahan_baku_id', $bahanBakuId);
        })->with('produk')->get();
        
        foreach ($boms as $bom) {
            $report['boms'][] = [
                'id' => $bom->id,
                'produk_id' => $bom->produk_id,
                'produk_nama' => $bom->produk->nama_produk,
                'total_biaya' => $bom->total_biaya,
                'harga_bom' => $bom->produk->harga_bom
            ];
        }
        
        $report['total_dampak_produk'] = count($boms);
        
        return $report;
    }
}
