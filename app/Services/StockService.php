<?php

namespace App\Services;

use App\Models\StockLayer;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function addLayer(string $itemType, int $itemId, float $qty, string $satuan, float $unitCost, string $refType, int $refId, string $tanggal): void
    {
        DB::transaction(function () use ($itemType, $itemId, $qty, $satuan, $unitCost, $refType, $refId, $tanggal) {
            StockLayer::create([
                'item_type' => $itemType,
                'item_id' => $itemId,
                'tanggal' => $tanggal,
                'remaining_qty' => $qty,
                'unit_cost' => $unitCost,
                'satuan' => $satuan,
                'ref_type' => $refType,
                'ref_id' => $refId,
            ]);

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
        });
    }

    public function consume(string $itemType, int $itemId, float $qty, string $satuan, string $refType, int $refId, string $tanggal): float
    {
        // FIFO: ambil layer berdasarkan tanggal ASC
        $remaining = $qty;
        $totalCost = 0.0;

        $layers = StockLayer::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('remaining_qty', '>', 0)
            ->orderBy('tanggal', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($layers as $layer) {
            if ($remaining <= 0) break;
            $take = min($remaining, (float)$layer->remaining_qty);
            $totalCost += $take * (float)$layer->unit_cost;
            $layer->remaining_qty = (float)$layer->remaining_qty - $take;
            $layer->save();
            $remaining -= $take;
        }

        if ($remaining > 0) {
            // Tidak cukup layer, konsumsi sisanya dengan biaya 0
            $take = $remaining;
            $totalCost += 0;
            $remaining = 0;
        }

        StockMovement::create([
            'item_type' => $itemType,
            'item_id' => $itemId,
            'tanggal' => $tanggal,
            'direction' => 'out',
            'qty' => $qty,
            'satuan' => $satuan,
            'unit_cost' => null,
            'total_cost' => $totalCost,
            'ref_type' => $refType,
            'ref_id' => $refId,
        ]);

        return $totalCost;
    }

    /**
     * Estimate total FIFO cost for a given quantity without mutating layers.
     */
    public function estimateCost(string $itemType, int $itemId, float $qty): float
    {
        $remaining = $qty;
        $totalCost = 0.0;
        if ($remaining <= 0) return 0.0;

        $layers = StockLayer::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('remaining_qty', '>', 0)
            ->orderBy('tanggal', 'asc')
            ->get();

        foreach ($layers as $layer) {
            if ($remaining <= 0) break;
            $take = min($remaining, (float)$layer->remaining_qty);
            $totalCost += $take * (float)$layer->unit_cost;
            $remaining -= $take;
        }

        // Jika stok layer tidak cukup, sisa dianggap biaya 0
        return $totalCost;
    }
}
