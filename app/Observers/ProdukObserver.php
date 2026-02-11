<?php

namespace App\Observers;

use App\Models\Produk;
use App\Models\BomJobCosting;
use App\Services\BomSyncService;
use Illuminate\Support\Facades\Log;

class ProdukObserver
{
    /**
     * Handle the Produk "created" event.
     * Automatically create BomJobCosting and populate BTKL/BOP data
     */
    public function created(Produk $produk): void
    {
        try {
            Log::info("ProdukObserver: New product created - {$produk->nama_produk}");
            
            // Create BomJobCosting for this product
            $bomJobCosting = BomJobCosting::create([
                'produk_id' => $produk->id,
                'jumlah_produk' => 1,
                'total_bbb' => 0,
                'total_btkl' => 0,
                'total_bahan_pendukung' => 0,
                'total_bop' => 0,
                'total_hpp' => 0,
                'hpp_per_unit' => 0
            ]);
            
            Log::info("ProdukObserver: Created BomJobCosting for {$produk->nama_produk}");
            
            // Auto-populate BTKL and BOP data
            BomSyncService::syncBTKLForBom($bomJobCosting);
            BomSyncService::syncBOPForBom($bomJobCosting);
            
            Log::info("ProdukObserver: Auto-populated BTKL and BOP for {$produk->nama_produk}");
            
        } catch (\Exception $e) {
            Log::error("ProdukObserver: Failed to auto-populate BOM for {$produk->nama_produk} - " . $e->getMessage());
        }
    }

    /**
     * Handle the Produk "updated" event.
     */
    public function updated(Produk $produk): void
    {
        // Skip recalculation if only pricing fields were updated to prevent infinite loop
        $pricingFields = ['harga_bom', 'harga_jual', 'biaya_bahan', 'margin_percent'];
        $changedFields = array_keys($produk->getDirty());
        
        // If only pricing fields changed, skip recalculation to prevent infinite loop
        $nonPricingChanges = array_diff($changedFields, $pricingFields);
        
        if (empty($nonPricingChanges)) {
            Log::info("ProdukObserver: Skipping recalculation for {$produk->nama_produk} - only pricing fields changed");
            return;
        }
        
        // Optional: Re-sync BOM data if product details change
        try {
            $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)->first();
            
            if ($bomJobCosting) {
                // Recalculate to ensure product pricing is updated
                $bomJobCosting->recalculate();
                Log::info("ProdukObserver: Recalculated BOM for updated product - {$produk->nama_produk}");
            }
            
        } catch (\Exception $e) {
            Log::error("ProdukObserver: Failed to recalculate BOM for {$produk->nama_produk} - " . $e->getMessage());
        }
    }

    /**
     * Handle the Produk "deleted" event.
     */
    public function deleted(Produk $produk): void
    {
        try {
            // BomJobCosting will be automatically deleted due to foreign key cascade
            Log::info("ProdukObserver: Product deleted - {$produk->nama_produk}");
            
        } catch (\Exception $e) {
            Log::error("ProdukObserver: Error during product deletion - " . $e->getMessage());
        }
    }
}