<?php

namespace App\Observers;

use App\Models\Btkl;
use App\Models\ProsesProduksi;
use App\Models\BopProses;
use App\Services\BomSyncService;
use Illuminate\Support\Facades\Log;

class BtklObserver
{
    /**
     * Handle Btkl "created" event
     */
    public function created(Btkl $btkl): void
    {
        try {
            Log::info("BtklObserver: Btkl created - ID: {$btkl->id}, Name: {$btkl->nama_btkl}");
            $this->syncProsesFromBtkl($btkl);
        } catch (\Exception $e) {
            Log::error("BtklObserver: Failed to sync ProsesProduksi after Btkl create - " . $e->getMessage());
        }
    }

    /**
     * Handle Btkl "updated" event
     */
    public function updated(Btkl $btkl): void
    {
        try {
            Log::info("BtklObserver: Btkl updated - ID: {$btkl->id}, Name: {$btkl->nama_btkl}");
            $this->syncProsesFromBtkl($btkl);
        } catch (\Exception $e) {
            Log::error("BtklObserver: Failed to sync ProsesProduksi after Btkl update - " . $e->getMessage());
        }
    }

    /**
     * Handle Btkl "deleted" event
     */
    public function deleted(Btkl $btkl): void
    {
        try {
            Log::info("BtklObserver: Btkl deleted - ID: {$btkl->id}, Name: {$btkl->nama_btkl}");
            // Clean up related ProsesProduksi when Btkl is deleted
            $prosesProduksi = ProsesProduksi::where('btkl_id', $btkl->id)->first();
            if ($prosesProduksi) {
                $prosesProduksi->delete();
            }
        } catch (\Exception $e) {
            Log::error("BtklObserver: Failed to clean up ProsesProduksi after Btkl delete - " . $e->getMessage());
        }
    }

    /**
     * Sync ProsesProduksi from Btkl data
     */
    private function syncProsesFromBtkl(Btkl $btkl): void
    {
        try {
            // Find or create ProsesProduksi for this Btkl
            $prosesProduksi = ProsesProduksi::where('btkl_id', $btkl->id)->first();
            
            if (!$prosesProduksi) {
                // Create new ProsesProduksi
                $prosesProduksi = ProsesProduksi::create([
                    'btkl_id' => $btkl->id,
                    'nama_proses' => $btkl->nama_btkl,
                    'deskripsi' => $btkl->deskripsi_proses,
                    'kapasitas_per_jam' => 1, // Default value
                    'tarif_btkl' => $btkl->tarif_btkl,
                    'user_id' => $btkl->user_id,
                ]);
                
                Log::info("BtklObserver: Created ProsesProduksi for Btkl ID: {$btkl->id}");
            } else {
                // Update existing ProsesProduksi
                $prosesProduksi->update([
                    'nama_proses' => $btkl->nama_btkl,
                    'deskripsi' => $btkl->deskripsi_proses,
                    'tarif_btkl' => $btkl->tarif_btkl,
                ]);
                
                Log::info("BtklObserver: Updated ProsesProduksi for Btkl ID: {$btkl->id}");
            }
            
            // Sync BOP data
            $this->syncBopFromProses($prosesProduksi);
            
        } catch (\Exception $e) {
            Log::error("BtklObserver: Failed to sync ProsesProduksi from Btkl - " . $e->getMessage());
        }
    }

    /**
     * Sync BOP data when ProsesProduksi changes
     */
    private function syncBopFromProses(ProsesProduksi $proses): void
    {
        try {
            // Find related BOP Proses
            $bopProses = BopProses::where('proses_produksi_id', $proses->id)->first();
            
            if ($bopProses) {
                // Update BOP Proses with new data from ProsesProduksi
                $bopProses->update([
                    'kapasitas_per_jam' => $proses->kapasitas_per_jam,
                ]);
                
                // Recalculate BOP per unit
                $this->recalculateBopPerUnit($bopProses);
                
                Log::info("BtklObserver: Synced BOP Proses ID: {$bopProses->id} with new ProsesProduksi data");
                Log::info("BtklObserver: New kapasitas: {$proses->kapasitas_per_jam}, New tarif: {$proses->tarif_btkl}");
            } else {
                Log::info("BtklObserver: No BOP Proses found for ProsesProduksi ID: {$proses->id}");
            }

            Log::info("BtklObserver: Successfully synced BOP data for ProsesProduksi ID: {$proses->id}");
        } catch (\Exception $e) {
            Log::error("BtklObserver: Failed to sync BOP from ProsesProduksi - " . $e->getMessage());
        }
    }

    /**
     * Recalculate BOP per unit
     */
    private function recalculateBopPerUnit(BopProses $bopProses): void
    {
        try {
            // Get total BOP from komponen_bop or individual fields
            $totalBop = 0;
            
            if ($bopProses->komponen_bop) {
                $komponenBop = is_array($bopProses->komponen_bop) ? $bopProses->komponen_bop : json_decode($bopProses->komponen_bop, true);
                if (is_array($komponenBop)) {
                    $totalBop = array_sum(array_column($komponenBop, 'rate_per_hour'));
                }
            }

            if ($totalBop == 0) {
                // Fallback to individual fields
                $totalBop = 
                    floatval($bopProses->listrik_per_jam ?? 0) +
                    floatval($bopProses->gas_bbm_per_jam ?? 0) +
                    floatval($bopProses->penyusutan_mesin_per_jam ?? 0) +
                    floatval($bopProses->maintenance_per_jam ?? 0) +
                    floatval($bopProses->gaji_mandor_per_jam ?? 0) +
                    floatval($bopProses->lain_lain_per_jam ?? 0);
            }

            // Update BOP per unit (same as total BOP)
            $bopProses->update([
                'total_bop_per_jam' => $totalBop,
                'bop_per_unit' => $totalBop,
            ]);

            Log::info("BtklObserver: Recalculated BOP per unit for BOP Proses ID: {$bopProses->id}");
        } catch (\Exception $e) {
            Log::error("BtklObserver: Failed to recalculate BOP per unit - " . $e->getMessage());
        }
    }
}