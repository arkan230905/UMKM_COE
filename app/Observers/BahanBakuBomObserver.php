<?php

namespace App\Observers;

use App\Models\BahanBaku;
use App\Models\BomDetail;
use App\Models\Bom;
use Illuminate\Support\Facades\DB;

class BahanBakuBomObserver
{
    /**
     * Handle the BahanBaku "updated" event.
     */
    public function updated(BahanBaku $bahanBaku)
    {
        // Cek apakah harga_rata_rata berubah
        if ($bahanBaku->wasChanged('harga_rata_rata')) {
            $this->updateBomCosts($bahanBaku->id, 'bahan_baku');
        }
    }

    /**
     * Update BOM costs when material price changes
     */
    private function updateBomCosts($materialId, $materialType)
    {
        try {
            DB::beginTransaction();
            
            // Get all BOM details that use this material
            $bomDetails = BomDetail::where($materialType . '_id', $materialId)->get();
            
            foreach ($bomDetails as $detail) {
                // Get the latest price
                $hargaTerbaru = $materialType === 'bahan_baku' 
                    ? $detail->bahanBaku->harga_rata_rata ?? 0
                    : $detail->bahanPendukung->harga_satuan ?? 0;
                
                // Update detail price
                $detail->update([
                    'harga_satuan' => $hargaTerbaru,
                    'subtotal' => $detail->jumlah * $hargaTerbaru
                ]);
                
                // Update BOM total cost
                $this->updateBomTotal($detail->bom_id);
            }
            
            DB::commit();
            
            \Log::info("BOM costs updated for {$materialType} ID: {$materialId}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Failed to update BOM costs: " . $e->getMessage());
        }
    }

    /**
     * Update BOM total cost
     */
    private function updateBomTotal($bomId)
    {
        $bom = Bom::find($bomId);
        $totalBiaya = $bom->details()->sum('subtotal');
        
        $bom->update(['total_biaya' => $totalBiaya]);
        
        // Update product selling price (optional - bisa ditambahkan logika bisnis)
        $this->updateProductPrice($bom->produk_id, $totalBiaya);
    }

    /**
     * Update product selling price based on BOM cost
     */
    private function updateProductPrice($produkId, $totalBiaya)
    {
        $produk = \App\Models\Produk::find($produkId);
        
        // Logika bisnis: harga jual = biaya BOM + margin (misal 30%)
        $margin = 0.3; // 30% margin
        $hargaJualBaru = $totalBiaya * (1 + $margin);
        
        $produk->update(['harga_jual' => $hargaJualBaru]);
        
        \Log::info("Product price updated for Produk ID: {$produkId}, New Price: {$hargaJualBaru}");
    }
}
