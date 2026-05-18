<?php

namespace App\Observers;

use App\Models\Produk;
// ✅ PERBAIKAN: Disable import BomJobCosting dan BomSyncService karena tabel bom_job_costings tidak ada
// use App\Models\BomJobCosting;
// use App\Services\BomSyncService;
use Illuminate\Support\Facades\Log;

class ProdukObserver
{
    /**
     * Handle the Produk "created" event.
     * DISABLED: BomJobCosting should only be created manually via HPP form
     */
    public function created(Produk $produk): void
    {
        try {
            Log::info("ProdukObserver: New product created - {$produk->nama_produk}");
            
            // DISABLED: Do not auto-create BomJobCosting
            // BomJobCosting should only be created manually via HPP form
            Log::info("ProdukObserver: BomJobCosting auto-creation DISABLED for {$produk->nama_produk}");
            
        } catch (\Exception $e) {
            Log::error("ProdukObserver: Error in created event for {$produk->nama_produk} - " . $e->getMessage());
        }
    }

    /**
     * Handle the Produk "updated" event.
     */
    public function updated(Produk $produk): void
    {
        // Skip recalculation if only pricing fields were updated to prevent infinite loop
        $pricingFields = ['harga_bom', 'harga_jual', 'biaya_bahan', 'margin_percent', 'harga_pokok'];
        $systemFields = ['updated_at', 'created_at'];
        $changedFields = array_keys($produk->getDirty());
        
        Log::info("ProdukObserver: Updated event for {$produk->nama_produk}");
        Log::info("Changed fields: " . json_encode($changedFields));
        Log::info("Pricing fields: " . json_encode($pricingFields));
        
        // Remove system fields from changed fields
        $relevantChanges = array_diff($changedFields, $systemFields);
        
        // If only pricing fields changed (excluding system fields), skip recalculation
        $nonPricingChanges = array_diff($relevantChanges, $pricingFields);
        
        Log::info("Relevant changes: " . json_encode(array_values($relevantChanges)));
        Log::info("Non-pricing changes: " . json_encode(array_values($nonPricingChanges)));
        
        if (empty($nonPricingChanges)) {
            Log::info("ProdukObserver: Skipping recalculation for {$produk->nama_produk} - only pricing fields changed");
            return;
        }
        
        // Optional: Re-sync BOM data if product details change
        // BomJobCosting class disabled - skip recalculation
        Log::info("ProdukObserver: Product updated - {$produk->nama_produk}, BOM recalculation skipped");
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