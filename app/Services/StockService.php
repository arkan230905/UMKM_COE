<?php

namespace App\Services;

use App\Models\StockLayer;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function getAvailableQty(string $itemType, int $itemId): float
    {
        $sum = (float) StockLayer::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->sum('remaining_qty');
        return max($sum, 0.0);
    }
    protected function aggregateLayer(string $itemType, int $itemId): ?StockLayer
    {
        $layers = StockLayer::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->get();

        if ($layers->isEmpty()) {
            return null;
        }

        $totalQty = 0.0; $totalCost = 0.0; $satuan = $layers->first()->satuan;
        foreach ($layers as $ly) {
            $q = (float)($ly->remaining_qty ?? 0);
            $c = (float)($ly->unit_cost ?? 0);
            $totalQty += $q;
            $totalCost += $q * $c;
        }
        $avgCost = $totalQty > 0 ? $totalCost / $totalQty : 0.0;

        // Collapse into a single layer (moving average)
        DB::table((new StockLayer)->getTable())
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->delete();

        if ($totalQty <= 0) {
            return null;
        }

        return StockLayer::create([
            'item_type' => $itemType,
            'item_id' => $itemId,
            'tanggal' => Carbon::now()->toDateString(),
            'remaining_qty' => $totalQty,
            'unit_cost' => $avgCost,
            'satuan' => $satuan,
            'ref_type' => 'avg_rollup',
            'ref_id' => 0,
        ]);
    }

    /**
     * Add stock layer with optional manual conversion data
     */
    public function addLayerWithManualConversion(string $itemType, int $itemId, float $qty, string $satuan, float $unitCost, string $refType, int $refId, string $tanggal, ?array $manualConversionData = null): void
    {
        DB::transaction(function () use ($itemType, $itemId, $qty, $satuan, $unitCost, $refType, $refId, $tanggal, $manualConversionData) {
            // Check if a stock movement with the same parameters already exists to prevent duplicates
            $existingMovement = StockMovement::where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->where('ref_type', $refType)
                ->where('ref_id', $refId)
                ->where('direction', 'in')
                ->where('tanggal', $tanggal)
                ->first();
            
            if ($existingMovement) {
                \Log::warning('Duplicate stock movement prevented', [
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'ref_type' => $refType,
                    'ref_id' => $refId,
                    'existing_movement_id' => $existingMovement->id
                ]);
                return; // Skip creating duplicate movement
            }
            
            // Convert input quantity to primary unit first
            $primaryQty = $this->convertToPrimaryUnit($itemType, $itemId, $qty, $satuan);
            $primaryUnitCost = $this->convertToPrimaryUnitCost($itemType, $itemId, $unitCost, $satuan);
            
            // Record movement IN first with converted quantities
            StockMovement::create([
                'item_type' => $itemType,
                'item_id' => $itemId,
                'tanggal' => $tanggal,
                'direction' => 'in',
                'qty' => $primaryQty,
                'satuan' => $this->getPrimarySatuan($itemType, $itemId),
                'unit_cost' => $primaryUnitCost,
                'total_cost' => $primaryQty * $primaryUnitCost,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'manual_conversion_data' => $manualConversionData ? json_encode($manualConversionData) : null,
            ]);

            // Moving average: compute new average with existing layers + this receipt
            $existing = StockLayer::where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->get();

            $oldQty = 0.0; $oldCost = 0.0;
            foreach ($existing as $ly) {
                $q = (float)($ly->remaining_qty ?? 0);
                $c = (float)($ly->unit_cost ?? 0);
                $oldQty += $q;
                $oldCost += $q * $c;
            }
            $newQty = max($oldQty + $primaryQty, 0.0);
            $newCost = max($oldCost + ($primaryQty * $primaryUnitCost), 0.0);
            $avgCost = $newQty > 0 ? ($newCost / $newQty) : 0.0;

            // Collapse to single layer
            DB::table((new StockLayer)->getTable())
                ->where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->delete();

            if ($newQty > 0) {
                StockLayer::create([
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'tanggal' => $tanggal,
                    'remaining_qty' => $newQty,
                    'unit_cost' => $avgCost,
                    'satuan' => $this->getPrimarySatuan($itemType, $itemId),
                    'ref_type' => 'avg_layer',
                    'ref_id' => 0,
                    'manual_conversion_data' => $manualConversionData ? json_encode($manualConversionData) : null,
                ]);
            }
        });
    }

    /**
     * Add stock layer (backward compatibility wrapper)
     */
    public function addLayer(string $itemType, int $itemId, float $qty, string $satuan, float $unitCost, string $refType, int $refId, string $tanggal): void
    {
        $this->addLayerWithManualConversion($itemType, $itemId, $qty, $satuan, $unitCost, $refType, $refId, $tanggal, null);
    }
    
    /**
     * Convert quantity to primary unit
     */
    private function convertToPrimaryUnit(string $itemType, int $itemId, float $qty, string $fromSatuan): float
    {
        // Get conversion ratio from database or use fallback
        $conversionRatio = $this->getConversionRatio($itemType, $itemId, $fromSatuan);
        return $qty * $conversionRatio;
    }
    
    /**
     * Convert unit cost to primary unit cost
     */
    private function convertToPrimaryUnitCost(string $itemType, int $itemId, float $unitCost, string $fromSatuan): float
    {
        // Get conversion ratio from database or use fallback
        $conversionRatio = $this->getConversionRatio($itemType, $itemId, $fromSatuan);
        return $unitCost / $conversionRatio;
    }
    
    /**
     * Get primary satuan for an item
     */
    private function getPrimarySatuan(string $itemType, int $itemId): string
    {
        // Get the primary satuan from the item
        $item = null;
        if ($itemType === 'material') {
            $item = \App\Models\BahanBaku::find($itemId);
        } elseif ($itemType === 'support') {
            $item = \App\Models\BahanPendukung::find($itemId);
        } elseif ($itemType === 'product') {
            $item = \App\Models\Produk::find($itemId);
        }
        
        return $item?->satuan?->nama ?? 'Unit';
    }
    
    /**
     * Get conversion ratio from database or use fallback logic
     */
    private function getConversionRatio(string $itemType, int $itemId, string $fromSatuan): float
    {
        // Try to get conversion from database table if exists
        try {
            $conversion = \App\Models\SatuanConversion::where(function($query) use ($itemType, $itemId, $fromSatuan) {
                $query->where(function($subQuery) use ($itemType, $itemId, $fromSatuan) {
                    // Find conversions where either source or target matches
                    $subQuery->where(function($subSubQuery) use ($fromSatuan) {
                        $subSubQuery->where('nama', 'like', '%' . $fromSatuan . '%');
                    });
                });
            })
                ->where(function($query) use ($itemId) {
                    $query->where('item_id', $itemId);
                })
                ->first();
                
            if ($conversion) {
                    // Determine conversion direction
                    if ($conversion->source_satuan_id == $conversion->target_satuan_id) {
                        return $conversion->amount_target / $conversion->amount_source;
                    } else {
                        return $conversion->amount_source / $conversion->amount_target;
                    }
            }
        } catch (\Exception $e) {
            // Use item-specific conversion data from bahan baku/bahan pendukung
            return $this->getItemSpecificConversionRatio($itemType, $itemId, $fromSatuan);
        }
        
        return 1.0; // Default: no conversion
    }
    
    /**
     * Get conversion ratio from item-specific data
     */
    private function getItemSpecificConversionRatio(string $itemType, int $itemId, string $fromSatuan): float
    {
        // Get the item and its conversion data
        $item = null;
        if ($itemType === 'material') {
            $item = \App\Models\BahanBaku::find($itemId);
        } elseif ($itemType === 'support') {
            $item = \App\Models\BahanPendukung::find($itemId);
        } elseif ($itemType === 'product') {
            $item = \App\Models\Produk::find($itemId);
        }
        
        if (!$item) {
            return $this->getFallbackConversionRatio($fromSatuan);
        }
        
        $primarySatuan = strtolower($item->satuan->nama ?? '');
        $fromSatuanLower = strtolower($fromSatuan);
        
        // Check if fromSatuan is the primary satuan
        if (str_contains($primarySatuan, $fromSatuanLower) || str_contains($fromSatuanLower, $primarySatuan)) {
            return 1.0;
        }
        
        // Only check sub-satuan for materials and supports (products don't have sub-satuan)
        if ($itemType !== 'product') {
            // Check sub satuan 1
            if ($item->sub_satuan_1_id) {
                $subSatuan1 = \App\Models\Satuan::find($item->sub_satuan_1_id);
                if ($subSatuan1 && str_contains(strtolower($subSatuan1->nama), $fromSatuanLower)) {
                    // sub_satuan_1_konversi: berapa primary unit = 1 sub unit
                    // sub_satuan_1_nilai: berapa sub unit = 1 primary unit
                    if ($item->sub_satuan_1_konversi > 0) {
                        return 1 / $item->sub_satuan_1_konversi; // Convert sub unit to primary
                    } elseif ($item->sub_satuan_1_nilai > 0) {
                        return $item->sub_satuan_1_nilai; // Convert sub unit to primary
                    }
                }
            }
            
            // Check sub satuan 2
            if ($item->sub_satuan_2_id) {
                $subSatuan2 = \App\Models\Satuan::find($item->sub_satuan_2_id);
                if ($subSatuan2 && str_contains(strtolower($subSatuan2->nama), $fromSatuanLower)) {
                    if ($item->sub_satuan_2_konversi > 0) {
                        return 1 / $item->sub_satuan_2_konversi;
                    } elseif ($item->sub_satuan_2_nilai > 0) {
                        return $item->sub_satuan_2_nilai;
                    }
                }
            }
            
            // Check sub satuan 3
            if ($item->sub_satuan_3_id) {
                $subSatuan3 = \App\Models\Satuan::find($item->sub_satuan_3_id);
                if ($subSatuan3 && str_contains(strtolower($subSatuan3->nama), $fromSatuanLower)) {
                    if ($item->sub_satuan_3_konversi > 0) {
                        return 1 / $item->sub_satuan_3_konversi;
                    } elseif ($item->sub_satuan_3_nilai > 0) {
                        return $item->sub_satuan_3_nilai;
                    }
                }
            }
        }
        
        // Fallback to common Indonesian conversions
        return $this->getFallbackConversionRatio($fromSatuan);
    }
    
    /**
     * Get fallback conversion ratio for common Indonesian units
     */
    private function getFallbackConversionRatio(string $fromSatuan): float
    {
        $fromSatuan = strtolower($fromSatuan);
        
        // Common Indonesian unit conversions
        $conversions = [
            'gram' => ['kg' => 0.001, 'kilogram' => 0.001, 'ons' => 0.1, 'potong' => 0.25],
            'kg' => ['gram' => 1000, 'kilogram' => 1000, 'ons' => 10, 'potong' => 4],
            'liter' => ['ml' => 0.001, 'cl' => 0.01],
            'meter' => ['cm' => 0.01, 'mm' => 0.001],
            'ons' => ['ekor' => 0.1, 'potong' => 0.25],
            'ekor' => ['potong' => 0.25, 'ons' => 0.1],
            'pcs' => ['lusin' => 1],
            'piece' => ['pcs' => 1],
        ];
        
        foreach ($conversions as $primaryUnit => $subUnits) {
            if (str_contains($fromSatuan, $primaryUnit)) {
                return $subUnits[$fromSatuan] ?? 1.0;
            }
        }
        
        return 1.0; // Default: no conversion
    }

    /**
     * Sync product stock to StockLayer if not exists
     */
    private function syncProductStockToLayer(int $productId): void
    {
        $layerExists = StockLayer::where('item_type', 'product')
            ->where('item_id', $productId)
            ->exists();
            
        if (!$layerExists) {
            $produk = \App\Models\Produk::find($productId);
            if ($produk && $produk->stok > 0) {
                // Calculate unit cost (estimate from selling price or use default)
                $unitCost = 0;
                if ($produk->harga_jual > 0) {
                    $unitCost = $produk->harga_jual / 1.3; // Estimate cost as 77% of selling price
                } else {
                    // Fallback to BOM cost if available
                    $sumBom = (float) \App\Models\Bom::where('produk_id', $productId)->sum('total_biaya');
                    $btkl = (float) ($produk->btkl_default ?? 0);
                    $bop = (float) ($produk->bop_default ?? 0);
                    $unitCost = $sumBom + $btkl + $bop;
                }
                
                StockLayer::create([
                    'item_type' => 'product',
                    'item_id' => $productId,
                    'tanggal' => now()->subDays(30), // Set to past date for opening balance
                    'remaining_qty' => $produk->stok,
                    'satuan' => 'pcs',
                    'unit_cost' => $unitCost,
                    'ref_type' => 'opening_balance',
                    'ref_id' => 0,
                ]);
                
                \Log::info("Auto-synced product #{$productId} stock to StockLayer: {$produk->stok} pcs, unit_cost: {$unitCost}");
            }
        }
    }

    public function consume(string $itemType, int $itemId, float $qty, string $satuan, string $refType, int $refId, string $tanggal): float
    {
        return DB::transaction(function () use ($itemType, $itemId, $qty, $satuan, $refType, $refId, $tanggal) {
            // Convert input quantity to primary unit first
            $primaryQty = $this->convertToPrimaryUnit($itemType, $itemId, $qty, $satuan);
            
            // Auto-sync product stock to StockLayer if not exists
            if ($itemType === 'product') {
                $this->syncProductStockToLayer($itemId);
            }
            
            // Get available stock after potential sync
            $available = (float) StockLayer::where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->sum('remaining_qty');
                
            // Strict check: do not allow consume beyond available
            if ($primaryQty > $available + 1e-9) {
                throw new \RuntimeException('Insufficient stock for '.$itemType.'#'.$itemId.' (need '.$primaryQty.', available '.$available.')');
            }

            // Lock the single (collapsed) layer; if multiple, collapse first
            $layer = StockLayer::where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            if (!$layer) {
                throw new \RuntimeException('Insufficient stock for '.$itemType.'#'.$itemId);
            }

            $unitCost = (float)($layer->unit_cost ?? 0);
            $totalCost = $primaryQty * $unitCost;

            $layer->remaining_qty = (float)$layer->remaining_qty - $primaryQty;
            if ($layer->remaining_qty <= 0) {
                $layer->delete();
            } else {
                $layer->save();
            }

            StockMovement::create([
                'item_type' => $itemType,
                'item_id' => $itemId,
                'tanggal' => $tanggal,
                'direction' => 'out',
                'qty' => $primaryQty,
                'satuan' => $this->getPrimarySatuan($itemType, $itemId),
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'ref_type' => $refType,
                'ref_id' => $refId,
            ]);

            return $totalCost;
        });
    }

    /**
     * Estimate total FIFO cost for a given quantity without mutating layers.
     */
    public function estimateCost(string $itemType, int $itemId, float $qty): float
    {
        if ($qty <= 0) return 0.0;

        $layers = StockLayer::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('remaining_qty', '>', 0)
            ->get();

        $totalQty = 0.0; $totalCost = 0.0;
        foreach ($layers as $ly) {
            $q = (float)($ly->remaining_qty ?? 0);
            $c = (float)($ly->unit_cost ?? 0);
            $totalQty += $q;
            $totalCost += $q * $c;
        }
        if ($totalQty <= 0) return 0.0;
        $avg = $totalCost / $totalQty;
        $est = min($qty, $totalQty) * $avg;
        return $est;
    }
}

