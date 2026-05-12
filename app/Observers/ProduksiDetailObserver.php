<?php

namespace App\Observers;

use App\Models\ProduksiDetail;
use App\Services\RealTimeStockService;
use Illuminate\Support\Facades\Log;

class ProduksiDetailObserver
{
    protected $stockService;

    public function __construct()
    {
        $this->stockService = new RealTimeStockService();
    }

    /**
     * Handle the ProduksiDetail "created" event.
     */
    public function created(ProduksiDetail $produksiDetail): void
    {
        try {
            // Only handle stock if produksi is confirmed/completed
            if ($produksiDetail->produksi && $produksiDetail->produksi->status !== 'draft') {
                $this->stockService->handleProduction($produksiDetail);
                Log::info("Stock updated for production", [
                    'produksi_detail_id' => $produksiDetail->id,
                    'produk_id' => $produksiDetail->produk_id,
                    'qty_produced' => $produksiDetail->jumlah_produksi
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to update stock for production", [
                'produksi_detail_id' => $produksiDetail->id,
                'error' => $e->getMessage()
            ]);
            
            // Don't throw exception to avoid breaking production creation
            // But you might want to notify administrators
        }
    }

    /**
     * Handle the ProduksiDetail "updated" event.
     */
    public function updated(ProduksiDetail $produksiDetail): void
    {
        try {
            // Check if production quantity changed
            if ($produksiDetail->isDirty(['jumlah_produksi', 'produk_id'])) {
                if ($produksiDetail->produksi && $produksiDetail->produksi->status !== 'draft') {
                    Log::info("Production detail updated - stock recalculation may be needed", [
                        'produksi_detail_id' => $produksiDetail->id,
                        'changes' => $produksiDetail->getDirty()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to handle production detail update", [
                'produksi_detail_id' => $produksiDetail->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the ProduksiDetail "deleted" event.
     */
    public function deleted(ProduksiDetail $produksiDetail): void
    {
        try {
            Log::info("Production detail deleted - manual stock adjustment may be needed", [
                'produksi_detail_id' => $produksiDetail->id,
                'produk_id' => $produksiDetail->produk_id,
                'qty_produced' => $produksiDetail->jumlah_produksi
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to handle production detail deletion", [
                'produksi_detail_id' => $produksiDetail->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}