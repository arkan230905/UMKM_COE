<?php

namespace App\Observers;

use App\Models\BopProses;
use App\Services\BomSyncService;
use Illuminate\Support\Facades\Log;

class BopProsesObserver
{
    /**
     * Handle the BopProses "created" event.
     */
    public function created(BopProses $bopProses): void
    {
        try {
            Log::info("BopProsesObserver: BOP Proses created - ID: {$bopProses->id}");
            BomSyncService::syncBomFromMaterialChange('bop', $bopProses->id);
        } catch (\Exception $e) {
            Log::error("BopProsesObserver: Failed to sync BOM after BOP creation - " . $e->getMessage());
        }
    }

    /**
     * Handle the BopProses "updated" event.
     */
    public function updated(BopProses $bopProses): void
    {
        try {
            Log::info("BopProsesObserver: BOP Proses updated - ID: {$bopProses->id}");
            BomSyncService::syncBomFromMaterialChange('bop', $bopProses->id);
        } catch (\Exception $e) {
            Log::error("BopProsesObserver: Failed to sync BOM after BOP update - " . $e->getMessage());
        }
    }

    /**
     * Handle the BopProses "deleted" event.
     */
    public function deleted(BopProses $bopProses): void
    {
        try {
            Log::info("BopProsesObserver: BOP Proses deleted - ID: {$bopProses->id}");
            BomSyncService::syncBomFromMaterialChange('bop', $bopProses->id);
        } catch (\Exception $e) {
            Log::error("BopProsesObserver: Failed to sync BOM after BOP deletion - " . $e->getMessage());
        }
    }
}