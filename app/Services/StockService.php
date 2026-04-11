<?php

namespace App\Services;

use App\Models\KartuStok;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
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
}