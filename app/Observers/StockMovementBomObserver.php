<?php

namespace App\Observers;

use App\Models\StockMovement;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class StockMovementBomObserver
{
    /**
     * Handle the StockMovement "created" event.
     * Auto-update BOM prices when new stock movement occurs
     */
    public function created(StockMovement $stockMovement)
    {
        // Update master data first
        $this->updateMasterData($stockMovement);
        
        // Only update BOM for purchase movements (new price data)
        if ($stockMovement->ref_type === 'purchase' && $stockMovement->direction === 'in') {
            $this->updateBomFromStockReport($stockMovement);
        }
    }

    /**
     * Handle the StockMovement "updated" event.
     */
    public function updated(StockMovement $stockMovement)
    {
        // Update master data first
        $this->updateMasterData($stockMovement);
        
        // Update BOM if price-related fields changed
        if ($stockMovement->wasChanged(['total_cost', 'qty']) && 
            $stockMovement->ref_type === 'purchase' && 
            $stockMovement->direction === 'in') {
            $this->updateBomFromStockReport($stockMovement);
        }
    }

    /**
     * Update master data stock and price based on stock movements
     */
    private function updateMasterData(StockMovement $stockMovement)
    {
        try {
            $itemType = $stockMovement->item_type;
            $itemId = $stockMovement->item_id;
            
            // Use RealTimeStockService to update master data
            $stockService = new \App\Services\RealTimeStockService();
            $reflection = new \ReflectionClass($stockService);
            $method = $reflection->getMethod('updateModelStock');
            $method->setAccessible(true);
            $method->invoke($stockService, $itemType, $itemId);
            
            Log::info('Master data updated via observer', [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'stock_movement_id' => $stockMovement->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating master data via observer', [
                'stock_movement_id' => $stockMovement->id,
                'item_type' => $stockMovement->item_type,
                'item_id' => $stockMovement->item_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update BOM prices based on stock report calculations
     */
    private function updateBomFromStockReport(StockMovement $stockMovement)
    {
        try {
            $itemType = $stockMovement->item_type === 'support' ? 'support' : 'material';
            
            Log::info('Auto-updating BOM from stock report', [
                'item_type' => $itemType,
                'item_id' => $stockMovement->item_id,
                'ref_type' => $stockMovement->ref_type,
                'total_cost' => $stockMovement->total_cost,
                'qty' => $stockMovement->qty
            ]);

            // Run the improved BOM fix command that uses actual stock report prices
            Artisan::call('bom:fix-from-actual-stock');

            Log::info('BOM auto-update completed successfully', [
                'item_type' => $itemType,
                'item_id' => $stockMovement->item_id
            ]);

        } catch (\Exception $e) {
            Log::error('Error auto-updating BOM from stock report', [
                'stock_movement_id' => $stockMovement->id,
                'item_type' => $stockMovement->item_type,
                'item_id' => $stockMovement->item_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}