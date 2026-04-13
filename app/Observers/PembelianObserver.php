<?php

namespace App\Observers;

use App\Models\Pembelian;
use App\Services\StockService;
use App\Services\PurchaseJournalService;
use Illuminate\Support\Facades\Log;

class PembelianObserver
{
    protected $stockService;
    protected $journalService;

    public function __construct(StockService $stockService, PurchaseJournalService $journalService)
    {
        $this->stockService = $stockService;
        $this->journalService = $journalService;
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
            
            // Create journal entries for purchase
            $this->journalService->createJournalFromPurchase($pembelian);
            
            Log::info('Journal entries created for purchase', [
                'pembelian_id' => $pembelian->id,
                'nomor_pembelian' => $pembelian->nomor_pembelian
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error processing purchase', [
                'pembelian_id' => $pembelian->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
        
        Log::info('Purchase updated', [
            'pembelian_id' => $pembelian->id,
            'nomor_pembelian' => $pembelian->nomor_pembelian
        ]);
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
