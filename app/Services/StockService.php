<?php

namespace App\Services;

use App\Models\KartuStok;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\StockLayer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    /**
     * Add stock (qty_masuk)
     */
    public function addStock($itemId, $itemType, $qty, $keterangan, $refType = null, $refId = null, $tanggal = null)
    {
        return $this->createStockEntry($itemId, $itemType, $qty, null, $keterangan, $refType, $refId, $tanggal);
    }

    /**
     * Reduce stock (qty_keluar)
     */
    public function reduceStock($itemId, $itemType, $qty, $keterangan, $refType = null, $refId = null, $tanggal = null)
    {
        return $this->createStockEntry($itemId, $itemType, null, $qty, $keterangan, $refType, $refId, $tanggal);
    }

    /**
     * Create stock entry
     */
    private function createStockEntry($itemId, $itemType, $qtyMasuk, $qtyKeluar, $keterangan, $refType, $refId, $tanggal)
    {
        // Validate item type
        if (!in_array($itemType, [KartuStok::ITEM_TYPE_BAHAN_BAKU, KartuStok::ITEM_TYPE_BAHAN_PENDUKUNG])) {
            throw new \InvalidArgumentException('Invalid item_type: ' . $itemType);
        }

        // Validate item exists
        $this->validateItemExists($itemId, $itemType);

        // Check stock availability for reduction
        if ($qtyKeluar > 0) {
            $currentStock = $this->getCurrentStock($itemId, $itemType);
            if ($currentStock < $qtyKeluar) {
                throw new \Exception("Insufficient stock. Current: {$currentStock}, Required: {$qtyKeluar}");
            }
        }

        $data = [
            'tanggal' => $tanggal ?? now()->format('Y-m-d'),
            'item_id' => $itemId,
            'item_type' => $itemType,
            'qty_masuk' => $qtyMasuk,
            'qty_keluar' => $qtyKeluar,
            'keterangan' => $keterangan,
            'ref_type' => $refType,
            'ref_id' => $refId
        ];

        Log::info('Creating stock entry', $data);

        return KartuStok::createEntry($data);
    }

    /**
     * Validate that item exists
     */
    private function validateItemExists($itemId, $itemType)
    {
        if ($itemType === KartuStok::ITEM_TYPE_BAHAN_BAKU) {
            if (!BahanBaku::find($itemId)) {
                throw new \Exception("Bahan Baku with ID {$itemId} not found");
            }
        } elseif ($itemType === KartuStok::ITEM_TYPE_BAHAN_PENDUKUNG) {
            if (!BahanPendukung::find($itemId)) {
                throw new \Exception("Bahan Pendukung with ID {$itemId} not found");
            }
        }
    }

    /**
     * Get current stock balance
     */
    public function getCurrentStock($itemId, $itemType)
    {
        return KartuStok::getStockBalance($itemId, $itemType);
    }

    /**
     * Process purchase stock entry
     */
    public function processPurchase($pembelianId)
    {
        $pembelian = \App\Models\Pembelian::with('details')->find($pembelianId);
        if (!$pembelian) {
            throw new \Exception("Pembelian with ID {$pembelianId} not found");
        }

        foreach ($pembelian->details as $detail) {
            $itemType = null;
            $itemId = null;

            if ($detail->bahan_baku_id) {
                $itemType = KartuStok::ITEM_TYPE_BAHAN_BAKU;
                $itemId = $detail->bahan_baku_id;
            } elseif ($detail->bahan_pendukung_id) {
                $itemType = KartuStok::ITEM_TYPE_BAHAN_PENDUKUNG;
                $itemId = $detail->bahan_pendukung_id;
            }

            if ($itemType && $itemId) {
                $this->addStock(
                    $itemId,
                    $itemType,
                    $detail->jumlah,
                    "Pembelian #{$pembelian->nomor_pembelian}",
                    KartuStok::REF_TYPE_PEMBELIAN,
                    $pembelianId,
                    $pembelian->tanggal->format('Y-m-d')
                );
            }
        }
    }

    /**
     * Process return stock entry (when goods are sent to vendor)
     */
    public function processReturnSent($returnId)
    {
        $return = \App\Models\PurchaseReturn::with(['items', 'pembelian'])->find($returnId);
        if (!$return) {
            throw new \Exception("Purchase Return with ID {$returnId} not found");
        }

        foreach ($return->items as $item) {
            $itemType = null;
            $itemId = null;

            if ($item->bahan_baku_id) {
                $itemType = KartuStok::ITEM_TYPE_BAHAN_BAKU;
                $itemId = $item->bahan_baku_id;
            } elseif ($item->bahan_pendukung_id) {
                $itemType = KartuStok::ITEM_TYPE_BAHAN_PENDUKUNG;
                $itemId = $item->bahan_pendukung_id;
            }

            if ($itemType && $itemId) {
                $this->reduceStock(
                    $itemId,
                    $itemType,
                    $item->quantity,
                    "Retur Pembelian #{$return->return_number}",
                    KartuStok::REF_TYPE_RETUR,
                    $returnId,
                    $return->return_date->format('Y-m-d')
                );
            }
        }
    }

    /**
     * Process return completion (when replacement goods are received - only for tukar_barang)
     */
    public function processReturnCompleted($returnId)
    {
        $return = \App\Models\PurchaseReturn::with(['items', 'pembelian'])->find($returnId);
        if (!$return) {
            throw new \Exception("Purchase Return with ID {$returnId} not found");
        }

        // Only process stock entry for tukar_barang
        if ($return->jenis_retur !== \App\Models\PurchaseReturn::JENIS_TUKAR_BARANG) {
            return;
        }

        foreach ($return->items as $item) {
            $itemType = null;
            $itemId = null;

            if ($item->bahan_baku_id) {
                $itemType = KartuStok::ITEM_TYPE_BAHAN_BAKU;
                $itemId = $item->bahan_baku_id;
            } elseif ($item->bahan_pendukung_id) {
                $itemType = KartuStok::ITEM_TYPE_BAHAN_PENDUKUNG;
                $itemId = $item->bahan_pendukung_id;
            }

            if ($itemType && $itemId) {
                $this->addStock(
                    $itemId,
                    $itemType,
                    $item->quantity,
                    "Barang Pengganti dari Retur #{$return->return_number}",
                    KartuStok::REF_TYPE_RETUR,
                    $returnId,
                    now()->format('Y-m-d')
                );
            }
        }
    }

    /**
     * Get stock report for an item
     */
    public function getStockReport($itemId, $itemType, $startDate = null, $endDate = null)
    {
        $query = KartuStok::forItem($itemId, $itemType)
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc');

        if ($startDate) {
            $query->where('tanggal', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('tanggal', '<=', $endDate);
        }

        $entries = $query->get();

        // Calculate running balance
        $runningBalance = 0;
        if ($startDate) {
            // Get balance before start date
            $runningBalance = KartuStok::getStockBalance($itemId, $itemType, 
                \Carbon\Carbon::parse($startDate)->subDay()->format('Y-m-d'));
        }

        $report = [];
        foreach ($entries as $entry) {
            $runningBalance += ($entry->qty_masuk ?? 0) - ($entry->qty_keluar ?? 0);
            
            $report[] = [
                'tanggal' => $entry->tanggal->format('d/m/Y'),
                'keterangan' => $entry->keterangan,
                'qty_masuk' => $entry->qty_masuk ?? 0,
                'qty_keluar' => $entry->qty_keluar ?? 0,
                'saldo' => $runningBalance,
                'ref_type' => $entry->ref_type,
                'ref_id' => $entry->ref_id
            ];
        }

        return [
            'saldo_awal' => $runningBalance - $entries->sum(function($e) { 
                return ($e->qty_masuk ?? 0) - ($e->qty_keluar ?? 0); 
            }),
            'entries' => $report,
            'saldo_akhir' => $runningBalance
        ];
    }

    /**
     * Add stock layer with manual conversion data
     */
    public function addLayerWithManualConversion($itemType, $itemId, $qty, $satuan, $unitCost, $refType, $refId, $tanggal, $manualConversionData = null)
    {
        try {
            $data = [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'tanggal' => $tanggal ?? now()->format('Y-m-d'),
                'remaining_qty' => $qty,
                'unit_cost' => $unitCost,
                'satuan' => $satuan,
                'ref_type' => $refType,
                'ref_id' => $refId
            ];

            Log::info('Creating stock layer with manual conversion', array_merge($data, [
                'manual_conversion_data' => $manualConversionData
            ]));

            return StockLayer::create($data);
        } catch (\Exception $e) {
            Log::error('Failed to create stock layer with manual conversion', [
                'error' => $e->getMessage(),
                'item_type' => $itemType,
                'item_id' => $itemId,
                'qty' => $qty
            ]);
            throw $e;
        }
    }

    /**
     * Sync product stock to StockLayer - force sync even if exists
     */
    public function estimateCost($itemType, $itemId, $qty)
    {
        $produk = \App\Models\Produk::find($productId);
        if (!$produk) {
            return;
        }
        
        $currentStockLayer = StockLayer::where('item_type', 'product')
            ->where('item_id', $productId)
            ->sum('remaining_qty');
            
        $productStok = (float) $produk->stok;
        
        // Force sync if there's a difference
        if (abs($currentStockLayer - $productStok) > 1e-9) {
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
            
            // Delete existing layers and create new one
            StockLayer::where('item_type', 'product')
                ->where('item_id', $productId)
                ->delete();
                
            if ($productStok > 0) {
                StockLayer::create([
                    'item_type' => 'product',
                    'item_id' => $productId,
                    'tanggal' => now()->subDays(30), // Set to past date for opening balance
                    'remaining_qty' => $productStok,
                    'satuan' => 'pcs',
                    'unit_cost' => $unitCost,
                    'ref_type' => 'opening_balance',
                    'ref_id' => 0,
                ]);
            }
            
            \Log::info("Force-synced product #{$productId} stock to StockLayer: {$productStok} pcs (was {$currentStockLayer}), unit_cost: {$unitCost}");
        }
    }
    
    /**
     * Public method to force sync product stock to StockLayer
     */
    public function forceSyncProductStock(int $productId): void
    {
        $this->syncProductStockToLayer($productId);
    }

                // Try to get cost from stock layers first
                $totalCost = 0;
                $remainingQty = $qty;

                // Get stock layers ordered by date (FIFO)
                $layers = \App\Models\StockLayer::where('item_type', 'product')
                    ->where('item_id', $itemId)
                    ->where('remaining_qty', '>', 0)
                    ->orderBy('tanggal', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($layers as $layer) {
                    if ($remainingQty <= 0) break;

                    $useQty = min($layer->remaining_qty, $remainingQty);
                    $totalCost += $useQty * $layer->unit_cost;
                    $remainingQty -= $useQty;
                }

                // If we still need more quantity, use BOM cost
                if ($remainingQty > 0) {
                    $bomCost = $this->calculateBOMCost($itemId);
                    $totalCost += $remainingQty * $bomCost;
                }

                return $totalCost;
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Error estimating cost', [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'qty' => $qty,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Calculate BOM cost for a product
     */
    private function calculateBOMCost($productId)
    {
        try {
            $bomTotal = \App\Models\Bom::where('produk_id', $productId)->sum('total_biaya');
            $product = \App\Models\Produk::find($productId);
            
            $btkl = $product->btkl_default ?? 0;
            $bop = $product->bop_default ?? 0;
            
            return $bomTotal + $btkl + $bop;
        } catch (\Exception $e) {
            Log::error('Error calculating BOM cost', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Consume stock (for sales/production)
     */
    public function consume($itemType, $itemId, $qty, $satuan = 'pcs', $refType = null, $refId = null, $tanggal = null)
    {
        try {
            if ($itemType === 'product') {
                $product = \App\Models\Produk::find($itemId);
                if (!$product) {
                    throw new \Exception("Product with ID {$itemId} not found");
                }

                // Check current stock
                $currentStock = $product->stok ?? 0;
                if ($currentStock < $qty) {
                    throw new \Exception("Insufficient stock. Current: {$currentStock}, Required: {$qty}");
                }

                // Calculate cost using FIFO
                $totalCost = 0;
                $remainingQty = $qty;

                // Get stock layers for FIFO cost calculation
                $layers = \App\Models\StockLayer::where('item_type', 'product')
                    ->where('item_id', $itemId)
                    ->where('remaining_qty', '>', 0)
                    ->orderBy('tanggal', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($layers as $layer) {
                    if ($remainingQty <= 0) break;

                    $useQty = min($layer->remaining_qty, $remainingQty);
                    $totalCost += $useQty * $layer->unit_cost;
                    
                    // Update layer remaining quantity
                    $layer->remaining_qty -= $useQty;
                    $layer->save();
                    
                    $remainingQty -= $useQty;
                }

                // If no layers found, use BOM cost
                if ($totalCost == 0 && $qty > 0) {
                    $bomCost = $this->calculateBOMCost($itemId);
                    $totalCost = $qty * $bomCost;
                }

                // Update product stock
                $product->stok = $currentStock - $qty;
                $product->save();

                // Create stock movement record
                \DB::table('stock_movements')->insert([
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'direction' => 'out',
                    'qty' => $qty,
                    'satuan' => $satuan,
                    'ref_type' => $refType,
                    'ref_id' => $refId,
                    'tanggal' => $tanggal ?? now()->format('Y-m-d'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return $totalCost;
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Error consuming stock', [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'qty' => $qty,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}