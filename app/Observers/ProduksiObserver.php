<?php

namespace App\Observers;

use App\Models\Produksi;
use App\Models\ProduksiDetail;
use App\Models\JurnalUmum;
use App\Services\AutoCoaService;
use Illuminate\Support\Facades\Log;

class ProduksiObserver
{
    /**
     * Handle the Produksi "updated" event.
     * 
     * NOTE: Journal creation is now handled by JournalService in ProduksiController
     * This observer is kept for future use but journal creation is disabled
     * to prevent duplication with the new journal_entries system.
     */
    public function updated(Produksi $produksi)
    {
        // DISABLED: Journal creation moved to JournalService to prevent duplication
        // The new system uses journal_entries table with production_* types
        // instead of jurnal_umum table with 'produksi' type
        
        /*
        // Check if status changed to 'selesai' or 'completed'
        if ($produksi->wasChanged('status') && 
            in_array($produksi->status, ['selesai', 'completed'])) {
            
            $this->createProduksiJournals($produksi);
        }
        */
        
        Log::info("ProduksiObserver: Journal creation disabled to prevent duplication", [
            'produksi_id' => $produksi->id,
            'status' => $produksi->status
        ]);
    }
    
    /**
     * Create journal entries for completed production
     * 
     * DISABLED: This method is kept for reference but not used
     * to prevent duplication with the new journal_entries system
     */
    private function createProduksiJournals(Produksi $produksi)
    {
        // DISABLED: Moved to JournalService in ProduksiController
        Log::info("ProduksiObserver: createProduksiJournals called but disabled", [
            'produksi_id' => $produksi->id
        ]);
        return;
        
        /* ORIGINAL CODE COMMENTED OUT TO PREVENT DUPLICATION
        try {
            Log::info("Creating journals for completed production: {$produksi->id}");
            
            // Get total production cost
            $totalCost = $produksi->total_biaya ?? 0;
            
            if ($totalCost <= 0) {
                Log::warning("Production cost is 0 or negative, skipping journal creation");
                return;
            }
            
            // ... rest of the original code ...
            
        } catch (\Exception $e) {
            Log::error("Failed to create journals for production {$produksi->id}: " . $e->getMessage());
        }
        */
    }
    
    /**
     * Delete existing journals for this production
     * 
     * DISABLED: This method is kept for reference but not used
     */
    private function deleteExistingJournals(Produksi $produksi)
    {
        // DISABLED: Journal deletion is now handled by JournalService
        Log::info("ProduksiObserver: deleteExistingJournals called but disabled", [
            'produksi_id' => $produksi->id
        ]);
        return;
        
        /* ORIGINAL CODE COMMENTED OUT
        $referensi = "PROD-" . $produksi->tanggal->format('Ymd') . "-" . str_pad($produksi->id, 3, '0', STR_PAD_LEFT);
        
        JurnalUmum::where('tipe_referensi', 'produksi')
            ->where('referensi', $referensi)
            ->delete();
        
        Log::info("Deleted existing journals for production: {$referensi}");
        */
    }
    
    /**
     * Handle the Produksi "deleted" event.
     * 
     * DISABLED: Journal deletion is now handled by JournalService
     */
    public function deleted(Produksi $produksi)
    {
        // DISABLED: Journal deletion is now handled by JournalService
        Log::info("ProduksiObserver: Journal deletion disabled", [
            'produksi_id' => $produksi->id
        ]);
        return;
        
        /* ORIGINAL CODE COMMENTED OUT
        // Delete associated journal entries when production is deleted
        $referensi = "PROD-" . $produksi->tanggal->format('Ymd') . "-" . str_pad($produksi->id, 3, '0', STR_PAD_LEFT);
        
        JurnalUmum::where('tipe_referensi', 'produksi')
            ->where('referensi', $referensi)
            ->delete();
        
        Log::info("Deleted journals for deleted production: {$referensi}");
        */
    }
}
