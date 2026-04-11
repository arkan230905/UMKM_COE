<?php

namespace App\Observers;

use App\Models\Pembelian;
use App\Services\StockService;
use Illuminate\Support\Facades\Log;

class PembelianObserver
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Handle the Pembelian "created" event.
     */
    public function created(Pembelian $pembelian)
    {
        try {
            // Process stock entries for purchase
            $this->stockService->processPurchase($pembelian->id);
            
            Log::info('Stock entries created for purchase', [
                'pembelian_id' => $pembelian->id,
                'nomor_pembelian' => $pembelian->nomor_pembelian
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating stock entries for purchase: ' . $e->getMessage(), [
                'pembelian_id' => $pembelian->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the Pembelian "updated" event.
     */
    public function updated(Pembelian $pembelian)
    {
        // Handle updates if needed
        // For now, we don't automatically update stock on pembelian updates
        // to avoid complications with existing stock movements
    }

    /**
     * Handle the Pembelian "deleted" event.
     */
    public function deleted(Pembelian $pembelian)
    {
        // When a purchase is deleted, we should reverse the stock entries
        // This is a complex operation that should be handled carefully
        Log::info('Purchase deleted, stock entries should be reviewed', [
            'pembelian_id' => $pembelian->id
        ]);
    }
}