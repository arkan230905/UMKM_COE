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
        $coaKode = $this->getCoaKodeFromName($bahanBaku->nama_bahan);
        
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
     */
    private function getCoaKodeFromName(string $nama): string
    {
        $nama = strtolower($nama);
        
        // Specific mappings
        if (str_contains($nama, 'ayam potong')) return '1141';
        if (str_contains($nama, 'ayam kampung')) return '1142';
        if (str_contains($nama, 'bebek')) return '1143';
        if (str_contains($nama, 'ayam')) return '1144'; // Ayam lainnya
        
        // Default: Generic Persediaan Bahan Baku
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
