<?php

namespace App\Services;

use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\KartuStok;
use Illuminate\Support\Facades\Log;

class ReturValidationService
{
    /**
     * Validate that kartu_stok entries match purchase return data
     */
    public function validateReturStockConsistency($returId)
    {
        $retur = PurchaseReturn::with('items')->findOrFail($returId);
        $inconsistencies = [];

        foreach ($retur->items as $item) {
            $itemType = $item->bahan_baku_id ? 'bahan_baku' : 'bahan_pendukung';
            $itemId = $item->bahan_baku_id ?: $item->bahan_pendukung_id;

            // Check stock out entry (when goods are sent)
            if (in_array($retur->status, ['dikirim', 'selesai'])) {
                $stockOutEntry = KartuStok::where('ref_type', 'retur')
                    ->where('ref_id', $retur->id)
                    ->where('item_id', $itemId)
                    ->where('item_type', $itemType)
                    ->whereNotNull('qty_keluar')
                    ->first();

                if (!$stockOutEntry) {
                    $inconsistencies[] = "Missing stock out entry for item {$itemId} ({$itemType}) in return {$retur->id}";
                } elseif ($stockOutEntry->qty_keluar != $item->quantity) {
                    $inconsistencies[] = "Stock out quantity mismatch for item {$itemId}: expected {$item->quantity}, found {$stockOutEntry->qty_keluar}";
                }
            }

            // Check stock in entry (for tukar_barang when completed)
            if ($retur->status === 'selesai' && $retur->jenis_retur === 'tukar_barang') {
                $stockInEntry = KartuStok::where('ref_type', 'retur')
                    ->where('ref_id', $retur->id)
                    ->where('item_id', $itemId)
                    ->where('item_type', $itemType)
                    ->whereNotNull('qty_masuk')
                    ->first();

                if (!$stockInEntry) {
                    $inconsistencies[] = "Missing stock in entry for item {$itemId} ({$itemType}) in return {$retur->id}";
                } elseif ($stockInEntry->qty_masuk != $item->quantity) {
                    $inconsistencies[] = "Stock in quantity mismatch for item {$itemId}: expected {$item->quantity}, found {$stockInEntry->qty_masuk}";
                }
            }
        }

        return $inconsistencies;
    }

    /**
     * Validate all purchase returns for stock consistency
     */
    public function validateAllReturStockConsistency()
    {
        $allInconsistencies = [];
        $returns = PurchaseReturn::all();

        foreach ($returns as $return) {
            $inconsistencies = $this->validateReturStockConsistency($return->id);
            if (!empty($inconsistencies)) {
                $allInconsistencies[$return->id] = $inconsistencies;
            }
        }

        return $allInconsistencies;
    }

    /**
     * Fix missing kartu_stok entries for a return
     */
    public function fixMissingStockEntries($returId)
    {
        $retur = PurchaseReturn::with('items')->findOrFail($returId);
        $fixed = [];

        foreach ($retur->items as $item) {
            $itemType = $item->bahan_baku_id ? 'bahan_baku' : 'bahan_pendukung';
            $itemId = $item->bahan_baku_id ?: $item->bahan_pendukung_id;

            // Fix missing stock out entry
            if (in_array($retur->status, ['dikirim', 'selesai'])) {
                $stockOutEntry = KartuStok::where('ref_type', 'retur')
                    ->where('ref_id', $retur->id)
                    ->where('item_id', $itemId)
                    ->where('item_type', $itemType)
                    ->whereNotNull('qty_keluar')
                    ->first();

                if (!$stockOutEntry) {
                    KartuStok::create([
                        'item_id' => $itemId,
                        'item_type' => $itemType,
                        'tanggal' => $retur->return_date,
                        'qty_masuk' => null,
                        'qty_keluar' => $item->quantity,
                        'keterangan' => "Retur Pembelian - Kirim ke Vendor #{$retur->return_number} (Fixed)",
                        'ref_type' => 'retur',
                        'ref_id' => $retur->id,
                    ]);
                    $fixed[] = "Created missing stock out entry for item {$itemId}";
                }
            }

            // Fix missing stock in entry for completed tukar_barang
            if ($retur->status === 'selesai' && $retur->jenis_retur === 'tukar_barang') {
                $stockInEntry = KartuStok::where('ref_type', 'retur')
                    ->where('ref_id', $retur->id)
                    ->where('item_id', $itemId)
                    ->where('item_type', $itemType)
                    ->whereNotNull('qty_masuk')
                    ->first();

                if (!$stockInEntry) {
                    KartuStok::create([
                        'item_id' => $itemId,
                        'item_type' => $itemType,
                        'tanggal' => $retur->return_date,
                        'qty_masuk' => $item->quantity,
                        'qty_keluar' => null,
                        'keterangan' => "Retur Pembelian - Barang Pengganti #{$retur->return_number} (Fixed)",
                        'ref_type' => 'retur',
                        'ref_id' => $retur->id,
                    ]);
                    $fixed[] = "Created missing stock in entry for item {$itemId}";
                }
            }
        }

        return $fixed;
    }
}