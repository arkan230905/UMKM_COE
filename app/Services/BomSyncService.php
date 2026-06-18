<?php

namespace App\Services;

// ✅ PERBAIKAN: Disable import BomJobCosting, BomJobBTKL, BomJobBOP karena tabel bom_job_costings tidak ada
// use App\Models\BomJobCosting;
// use App\Models\BomJobBTKL;
// use App\Models\BomJobBOP;
use App\Models\ProsesProduksi;
use App\Models\Btkl;
use App\Models\BopProses;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ✅ PERBAIKAN: Service ini di-disable karena tabel bom_job_costings tidak ada
 * Semua method akan return early dengan log warning
 */
class BomSyncService
{
    /**
     * Sync BOM data when BTKL or BOP data changes
     */
    public static function syncBomFromMaterialChange($type, $materialId)
    {
        Log::warning("BomSyncService::syncBomFromMaterialChange disabled - table bom_job_costings does not exist", [
            'type' => $type,
            'materialId' => $materialId
        ]);
        return;
    }
    
    /**
     * Sync BTKL data for a specific BOM
     */
    public static function syncBTKLForBom($bomJobCosting)
    {
        Log::warning("BomSyncService::syncBTKLForBom disabled - table bom_job_costings does not exist");
        return;
    }
    
    /**
     * Sync BOP data for a specific BOM
     */
    public static function syncBOPForBom($bomJobCosting)
    {
        Log::warning("BomSyncService::syncBOPForBom disabled - table bom_job_costings does not exist");
        return;
    }
    
    /**
     * Full sync of all BTKL and BOP data for all BOMs
     */
    public static function fullSync()
    {
        Log::warning("BomSyncService::fullSync disabled - table bom_job_costings does not exist");
        return;
    }
    
    /**
     * Ensure all products have BomJobCosting with BTKL and BOP data
     */
    public static function ensureAllProductsHaveBomJobCosting()
    {
        Log::warning("BomSyncService::ensureAllProductsHaveBomJobCosting disabled - table bom_job_costings does not exist");
        return;
    }
    
    /**
     * Auto-populate BTKL and BOP for all products
     */
    public static function autoPopulateAllProducts()
    {
        Log::warning("BomSyncService::autoPopulateAllProducts disabled - table bom_job_costings does not exist");
        return;
    }
    
    /**
     * Sync all BOMs - alias for fullSync for backward compatibility
     */
    public static function syncAllBoms()
    {
        Log::warning("BomSyncService::syncAllBoms disabled - table bom_job_costings does not exist");
        return;
    }
    
    /**
     * Sync specific BOM by product ID
     */
    public static function syncBomByProduct($productId)
    {
        Log::warning("BomSyncService::syncBomByProduct disabled - table bom_job_costings does not exist", [
            'productId' => $productId
        ]);
        return;
    }
    
    /**
     * Sync BOM when kualifikasi data changes (affects BTKL calculations)
     */
    public static function syncBomFromKualifikasiChange($kualifikasiId)
    {
        Log::warning("BomSyncService::syncBomFromKualifikasiChange disabled - table bom_job_costings does not exist", [
            'kualifikasiId' => $kualifikasiId
        ]);
        return;
    }
    
    /**
     * Recalculate BOM costs (legacy method for backward compatibility)
     */
    public static function recalculateBomCosts($bom)
    {
        Log::warning("BomSyncService::recalculateBomCosts disabled - table bom_job_costings does not exist");
        return;
    }
}
