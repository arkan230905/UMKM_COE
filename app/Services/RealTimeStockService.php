<?php

namespace App\Services;

use App\Models\StockLayer;
use App\Models\StockMovement;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RealTimeStockService
{
    /**
     * Handle stock movement for purchases (IN)
     */
    public function handlePurchase($pembelianDetail)
    {
        try {
            DB::beginTransaction();
            
            // Map item types to database enum values
            $itemType = $pembelianDetail->tipe_item === 'bahan_baku' ? 'material' : 'support';
            $itemId = $pembelianDetail->tipe_item === 'bahan_baku' 
                ? $pembelianDetail->bahan_baku_id 
                : $pembelianDetail->bahan_pendukung_id;
            
            $qty = $pembelianDetail->jumlah;
            $satuan = $pembelianDetail->satuan;
            $unitCost = $pembelianDetail->harga_satuan;
            $tanggal = $pembelianDetail->pembelian->tanggal->format('Y-m-d');
            
            // Add stock layer
            $this->addStockLayer(
                $itemType,
                $itemId,
                $qty,
                $satuan,
                $unitCost,
                'purchase',
                $pembelianDetail->pembelian_id,
                $tanggal
            );
            
            // Update model stock
            $this->updateModelStock($itemType, $itemId);
            
            DB::commit();
            
            Log::info("Stock added via purchase", [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'qty' => $qty,
                'unit_cost' => $unitCost,
                'purchase_id' => $pembelianDetail->pembelian_id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to handle purchase stock movement", [
                'error' => $e->getMessage(),
                'pembelian_detail_id' => $pembelianDetail->id
            ]);
            throw $e;
        }
    }
    
    /**
     * Handle stock movement for production (OUT for materials, IN for products)
     */
    public function handleProduction($produksiDetail)
    {
        try {
            DB::beginTransaction();
            
            $produksi = $produksiDetail->produksi;
            $produk = $produksiDetail->produk;
            $qtyProduksi = $produksiDetail->jumlah_produksi;
            $tanggal = $produksi->tanggal->format('Y-m-d');
            
            // 1. Consume materials based on BOM
            $this->consumeMaterialsForProduction($produk->id, $qtyProduksi, $tanggal, $produksi->id);
            
            // 2. Add finished product to stock
            $this->addStockLayer(
                'product',
                $produk->id,
                $qtyProduksi,
                $produk->satuan->nama ?? 'Unit',
                $produk->harga_bom ?? 0, // Use BOM cost as unit cost
                'production',
                $produksi->id,
                $tanggal
            );
            
            // 3. Update product stock
            $this->updateModelStock('product', $produk->id);
            
            DB::commit();
            
            Log::info("Stock updated via production", [
                'produk_id' => $produk->id,
                'qty_produced' => $qtyProduksi,
                'production_id' => $produksi->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to handle production stock movement", [
                'error' => $e->getMessage(),
                'produksi_detail_id' => $produksiDetail->id
            ]);
            throw $e;
        }
    }
    
    /**
     * Handle stock movement for sales (OUT for products)
     */
    public function handleSale($penjualanDetail)
    {
        try {
            DB::beginTransaction();
            
            $produk = $penjualanDetail->produk;
            $qty = $penjualanDetail->jumlah;
            $tanggal = $penjualanDetail->penjualan->tanggal->format('Y-m-d');
            
            // Consume product stock
            $totalCost = $this->consumeStock(
                'product',
                $produk->id,
                $qty,
                $produk->satuan->nama ?? 'Unit',
                'sale',
                $penjualanDetail->penjualan_id,
                $tanggal
            );
            
            // Update product stock
            $this->updateModelStock('product', $produk->id);
            
            DB::commit();
            
            Log::info("Stock consumed via sale", [
                'produk_id' => $produk->id,
                'qty_sold' => $qty,
                'total_cost' => $totalCost,
                'sale_id' => $penjualanDetail->penjualan_id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to handle sale stock movement", [
                'error' => $e->getMessage(),
                'penjualan_detail_id' => $penjualanDetail->id
            ]);
            throw $e;
        }
    }
    
    /**
     * Add stock layer (for purchases and production)
     */
    private function addStockLayer($itemType, $itemId, $qty, $satuan, $unitCost, $refType, $refId, $tanggal)
    {
        // Record movement IN
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
        
        // Update or create stock layer using moving average
        $existingLayer = StockLayer::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->first();
        
        if ($existingLayer) {
            // Calculate new moving average
            $oldQty = $existingLayer->remaining_qty;
            $oldCost = $existingLayer->unit_cost;
            $oldTotalCost = $oldQty * $oldCost;
            
            $newQty = $oldQty + $qty;
            $newTotalCost = $oldTotalCost + ($qty * $unitCost);
            $newAvgCost = $newQty > 0 ? $newTotalCost / $newQty : 0;
            
            $existingLayer->update([
                'remaining_qty' => $newQty,
                'unit_cost' => $newAvgCost,
                'tanggal' => $tanggal
            ]);
        } else {
            // Create new layer
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
        }
    }
    
    /**
     * Consume stock (for sales and production)
     */
    private function consumeStock($itemType, $itemId, $qty, $satuan, $refType, $refId, $tanggal)
    {
        $layer = StockLayer::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->lockForUpdate()
            ->first();
        
        if (!$layer || $layer->remaining_qty < $qty) {
            throw new \RuntimeException("Insufficient stock for {$itemType} ID {$itemId}. Available: " . ($layer->remaining_qty ?? 0) . ", Required: {$qty}");
        }
        
        $unitCost = $layer->unit_cost;
        $totalCost = $qty * $unitCost;
        
        // Update layer
        $layer->remaining_qty -= $qty;
        if ($layer->remaining_qty <= 0) {
            $layer->delete();
        } else {
            $layer->save();
        }
        
        // Record movement OUT
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
    }
    
    /**
     * Consume materials for production based on BOM
     */
    private function consumeMaterialsForProduction($produkId, $qtyProduksi, $tanggal, $produksiId)
    {
        // Get BOM data
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produkId)
            ->with(['detailBBB.bahanBaku', 'detailBahanPendukung.bahanPendukung'])
            ->first();
        
        if (!$bomJobCosting) {
            throw new \RuntimeException("No BOM found for product ID {$produkId}");
        }
        
        // Consume bahan baku
        foreach ($bomJobCosting->detailBBB as $detail) {
            $qtyNeeded = $detail->jumlah * $qtyProduksi;
            $this->consumeStock(
                'material',
                $detail->bahan_baku_id,
                $qtyNeeded,
                $detail->satuan,
                'production_material',
                $produksiId,
                $tanggal
            );
            
            // Update bahan baku stock
            $this->updateModelStock('material', $detail->bahan_baku_id);
        }
        
        // Consume bahan pendukung
        foreach ($bomJobCosting->detailBahanPendukung as $detail) {
            $qtyNeeded = $detail->jumlah * $qtyProduksi;
            $this->consumeStock(
                'support',
                $detail->bahan_pendukung_id,
                $qtyNeeded,
                $detail->satuan,
                'production_material',
                $produksiId,
                $tanggal
            );
            
            // Update bahan pendukung stock
            $this->updateModelStock('support', $detail->bahan_pendukung_id);
        }
    }
    
    /**
     * Update model stock field based on stock layers
     */
    private function updateModelStock($itemType, $itemId)
    {
        $totalStock = StockLayer::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->sum('remaining_qty');
        
        switch ($itemType) {
            case 'material':
                BahanBaku::where('id', $itemId)->update(['stok' => $totalStock]);
                break;
            case 'support':
                BahanPendukung::where('id', $itemId)->update(['stok' => $totalStock]);
                break;
            case 'product':
                Produk::where('id', $itemId)->update(['stok' => $totalStock]);
                break;
        }
    }
    
    /**
     * Get current stock for an item
     */
    public function getCurrentStock($itemType, $itemId)
    {
        return StockLayer::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->sum('remaining_qty');
    }
    
    /**
     * Get stock movements for an item
     */
    public function getStockMovements($itemType, $itemId, $limit = 50)
    {
        return StockMovement::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get stock report for all items
     */
    public function getStockReport()
    {
        $report = [];
        
        // Bahan Baku
        $bahanBakus = BahanBaku::with('satuan')->get();
        foreach ($bahanBakus as $item) {
            $currentStock = $this->getCurrentStock('material', $item->id);
            $report['bahan_baku'][] = [
                'id' => $item->id,
                'nama' => $item->nama_bahan,
                'satuan' => $item->satuan->nama ?? 'Unit',
                'stok_sistem' => $item->stok,
                'stok_realtime' => $currentStock,
                'stok_minimum' => $item->stok_minimum,
                'status' => $this->getStockStatus($currentStock, $item->stok_minimum),
                'harga_rata_rata' => $this->getAveragePrice('material', $item->id)
            ];
        }
        
        // Bahan Pendukung
        $bahanPendukungs = BahanPendukung::with('satuan')->get();
        foreach ($bahanPendukungs as $item) {
            $currentStock = $this->getCurrentStock('support', $item->id);
            $report['bahan_pendukung'][] = [
                'id' => $item->id,
                'nama' => $item->nama_bahan,
                'satuan' => $item->satuan->nama ?? 'Unit',
                'stok_sistem' => $item->stok,
                'stok_realtime' => $currentStock,
                'stok_minimum' => $item->stok_minimum,
                'status' => $this->getStockStatus($currentStock, $item->stok_minimum),
                'harga_rata_rata' => $this->getAveragePrice('support', $item->id)
            ];
        }
        
        // Produk
        $produks = Produk::with('satuan')->get();
        foreach ($produks as $item) {
            $currentStock = $this->getCurrentStock('product', $item->id);
            $report['produk'][] = [
                'id' => $item->id,
                'nama' => $item->nama_produk,
                'satuan' => $item->satuan->nama ?? 'Unit',
                'stok_sistem' => $item->stok,
                'stok_realtime' => $currentStock,
                'harga_bom' => $item->harga_bom,
                'harga_jual' => $item->harga_jual,
                'harga_rata_rata' => $this->getAveragePrice('product', $item->id)
            ];
        }
        
        return $report;
    }
    
    /**
     * Get stock status based on minimum stock
     */
    private function getStockStatus($currentStock, $minimumStock)
    {
        if ($currentStock <= 0) {
            return 'habis';
        } elseif ($currentStock <= $minimumStock) {
            return 'menipis';
        } else {
            return 'aman';
        }
    }
    
    /**
     * Get average price from stock layers
     */
    private function getAveragePrice($itemType, $itemId)
    {
        $layer = StockLayer::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->first();
        
        return $layer ? $layer->unit_cost : 0;
    }
}