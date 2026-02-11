<?php

namespace App\Observers;

use App\Models\PenjualanDetail;
use App\Services\RealTimeStockService;
use Illuminate\Support\Facades\Log;

class PenjualanDetailObserver
{
    protected $stockService;

    public function __construct()
    {
        $this->stockService = new RealTimeStockService();
    }

    /**
     * Handle the PenjualanDetail "created" event.
     */
    public function created(PenjualanDetail $penjualanDetail): void
    {
        try {
            // Only handle stock if penjualan is confirmed/completed
            if ($penjualanDetail->penjualan && $penjualanDetail->penjualan->status !== 'draft') {
                $this->stockService->handleSale($penjualanDetail);
                Log::info("Stock updated for sale", [
                    'penjualan_detail_id' => $penjualanDetail->id,
                    'produk_id' => $penjualanDetail->produk_id,
                    'qty_sold' => $penjualanDetail->jumlah
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to update stock for sale", [
                'penjualan_detail_id' => $penjualanDetail->id,
                'error' => $e->getMessage()
            ]);
            
            // You might want to prevent the sale if stock is insufficient
            // throw $e;
        }
    }

    /**
     * Handle the PenjualanDetail "updated" event.
     */
    public function updated(PenjualanDetail $penjualanDetail): void
    {
        try {
            // Check if quantity or product changed
            if ($penjualanDetail->isDirty(['jumlah', 'produk_id'])) {
                if ($penjualanDetail->penjualan && $penjualanDetail->penjualan->status !== 'draft') {
                    Log::info("Sale detail updated - stock recalculation may be needed", [
                        'penjualan_detail_id' => $penjualanDetail->id,
                        'changes' => $penjualanDetail->getDirty()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to handle sale detail update", [
                'penjualan_detail_id' => $penjualanDetail->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the PenjualanDetail "deleted" event.
     */
    public function deleted(PenjualanDetail $penjualanDetail): void
    {
        try {
            Log::info("Sale detail deleted - manual stock adjustment may be needed", [
                'penjualan_detail_id' => $penjualanDetail->id,
                'produk_id' => $penjualanDetail->produk_id,
                'qty_sold' => $penjualanDetail->jumlah
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to handle sale detail deletion", [
                'penjualan_detail_id' => $penjualanDetail->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}