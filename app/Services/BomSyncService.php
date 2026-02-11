<?php

namespace App\Services;

use App\Models\BomJobCosting;
use App\Models\BomJobBTKL;
use App\Models\BomJobBOP;
use App\Models\ProsesProduksi;
use App\Models\Btkl;
use App\Models\BopProses;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BomSyncService
{
    /**
     * Sync BOM data when BTKL or BOP data changes
     */
    public static function syncBomFromMaterialChange($type, $materialId)
    {
        try {
            DB::beginTransaction();
            
            Log::info("BomSyncService: Starting sync for {$type} ID {$materialId}");
            
            // Get all BOM Job Costings that need to be updated
            $bomJobCostings = BomJobCosting::all();
            
            foreach ($bomJobCostings as $bomJobCosting) {
                if ($type === 'btkl') {
                    self::syncBTKLForBom($bomJobCosting);
                } elseif ($type === 'bop') {
                    self::syncBOPForBom($bomJobCosting);
                }
            }
            
            DB::commit();
            
            Log::info("BomSyncService: Sync completed successfully");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("BomSyncService: Sync failed - " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Sync BTKL data for a specific BOM
     */
    public static function syncBTKLForBom(BomJobCosting $bomJobCosting)
    {
        try {
            Log::info("Syncing BTKL for BOM Job Costing ID: {$bomJobCosting->id}");
            
            // Get all active BTKL processes
            $btklProcesses = Btkl::with('jabatan.pegawais')->get();
            
            // Clear existing BTKL data for this BOM
            BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->delete();
            
            // Add BTKL data from each process
            foreach ($btklProcesses as $btkl) {
                $kapasitasPerJam = $btkl->kapasitas_per_jam ?? 1;
                $tarifPerJam = $btkl->tarif_per_jam ?? 0;
                $durasiPerUnit = 1 / $kapasitasPerJam; // jam per unit
                $biayaPerUnit = $tarifPerJam / $kapasitasPerJam; // biaya per unit (CORRECT CALCULATION)
                
                // Use direct database insert to ensure correct values
                DB::table('bom_job_btkl')->insert([
                    'bom_job_costing_id' => $bomJobCosting->id,
                    'btkl_id' => $btkl->id,
                    'nama_proses' => $btkl->nama_btkl,
                    'durasi_jam' => $durasiPerUnit,
                    'tarif_per_jam' => $tarifPerJam,
                    'kapasitas_per_jam' => $kapasitasPerJam,
                    'subtotal' => $biayaPerUnit, // This should be the correct biaya per unit
                    'keterangan' => "Proses {$btkl->nama_btkl} untuk 1 unit {$bomJobCosting->produk->nama_produk}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                Log::info("Added BTKL: {$btkl->nama_btkl} - Rp " . number_format($biayaPerUnit, 2));
            }
            
            // Recalculate BOM totals
            $bomJobCosting->recalculate();
            
        } catch (\Exception $e) {
            Log::error("Failed to sync BTKL for BOM: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Sync BOP data for a specific BOM
     */
    public static function syncBOPForBom(BomJobCosting $bomJobCosting)
    {
        try {
            Log::info("Syncing BOP for BOM Job Costing ID: {$bomJobCosting->id}");
            
            // Get all active BOP processes
            $bopProcesses = BopProses::with('prosesProduksi')->get();
            
            // Clear existing BOP data for this BOM
            BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->delete();
            
            // Add BOP data from each process
            foreach ($bopProcesses as $bopProses) {
                $proses = $bopProses->prosesProduksi;
                
                if (!$proses) {
                    Log::warning("BOP Process ID {$bopProses->id} has no related ProsesProduksi");
                    continue;
                }
                
                $kapasitasPerJam = $proses->kapasitas_per_jam ?? 1;
                $bopPerJam = $bopProses->total_bop_per_jam ?? 0;
                
                // If total_bop_per_jam is 0, calculate from komponen_bop
                if ($bopPerJam == 0 && !empty($bopProses->komponen_bop)) {
                    $komponenBop = is_array($bopProses->komponen_bop) 
                        ? $bopProses->komponen_bop 
                        : json_decode($bopProses->komponen_bop, true);
                    
                    if (is_array($komponenBop)) {
                        $bopPerJam = array_sum(array_column($komponenBop, 'rate_per_hour'));
                    }
                }
                
                $durasiPerUnit = 1 / $kapasitasPerJam; // jam per unit
                $biayaBopPerUnit = $bopPerJam / $kapasitasPerJam; // biaya BOP per unit
                
                BomJobBOP::create([
                    'bom_job_costing_id' => $bomJobCosting->id,
                    'bop_id' => null, // Set to null since we're using BopProses, not Bop
                    'nama_bop' => "BOP {$proses->nama_proses}",
                    'jumlah' => $durasiPerUnit,
                    'tarif' => $bopPerJam,
                    'subtotal' => $biayaBopPerUnit,
                    'keterangan' => "Biaya overhead untuk proses {$proses->nama_proses}"
                ]);
                
                Log::info("Added BOP: {$proses->nama_proses} - Rp " . number_format($biayaBopPerUnit, 2));
            }
            
            // Recalculate BOM totals
            $bomJobCosting->recalculate();
            
        } catch (\Exception $e) {
            Log::error("Failed to sync BOP for BOM: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Full sync of all BTKL and BOP data for all BOMs
     * Also ensures all products have BomJobCosting with BTKL and BOP data
     */
    public static function fullSync()
    {
        try {
            DB::beginTransaction();
            
            Log::info("BomSyncService: Starting full sync");
            
            // First, ensure all products have BomJobCosting
            self::ensureAllProductsHaveBomJobCosting();
            
            // Then sync all existing BomJobCostings
            $bomJobCostings = BomJobCosting::all();
            
            foreach ($bomJobCostings as $bomJobCosting) {
                self::syncBTKLForBom($bomJobCosting);
                self::syncBOPForBom($bomJobCosting);
            }
            
            DB::commit();
            
            Log::info("BomSyncService: Full sync completed successfully");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("BomSyncService: Full sync failed - " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Ensure all products have BomJobCosting with BTKL and BOP data
     */
    public static function ensureAllProductsHaveBomJobCosting()
    {
        try {
            Log::info("BomSyncService: Ensuring all products have BomJobCosting");
            
            $products = Produk::all();
            $createdCount = 0;
            
            foreach ($products as $product) {
                $bomJobCosting = BomJobCosting::where('produk_id', $product->id)->first();
                
                if (!$bomJobCosting) {
                    // Create new BomJobCosting for this product
                    $bomJobCosting = BomJobCosting::create([
                        'produk_id' => $product->id,
                        'jumlah_produk' => 1,
                        'total_bbb' => 0,
                        'total_btkl' => 0,
                        'total_bahan_pendukung' => 0,
                        'total_bop' => 0,
                        'total_hpp' => 0,
                        'hpp_per_unit' => 0
                    ]);
                    
                    $createdCount++;
                    Log::info("Created BomJobCosting for product: {$product->nama_produk}");
                }
            }
            
            Log::info("BomSyncService: Created {$createdCount} new BomJobCosting records");
            
        } catch (\Exception $e) {
            Log::error("BomSyncService: Failed to ensure BomJobCosting - " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Auto-populate BTKL and BOP for all products
     * This ensures every product has complete BTKL and BOP data
     */
    public static function autoPopulateAllProducts()
    {
        try {
            DB::beginTransaction();
            
            Log::info("BomSyncService: Starting auto-population for all products");
            
            // Get all active BTKL and BOP data
            $btklData = Btkl::where('is_active', true)->with('jabatan')->get();
            $bopData = BopProses::where('is_active', true)->with('prosesProduksi')->get();
            
            if ($btklData->isEmpty() || $bopData->isEmpty()) {
                Log::warning("BomSyncService: No BTKL or BOP master data found");
                return;
            }
            
            // Ensure all products have BomJobCosting
            self::ensureAllProductsHaveBomJobCosting();
            
            // Get all BomJobCosting records
            $bomJobCostings = BomJobCosting::with('produk')->get();
            
            foreach ($bomJobCostings as $bomJobCosting) {
                Log::info("Auto-populating BTKL and BOP for: {$bomJobCosting->produk->nama_produk}");
                
                // Populate BTKL data
                self::populateBTKLForProduct($bomJobCosting, $btklData);
                
                // Populate BOP data
                self::populateBOPForProduct($bomJobCosting, $bopData);
                
                // Recalculate totals
                $bomJobCosting->recalculate();
            }
            
            DB::commit();
            
            Log::info("BomSyncService: Auto-population completed successfully");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("BomSyncService: Auto-population failed - " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Populate BTKL data for a specific product
     */
    private static function populateBTKLForProduct(BomJobCosting $bomJobCosting, $btklData)
    {
        // Clear existing BTKL data
        BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->delete();
        
        foreach ($btklData as $btkl) {
            $kapasitasPerJam = $btkl->kapasitas_per_jam ?? 1;
            $tarifPerJam = $btkl->tarif_per_jam ?? 0;
            $durasiPerUnit = 1 / $kapasitasPerJam;
            $biayaPerUnit = $tarifPerJam / $kapasitasPerJam;
            
            BomJobBTKL::create([
                'bom_job_costing_id' => $bomJobCosting->id,
                'btkl_id' => $btkl->id,
                'nama_proses' => $btkl->nama_btkl,
                'durasi_jam' => $durasiPerUnit,
                'tarif_per_jam' => $tarifPerJam,
                'kapasitas_per_jam' => $kapasitasPerJam,
                'subtotal' => $biayaPerUnit,
                'keterangan' => "Auto-populated: {$btkl->nama_btkl} untuk {$bomJobCosting->produk->nama_produk}"
            ]);
        }
    }
    
    /**
     * Populate BOP data for a specific product
     */
    private static function populateBOPForProduct(BomJobCosting $bomJobCosting, $bopData)
    {
        // Clear existing BOP data
        BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->delete();
        
        foreach ($bopData as $bopProses) {
            $proses = $bopProses->prosesProduksi;
            
            if (!$proses) {
                continue;
            }
            
            $kapasitasPerJam = $proses->kapasitas_per_jam ?? 1;
            $bopPerJam = $bopProses->total_bop_per_jam ?? 0;
            
            // Calculate from komponen_bop if needed
            if ($bopPerJam == 0 && !empty($bopProses->komponen_bop)) {
                $komponenBop = is_array($bopProses->komponen_bop) 
                    ? $bopProses->komponen_bop 
                    : json_decode($bopProses->komponen_bop, true);
                
                if (is_array($komponenBop)) {
                    $bopPerJam = array_sum(array_column($komponenBop, 'rate_per_hour'));
                }
            }
            
            $durasiPerUnit = 1 / $kapasitasPerJam;
            $biayaBopPerUnit = $bopPerJam / $kapasitasPerJam;
            
            BomJobBOP::create([
                'bom_job_costing_id' => $bomJobCosting->id,
                'bop_id' => null,
                'nama_bop' => "BOP {$proses->nama_proses}",
                'jumlah' => $durasiPerUnit,
                'tarif' => $bopPerJam,
                'subtotal' => $biayaBopPerUnit,
                'keterangan' => "Auto-populated: Biaya overhead untuk proses {$proses->nama_proses}"
            ]);
        }
    }
    
    /**
     * Sync all BOMs - alias for fullSync for backward compatibility
     */
    public static function syncAllBoms()
    {
        return self::fullSync();
    }
    
    /**
     * Sync specific BOM by product ID
     */
    public static function syncBomByProduct($productId)
    {
        try {
            DB::beginTransaction();
            
            $bomJobCosting = BomJobCosting::where('produk_id', $productId)->first();
            
            if (!$bomJobCosting) {
                Log::warning("No BOM Job Costing found for product ID: {$productId}");
                return;
            }
            
            self::syncBTKLForBom($bomJobCosting);
            self::syncBOPForBom($bomJobCosting);
            
            DB::commit();
            
            Log::info("BomSyncService: Sync completed for product ID: {$productId}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("BomSyncService: Sync failed for product ID {$productId} - " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Sync BOM when jabatan data changes (affects BTKL calculations)
     */
    public static function syncBomFromJabatanChange($jabatanId)
    {
        try {
            DB::beginTransaction();
            
            Log::info("BomSyncService: Starting sync for jabatan ID {$jabatanId}");
            
            // Find all BTKL records that use this jabatan
            $btkls = \App\Models\Btkl::where('jabatan_id', $jabatanId)->get();
            
            foreach ($btkls as $btkl) {
                self::syncBomFromMaterialChange('btkl', $btkl->id);
            }
            
            DB::commit();
            
            Log::info("BomSyncService: Jabatan sync completed successfully");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("BomSyncService: Jabatan sync failed - " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Recalculate BOM costs (legacy method for backward compatibility)
     */
    public static function recalculateBomCosts($bom)
    {
        try {
            if ($bom && $bom->produk_id) {
                return self::syncBomByProduct($bom->produk_id);
            }
            
            Log::warning("BomSyncService: Invalid BOM provided for recalculation");
            
        } catch (\Exception $e) {
            Log::error("BomSyncService: Failed to recalculate BOM costs - " . $e->getMessage());
            throw $e;
        }
    }
}