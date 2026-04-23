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

        // Create entry in kartu_stok (existing functionality)
        $kartuStokEntry = KartuStok::createEntry($data);
        
        // Also create entry in stock_movements for laporan stok
        $this->createStockMovementEntry($itemId, $itemType, $qtyMasuk, $qtyKeluar, $keterangan, $refType, $refId, $tanggal);
        
        return $kartuStokEntry;
    }

    /**
     * Create entry in stock_movements table for laporan stok
     */
    private function createStockMovementEntry($itemId, $itemType, $qtyMasuk, $qtyKeluar, $keterangan, $refType, $refId, $tanggal)
    {
        try {
            // Convert item_type from kartu_stok format to stock_movements format
            $stockMovementItemType = '';
            if ($itemType === KartuStok::ITEM_TYPE_BAHAN_BAKU) {
                $stockMovementItemType = 'material';
            } elseif ($itemType === KartuStok::ITEM_TYPE_BAHAN_PENDUKUNG) {
                $stockMovementItemType = 'support';
            } else {
                Log::warning('Unknown item_type for stock_movements', ['item_type' => $itemType]);
                return;
            }
            
            // Convert ref_type
            $stockMovementRefType = $refType;
            if ($refType === 'pembelian') {
                $stockMovementRefType = 'purchase';
            }
            
            // Determine direction and qty
            $direction = '';
            $qty = 0;
            
            if ($qtyMasuk > 0) {
                $direction = 'in';
                $qty = $qtyMasuk;
            } elseif ($qtyKeluar > 0) {
                $direction = 'out';
                $qty = $qtyKeluar;
            } else {
                Log::warning('No qty_masuk or qty_keluar for stock_movement', [
                    'item_id' => $itemId,
                    'item_type' => $itemType
                ]);
                return;
            }
            
            // Get unit cost from item master data
            $unitCost = 0;
            $totalCost = 0;
            
            if ($stockMovementItemType === 'material') {
                $bahanBaku = BahanBaku::find($itemId);
                $unitCost = $bahanBaku ? ($bahanBaku->harga_satuan ?? 0) : 0;
            } elseif ($stockMovementItemType === 'support') {
                $bahanPendukung = \App\Models\BahanPendukung::find($itemId);
                $unitCost = $bahanPendukung ? ($bahanPendukung->harga_satuan ?? 0) : 0;
            }
            
            $totalCost = $qty * $unitCost;
            
            // Check if stock_movement already exists to avoid duplicates
            $existingMovement = \App\Models\StockMovement::where('item_type', $stockMovementItemType)
                ->where('item_id', $itemId)
                ->where('ref_type', $stockMovementRefType)
                ->where('ref_id', $refId)
                ->where('tanggal', $tanggal ?? now()->format('Y-m-d'))
                ->where('direction', $direction)
                ->where('qty', $qty)
                ->first();
            
            if ($existingMovement) {
                Log::info('Stock movement already exists, skipping', [
                    'item_type' => $stockMovementItemType,
                    'item_id' => $itemId,
                    'ref_type' => $stockMovementRefType,
                    'ref_id' => $refId
                ]);
                return;
            }
            
            // Create stock_movement entry
            $stockMovement = \App\Models\StockMovement::create([
                'item_type' => $stockMovementItemType,
                'item_id' => $itemId,
                'tanggal' => $tanggal ?? now()->format('Y-m-d'),
                'ref_type' => $stockMovementRefType,
                'ref_id' => $refId,
                'direction' => $direction,
                'qty' => $qty,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'keterangan' => $keterangan
            ]);
            
            Log::info('Stock movement created', [
                'stock_movement_id' => $stockMovement->id,
                'item_type' => $stockMovementItemType,
                'item_id' => $itemId,
                'direction' => $direction,
                'qty' => $qty
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error creating stock movement entry', [
                'item_id' => $itemId,
                'item_type' => $itemType,
                'error' => $e->getMessage()
            ]);
            // Don't throw exception to avoid breaking the main flow
        }
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
                // Use jumlah_satuan_utama for stock (converted to main unit)
                // instead of jumlah (purchase unit)
                $stockQuantity = $detail->jumlah_satuan_utama ?? $detail->jumlah;
                
                $this->addStock(
                    $itemId,
                    $itemType,
                    $stockQuantity,
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
        // Convert item_type to stock_movements format
        $stockMovementItemType = '';
        if ($itemType === KartuStok::ITEM_TYPE_BAHAN_BAKU) {
            $stockMovementItemType = 'material';
        } elseif ($itemType === KartuStok::ITEM_TYPE_BAHAN_PENDUKUNG) {
            $stockMovementItemType = 'support';
        } else {
            // Fallback to kartu_stok if unknown type
            return $this->getStockReportFromKartuStok($itemId, $itemType, $startDate, $endDate);
        }

        // Use stock_movements for more accurate data including qty_as_input and satuan_as_input
        $query = \App\Models\StockMovement::where('item_type', $stockMovementItemType)
            ->where('item_id', $itemId)
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
            // Get balance before start date from stock_movements
            $beforeEntries = \App\Models\StockMovement::where('item_type', $stockMovementItemType)
                ->where('item_id', $itemId)
                ->where('tanggal', '<', $startDate)
                ->get();
            
            foreach ($beforeEntries as $entry) {
                if ($entry->direction === 'in') {
                    $runningBalance += (float)$entry->qty;
                } else {
                    $runningBalance -= (float)$entry->qty;
                }
            }
        }

        $report = [];
        foreach ($entries as $entry) {
            // Update running balance
            if ($entry->direction === 'in') {
                $runningBalance += (float)$entry->qty;
                $qtyMasuk = (float)$entry->qty;
                $qtyKeluar = 0;
            } else {
                $runningBalance -= (float)$entry->qty;
                $qtyMasuk = 0;
                $qtyKeluar = (float)$entry->qty;
            }

            // Generate keterangan based on ref_type
            $keterangan = '';
            switch ($entry->ref_type) {
                case 'initial_stock':
                    $keterangan = 'Saldo Awal';
                    break;
                case 'purchase':
                    $keterangan = 'Pembelian';
                    break;
                case 'production':
                    // For production, show the original qty and satuan from transaction
                    if ($entry->qty_as_input && $entry->satuan_as_input) {
                        $keterangan = 'Pemakaian Produksi - ' . number_format($entry->qty_as_input, 2) . ' ' . $entry->satuan_as_input;
                    } else {
                        $keterangan = 'Pemakaian Produksi';
                    }
                    break;
                case 'retur':
                    $keterangan = 'Retur Pembelian';
                    break;
                case 'adjustment':
                    $keterangan = 'Penyesuaian Stok';
                    break;
                default:
                    $keterangan = ucfirst(str_replace('_', ' ', $entry->ref_type));
            }
            
            $report[] = [
                'tanggal' => \Carbon\Carbon::parse($entry->tanggal)->format('d/m/Y'),
                'keterangan' => $keterangan,
                'qty_masuk' => $qtyMasuk,
                'qty_keluar' => $qtyKeluar,
                'saldo' => $runningBalance,
                'ref_type' => $entry->ref_type,
                'ref_id' => $entry->ref_id,
                'qty_as_input' => $entry->qty_as_input,
                'satuan_as_input' => $entry->satuan_as_input
            ];
        }

        // Calculate saldo awal
        $totalMovements = 0;
        foreach ($entries as $entry) {
            if ($entry->direction === 'in') {
                $totalMovements += (float)$entry->qty;
            } else {
                $totalMovements -= (float)$entry->qty;
            }
        }

        return [
            'saldo_awal' => $runningBalance - $totalMovements,
            'entries' => $report,
            'saldo_akhir' => $runningBalance
        ];
    }

    /**
     * Fallback method using kartu_stok table
     */
    private function getStockReportFromKartuStok($itemId, $itemType, $startDate = null, $endDate = null)
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
            // Check if stock movement already exists for this purchase and item
            $stockMovementItemType = $itemType === 'support' ? 'support' : ($itemType === 'material' ? 'material' : $itemType);
            $stockMovementRefType = $refType === 'pembelian' ? 'purchase' : $refType;
            
            $existingMovement = \App\Models\StockMovement::where('item_type', $stockMovementItemType)
                ->where('item_id', $itemId)
                ->where('ref_type', $stockMovementRefType)
                ->where('ref_id', $refId)
                ->where('direction', 'in')
                ->first();
                
            if ($existingMovement) {
                Log::info('Stock movement already exists, skipping creation', [
                    'item_type' => $stockMovementItemType,
                    'item_id' => $itemId,
                    'ref_type' => $stockMovementRefType,
                    'ref_id' => $refId,
                    'existing_movement_id' => $existingMovement->id
                ]);
                
                // Still create stock layer if it doesn't exist
                $existingLayer = \App\Models\StockLayer::where('item_type', $itemType)
                    ->where('item_id', $itemId)
                    ->where('ref_type', $refType)
                    ->where('ref_id', $refId)
                    ->first();
                    
                if (!$existingLayer) {
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
                    
                    $stockLayer = \App\Models\StockLayer::create($data);
                    return $stockLayer;
                }
                
                return $existingLayer;
            }
            
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

            $stockLayer = \App\Models\StockLayer::create($data);
            
            $totalCost = $qty * $unitCost;
            
            \App\Models\StockMovement::create([
                'item_type' => $stockMovementItemType,
                'item_id' => $itemId,
                'tanggal' => $tanggal ?? now()->format('Y-m-d'),
                'ref_type' => $stockMovementRefType,
                'ref_id' => $refId,
                'direction' => 'in',
                'qty' => $qty,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'keterangan' => 'Pembelian',
                'manual_conversion_data' => $manualConversionData ? json_encode($manualConversionData) : null
            ]);
            
            Log::info('Stock movement created with manual conversion data', [
                'item_type' => $stockMovementItemType,
                'item_id' => $itemId,
                'manual_conversion_data' => $manualConversionData
            ]);
            
            return $stockLayer;
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
     * Estimate cost for items using FIFO from stock layers
     */
    public function estimateCost($itemType, $itemId, $qty)
    {
        try {
            if ($itemType === 'product') {
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
     * Sync product stock to StockLayer - force sync even if exists
     */
    private function syncProductStockToLayer($productId)
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