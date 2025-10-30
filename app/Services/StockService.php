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

    public function addLayer(string $itemType, int $itemId, float $qty, string $satuan, float $unitCost, string $refType, int $refId, string $tanggal): void
    {
        DB::transaction(function () use ($itemType, $itemId, $qty, $satuan, $unitCost, $refType, $refId, $tanggal) {
            // Record movement IN first
            StockMovement::create([
                'item_type' => $itemType,
                'item_id' => $itemId,
                'tanggal' => $tanggal,
                'direction' => 'in',
                'qty' => $qty,
                'satuan' => $satuan,
                'unit_cost' => $unitCost,
                'total_cost' => $unitCost * $qty,
                'ref_type' => $refType,
                'ref_id' => $refId,
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
            $newQty = max($oldQty + $qty, 0.0);
            $newCost = max($oldCost + ($qty * $unitCost), 0.0);
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
                    'satuan' => $satuan,
                    'ref_type' => 'avg_layer',
                    'ref_id' => 0,
                ]);
            }
        });
    }

    public function consume(string $itemType, int $itemId, float $qty, string $satuan, string $refType, int $refId, string $tanggal): float
    {
        return DB::transaction(function () use ($itemType, $itemId, $qty, $satuan, $refType, $refId, $tanggal) {
            // Strict check: do not allow consume beyond available
            $available = (float) StockLayer::where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->sum('remaining_qty');
            if ($qty > $available + 1e-9) {
                throw new \RuntimeException('Insufficient stock for '.$itemType.'#'.$itemId.' (need '.$qty.', available '.$available.')');
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
            $totalCost = $qty * $unitCost;

            $layer->remaining_qty = (float)$layer->remaining_qty - $qty;
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
                'qty' => $qty,
                'satuan' => $satuan,
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

