<?php

namespace App\Observers;

use App\Models\Btkl;
use App\Services\BomSyncService;
use Illuminate\Support\Facades\Log;

class BtklObserver
{
    /**
     * Handle the Btkl "created" event.
     */
    public function created(Btkl $btkl): void
    {
        try {
            Log::info("BtklObserver: BTKL created - ID: {$btkl->id}, Name: {$btkl->nama_btkl}");
            BomSyncService::syncBomFromMaterialChange('btkl', $btkl->id);
        } catch (\Exception $e) {
            Log::error("BtklObserver: Failed to sync BOM after BTKL creation - " . $e->getMessage());
        }
    }

    /**
     * Handle the Btkl "updated" event.
     */
    public function updated(Btkl $btkl): void
    {
        try {
            Log::info("BtklObserver: BTKL updated - ID: {$btkl->id}, Name: {$btkl->nama_btkl}");
            BomSyncService::syncBomFromMaterialChange('btkl', $btkl->id);
        } catch (\Exception $e) {
            Log::error("BtklObserver: Failed to sync BOM after BTKL update - " . $e->getMessage());
        }
    }

    /**
     * Handle the Btkl "deleted" event.
     */
    public function deleted(Btkl $btkl): void
    {
        try {
            Log::info("BtklObserver: BTKL deleted - ID: {$btkl->id}, Name: {$btkl->nama_btkl}");
            BomSyncService::syncBomFromMaterialChange('btkl', $btkl->id);
        } catch (\Exception $e) {
            Log::error("BtklObserver: Failed to sync BOM after BTKL deletion - " . $e->getMessage());
        }
    }
}