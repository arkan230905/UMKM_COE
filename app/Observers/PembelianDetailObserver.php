<?php

namespace App\Observers;

use App\Models\PembelianDetail;
use App\Services\RealTimeStockService;
use Illuminate\Support\Facades\Log;

class PembelianDetailObserver
{
    protected $stockService;

    public function __construct()
    {
        $this->stockService = new RealTimeStockService();
    }

    /**
     * Handle the PembelianDetail "created" event.
     * 
     * DISABLED: Stock updates are now handled directly in PembelianController
     * to prevent double counting. This observer was causing stock to be 
     * updated twice (once in controller, once here).
     */
    public function created(PembelianDetail $pembelianDetail): void
    {
        // DISABLED: Preventing double stock updates
        // Stock is now handled exclusively in PembelianController->store()
        Log::info("PembelianDetail created - stock handled by controller", [
            'pembelian_detail_id' => $pembelianDetail->id,
            'item_type' => $pembelianDetail->tipe_item ?? 'unknown',
            'qty' => $pembelianDetail->jumlah,
            'note' => 'Stock update handled by PembelianController to prevent double counting'
        ]);
        
        /* ORIGINAL CODE - DISABLED TO PREVENT DOUBLE COUNTING:
        try {
            // Only handle stock if pembelian is confirmed/completed
            if ($pembelianDetail->pembelian && $pembelianDetail->pembelian->status !== 'draft') {
                $this->stockService->handlePurchase($pembelianDetail);
                Log::info("Stock updated for purchase detail", [
                    'pembelian_detail_id' => $pembelianDetail->id,
                    'item_type' => $pembelianDetail->tipe_item,
                    'qty' => $pembelianDetail->jumlah
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to update stock for purchase detail", [
                'pembelian_detail_id' => $pembelianDetail->id,
                'error' => $e->getMessage()
            ]);
        }
        */
    }

    /**
     * Handle the PembelianDetail "updated" event.
     */
    public function updated(PembelianDetail $pembelianDetail): void
    {
        try {
            // Check if quantity or item changed
            if ($pembelianDetail->isDirty(['jumlah', 'bahan_baku_id', 'bahan_pendukung_id', 'harga_satuan'])) {
                // For simplicity, we'll reverse the old transaction and create a new one
                // In a more sophisticated system, you might want to calculate the difference
                
                if ($pembelianDetail->pembelian && $pembelianDetail->pembelian->status !== 'draft') {
                    // Note: This is a simplified approach
                    // In production, you might want to implement more sophisticated stock adjustment
                    Log::info("Purchase detail updated - stock recalculation may be needed", [
                        'pembelian_detail_id' => $pembelianDetail->id,
                        'changes' => $pembelianDetail->getDirty()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to handle purchase detail update", [
                'pembelian_detail_id' => $pembelianDetail->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the PembelianDetail "deleted" event.
     */
    public function deleted(PembelianDetail $pembelianDetail): void
    {
        try {
            // Note: In a production system, you might want to reverse the stock movement
            // For now, we'll just log the deletion
            Log::info("Purchase detail deleted - manual stock adjustment may be needed", [
                'pembelian_detail_id' => $pembelianDetail->id,
                'item_type' => $pembelianDetail->tipe_item,
                'qty' => $pembelianDetail->jumlah
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to handle purchase detail deletion", [
                'pembelian_detail_id' => $pembelianDetail->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}