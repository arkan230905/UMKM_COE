<?php

namespace App\Observers;

use App\Models\BahanBaku;
use App\Models\Coa;
use Illuminate\Support\Facades\Log;

class BahanBakuObserver
{
    /**
     * Handle the BahanBaku "created" event.
     * Auto-set coa_persediaan_id based on item name
     */
    public function created(BahanBaku $bahanBaku): void
    {
        $this->autoSetCoaIfNeeded($bahanBaku);
    }
    
    /**
     * Handle the BahanBaku "updating" event.
     * Auto-set coa_persediaan_id if changed or still null
     */
    public function updating(BahanBaku $bahanBaku): void
    {
        // Only auto-set if COA is null or if nama_bahan changed
        if (!$bahanBaku->coa_persediaan_id || $bahanBaku->isDirty('nama_bahan')) {
            $this->autoSetCoaIfNeeded($bahanBaku, false); // Don't save, let the update continue
        }
    }
    
    /**
     * Auto-set coa_persediaan_id based on item name
     */
    private function autoSetCoaIfNeeded(BahanBaku $bahanBaku, bool $save = true): void
    {
        // Skip if COA already set
        if ($bahanBaku->coa_persediaan_id) {
            return;
        }

        // Try to find specific COA based on item name
        $coaKode = $this->getCoaKodeFromName($bahanBaku->nama_bahan, $bahanBaku->user_id);
        
        // Find COA for this user
        $coa = Coa::where('kode_akun', $coaKode)
            ->where('user_id', $bahanBaku->user_id)
            ->first();
        
        if ($coa) {
            $bahanBaku->coa_persediaan_id = $coa->kode_akun;
            
            if ($save) {
                $bahanBaku->saveQuietly(); // Save without triggering events
            }
            
            Log::info('[BahanBakuObserver] Auto-set COA Persediaan', [
                'bahan_baku_id' => $bahanBaku->id,
                'nama_bahan' => $bahanBaku->nama_bahan,
                'coa_kode' => $coa->kode_akun,
                'coa_nama' => $coa->nama_akun
            ]);
        } else {
            Log::warning('[BahanBakuObserver] COA not found for auto-set', [
                'bahan_baku_id' => $bahanBaku->id,
                'nama_bahan' => $bahanBaku->nama_bahan,
                'expected_coa_kode' => $coaKode,
                'user_id' => $bahanBaku->user_id
            ]);
        }
    }

    /**
     * Determine COA code based on item name
     * Updated for JASUKE (Jagung Susu Keju) business - but flexible for any COA structure
     * 
     * Algorithm:
     * 1. Try exact/partial name match with child COA (1141, 1142, etc)
     * 2. If no match, use parent COA (114)
     */
    private function getCoaKodeFromName(string $nama, int $userId): string
    {
        $nama = strtolower($nama);
        
        // Get all available child COAs for Bahan Baku from user's COA list
        $childCoas = Coa::where('user_id', $userId)
            ->where('kode_akun', 'LIKE', '114%')
            ->where('kode_akun', '!=', '114') // Exclude parent
            ->get();
        
        // Try to find best match based on COA name
        foreach ($childCoas as $coa) {
            $coaNama = strtolower($coa->nama_akun);
            
            // Extract keywords from COA name (after "Pers. Bahan Baku")
            $keywords = str_replace(['pers. bahan baku', 'persediaan bahan baku', 'pers.', 'persediaan'], '', $coaNama);
            $keywords = trim($keywords);
            
            // Check if item name contains the COA keyword
            if (!empty($keywords) && str_contains($nama, $keywords)) {
                return $coa->kode_akun;
            }
        }
        
        // Default: Generic Persediaan Bahan Baku (parent account)
        return '114';
    }

    /**
     * Handle the BahanBaku "deleting" event.
     */
    public function deleting(BahanBaku $bahanBaku): void
    {
        try {
            // Check if table exists before deleting
            if (\Schema::hasTable('job_bahan_baku')) {
                $bahanBaku->jobs()->detach();
            }
            
            if (\Schema::hasTable('produksi_details')) {
                \DB::table('produksi_details')
                    ->where('bahan_baku_id', $bahanBaku->id)
                    ->delete();
            }
        } catch (\Exception $e) {
            Log::warning('[BahanBakuObserver] Error during delete cascade', [
                'bahan_baku_id' => $bahanBaku->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
