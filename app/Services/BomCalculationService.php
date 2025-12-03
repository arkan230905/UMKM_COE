<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\Bom;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;

class BomCalculationService
{
    /**
     * Calculate the cost of a BOM item with unit conversion
     */
    public function calculateBomItemCost($bahanBakuId, $quantity, $unit)
    {
        $bahanBaku = BahanBaku::findOrFail($bahanBakuId);
        
        // Convert quantity to base unit (gram)
        $quantityInBaseUnit = $this->convertToBaseUnit($quantity, $unit, $bahanBaku);
        
        // Calculate cost in base unit
        $cost = $quantityInBaseUnit * $bahanBaku->harga_per_satuan_dasar;
        
        return [
            'bahan_baku_id' => $bahanBaku->id,
            'quantity' => $quantity,
            'unit' => $unit,
            'quantity_in_base_unit' => $quantityInBaseUnit,
            'base_unit' => $bahanBaku->satuan_dasar,
            'cost' => $cost,
            'cost_per_unit' => $cost / max(1, $quantity) // Prevent division by zero
        ];
    }
    
    /**
     * Convert quantity to base unit
     */
    protected function convertToBaseUnit($quantity, $fromUnit, BahanBaku $bahanBaku)
    {
        if ($fromUnit === $bahanBaku->satuan_dasar) {
            return $quantity;
        }
        
        // If the unit matches the display unit, use the conversion factor
        if ($fromUnit === $bahanBaku->satuan) {
            return $quantity * $bahanBaku->faktor_konversi;
        }
        
        // For other units, you might need to implement a more complex conversion logic
        // This is a simple example - adjust according to your needs
        return $quantity;
    }
    
    /**
     * Calculate total cost of a BOM
     */
    public function calculateBomCost($produkId, $quantity = 1)
    {
        $bomItems = Bom::where('produk_id', $produkId)->get();
        $totalCost = 0;
        $details = [];
        
        foreach ($bomItems as $item) {
            $calculation = $this->calculateBomItemCost(
                $item->bahan_baku_id,
                $item->jumlah * $quantity,
                $item->satuan_resep
            );
            
            $totalCost += $calculation['cost'];
            $details[] = $calculation;
        }
        
        return [
            'total_cost' => $totalCost,
            'details' => $details,
            'unit_cost' => $totalCost / max(1, $quantity) // Prevent division by zero
        ];
    }
    
    /**
     * Update product cost based on BOM
     */
    public function updateProductCost(Produk $produk)
    {
        $bomCost = $this->calculateBomCost($produk->id);
        
        $produk->harga_pokok = $bomCost['total_cost'];
        $produk->save();
        
        return $bomCost;
    }
}
