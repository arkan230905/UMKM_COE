<?php

namespace App\Services;

use App\Models\Bom;
use App\Models\BomDetail;
use App\Models\BomJobCosting;
use App\Models\BomJobBahanPendukung;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BomSyncService
{
    /**
     * Sync BOM when material prices change
     */
    public static function syncBomFromMaterialChange($materialType, $materialId)
    {
        try {
            DB::beginTransaction();
            
            if ($materialType === 'bahan_baku') {
                self::syncBomFromBahanBakuChange($materialId);
            } elseif ($materialType === 'bahan_pendukung') {
                self::syncBomFromBahanPendukungChange($materialId);
            } elseif ($materialType === 'btkl') {
                self::syncBomFromBTKLChange($materialId);
            }
            
            DB::commit();
            
            Log::info("BOM synced from {$materialType} change", ['material_id' => $materialId]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error syncing BOM from {$materialType} change: " . $e->getMessage());
        }
    }
    
    /**
     * Sync BOM when Bahan Baku price changes
     */
    private static function syncBomFromBahanBakuChange($bahanBakuId)
    {
        $details = BomDetail::where('bahan_baku_id', $bahanBakuId)->get();
        
        foreach ($details as $detail) {
            $bom = $detail->bom;
            if ($bom) {
                self::recalculateBomCosts($bom);
            }
        }
    }
    
    /**
     * Sync BOM when Bahan Pendukung price changes
     */
    private static function syncBomFromBahanPendukungChange($bahanPendukungId)
    {
        $pendukungDetails = BomJobBahanPendukung::where('bahan_pendukung_id', $bahanPendukungId)->get();
        
        foreach ($pendukungDetails as $detail) {
            $bomJobCosting = $detail->bomJobCosting;
            if ($bomJobCosting) {
                $bom = Bom::where('produk_id', $bomJobCosting->produk_id)->first();
                if ($bom) {
                    self::recalculateBomCosts($bom);
                }
            }
        }
    }
    
    /**
     * Sync BOM when BTKL data changes
     */
    private static function syncBomFromBTKLChange($btklId)
    {
        $btklDetails = DB::table('bom_job_btkl')
            ->where('btkl_id', $btklId)
            ->get();
        
        foreach ($btklDetails as $detail) {
            $bomJobCosting = BomJobCosting::find($detail->bom_job_costing_id);
            if ($bomJobCosting) {
                $bom = Bom::where('produk_id', $bomJobCosting->produk_id)->first();
                if ($bom) {
                    self::recalculateBomCosts($bom);
                }
            }
        }
    }
    
    /**
     * Recalculate BOM costs
     */
    public static function recalculateBomCosts($bom)
    {
        try {
            $totalBiaya = 0;
            
            // Calculate from BOM details
            foreach ($bom->details as $detail) {
                $totalBiaya += $detail->total_harga;
            }
            
            // Get BomJobCosting for additional costs
            $bomJobCosting = BomJobCosting::where('produk_id', $bom->produk_id)->first();
            if ($bomJobCosting) {
                // Add Bahan Pendukung
                $bahanPendukung = BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->sum('subtotal');
                $totalBiaya += $bahanPendukung;
                
                // Add BTKL
                $totalBTKL = DB::table('bom_job_btkl')
                    ->where('bom_job_costing_id', $bomJobCosting->id)
                    ->sum('subtotal');
                $totalBiaya += $totalBTKL;
                
                // Add BOP
                $totalBOP = $bomJobCosting->total_bop ?? 0;
                $totalBiaya += $totalBOP;
            }
            
            // Update BOM
            $bom->update([
                'total_biaya' => $totalBiaya
            ]);
            
            // Update product harga_bom
            $bom->produk->update(['harga_bom' => $totalBiaya]);
            
            Log::info('BOM recalculated', [
                'bom_id' => $bom->id,
                'produk_id' => $bom->produk_id,
                'total_biaya' => $totalBiaya
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error recalculating BOM: ' . $e->getMessage());
        }
    }
    
    /**
     * Force sync all BOMs
     */
    public static function syncAllBoms()
    {
        try {
            DB::beginTransaction();
            
            $boms = Bom::all();
            foreach ($boms as $bom) {
                self::recalculateBomCosts($bom);
            }
            
            DB::commit();
            
            Log::info('All BOMs synced successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error syncing all BOMs: ' . $e->getMessage());
        }
    }
    
    /**
     * Sync BTKL master data to BOMs
     * This automatically adds all available BTKL processes to BOM job costing
     */
    public static function syncBTKLToBOMs()
    {
        try {
            DB::beginTransaction();
            
            // Get all BTKL processes
            $btklProcesses = \App\Models\ProsesProduksi::where('tarif_btkl', '>', 0)->get();
            
            // Get all BOM job costings
            $bomJobCostings = BomJobCosting::all();
            
            foreach ($bomJobCostings as $bomJobCosting) {
                foreach ($btklProcesses as $process) {
                    // Check if this process is already linked
                    $existing = DB::table('bom_job_btkl')
                        ->where('bom_job_costing_id', $bomJobCosting->id)
                        ->where('proses_produksi_id', $process->id)
                        ->first();
                    
                    if (!$existing) {
                        // Calculate biaya per produk
                        $biayaPerProduk = $process->kapasitas_per_jam > 0 ? 
                            $process->tarif_btkl / $process->kapasitas_per_jam : 0;
                        
                        // Insert new BTKL link with default 1 hour duration
                        DB::table('bom_job_btkl')->insert([
                            'bom_job_costing_id' => $bomJobCosting->id,
                            'proses_produksi_id' => $process->id,
                            'nama_proses' => $process->nama_proses,
                            'durasi_jam' => 1, // Default 1 hour
                            'tarif_per_jam' => $process->tarif_btkl,
                            'subtotal' => $biayaPerProduk * 1, // biaya_per_produk * durasi_jam
                            'keterangan' => 'Auto-sync from BTKL master data',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
                
                // Recalculate BOM costs after adding BTKL
                $bom = Bom::where('produk_id', $bomJobCosting->produk_id)->first();
                if ($bom) {
                    self::recalculateBomCosts($bom);
                }
            }
            
            DB::commit();
            
            Log::info('BTKL master data synced to all BOMs successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error syncing BTKL to BOMs: ' . $e->getMessage());
        }
    }
}
