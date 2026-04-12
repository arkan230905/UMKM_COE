<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockMovement;
use App\Models\BomJobBahanPendukung;
use App\Models\BomJobBbb;
use App\Models\BahanPendukung;
use App\Models\BahanBaku;
use Illuminate\Support\Facades\DB;

class UpdateHppFromStockMovements extends Command
{
    protected $signature = 'hpp:update-from-stock {--item-type=} {--item-id=} {--dry-run}';
    protected $description = 'Update HPP prices in BOM tables based on latest stock movement prices';

    public function handle()
    {
        $itemType = $this->option('item-type');
        $itemId = $this->option('item-id');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🚀 Starting HPP update from stock movements...');

        // Get latest purchase prices from stock movements
        $query = StockMovement::where('ref_type', 'purchase')
            ->where('direction', 'in')
            ->where('qty', '>', 0)
            ->where('total_cost', '>', 0);

        if ($itemType) {
            $query->where('item_type', $itemType);
        }

        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        // Get latest price for each item
        $latestPrices = $query->select('item_type', 'item_id')
            ->groupBy('item_type', 'item_id')
            ->get()
            ->map(function ($item) {
                // Get the actual latest movement for this item
                $latestMovement = StockMovement::where('item_type', $item->item_type)
                    ->where('item_id', $item->item_id)
                    ->where('ref_type', 'purchase')
                    ->where('direction', 'in')
                    ->where('qty', '>', 0)
                    ->where('total_cost', '>', 0)
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
                
                if ($latestMovement) {
                    $item->latest_movement = $latestMovement;
                    $item->unit_price = $latestMovement->total_cost / $latestMovement->qty;
                    return $item;
                }
                return null;
            })
            ->filter();

        $this->info("📊 Found {$latestPrices->count()} items with purchase data");

        $updatedItems = 0;
        $updatedBomEntries = 0;

        foreach ($latestPrices as $priceData) {
            $latestMovement = $priceData->latest_movement;
            $newUnitPrice = $priceData->unit_price;

            $this->line("📦 Processing {$priceData->item_type} ID {$priceData->item_id}");
            $this->line("   💰 New unit price: Rp " . number_format($newUnitPrice, 2));

            if ($priceData->item_type === 'support') {
                $result = $this->updateBahanPendukungHpp($priceData->item_id, $newUnitPrice, $dryRun);
            } elseif ($priceData->item_type === 'material') {
                $result = $this->updateBahanBakuHpp($priceData->item_id, $newUnitPrice, $dryRun);
            } else {
                continue;
            }

            if ($result) {
                $updatedItems++;
                $updatedBomEntries += $result['bom_entries'];
                $this->line("   ✅ Updated {$result['bom_entries']} BOM entries");
            }
        }

        $this->info("🎉 Update complete!");
        $this->info("📈 Items processed: {$updatedItems}");
        $this->info("📋 BOM entries updated: {$updatedBomEntries}");

        if ($dryRun) {
            $this->warn('⚠️  This was a dry run - no actual changes were made');
        }
    }

    private function updateBahanPendukungHpp($bahanPendukungId, $newUnitPrice, $dryRun = false)
    {
        $bahanPendukung = BahanPendukung::find($bahanPendukungId);
        if (!$bahanPendukung) {
            $this->error("   ❌ Bahan Pendukung ID {$bahanPendukungId} not found");
            return null;
        }

        $this->line("   📝 {$bahanPendukung->nama_bahan}");
        $this->line("   🔄 Old price: Rp " . number_format($bahanPendukung->harga_satuan, 2));

        if (!$dryRun) {
            // Update master data
            $bahanPendukung->update(['harga_satuan' => $newUnitPrice]);
        }

        // Find BOM entries
        $bomEntries = BomJobBahanPendukung::where('bahan_pendukung_id', $bahanPendukungId)
            ->with(['bomJobCosting.produk'])
            ->get();

        $updatedCount = 0;

        foreach ($bomEntries as $bomEntry) {
            $hargaPerSatuanResep = $this->calculateRecipeUnitPrice(
                $newUnitPrice, 
                $bahanPendukung, 
                $bomEntry->satuan
            );

            $oldSubtotal = $bomEntry->subtotal;
            $newSubtotal = $bomEntry->jumlah * $hargaPerSatuanResep;

            $this->line("     🔸 BOM Entry ID {$bomEntry->id}: " . 
                       "Rp " . number_format($bomEntry->harga_satuan, 2) . 
                       " → Rp " . number_format($hargaPerSatuanResep, 2));

            if (!$dryRun) {
                $bomEntry->update([
                    'harga_satuan' => $hargaPerSatuanResep,
                    'subtotal' => $newSubtotal
                ]);
            }

            $updatedCount++;
        }

        return ['bom_entries' => $updatedCount];
    }

    private function updateBahanBakuHpp($bahanBakuId, $newUnitPrice, $dryRun = false)
    {
        $bahanBaku = BahanBaku::find($bahanBakuId);
        if (!$bahanBaku) {
            $this->error("   ❌ Bahan Baku ID {$bahanBakuId} not found");
            return null;
        }

        $this->line("   📝 {$bahanBaku->nama_bahan}");
        $this->line("   🔄 Old price: Rp " . number_format($bahanBaku->harga_satuan, 2));

        if (!$dryRun) {
            // Update master data
            $bahanBaku->update(['harga_satuan' => $newUnitPrice]);
        }

        // Find BOM entries
        $bomEntries = BomJobBbb::where('bahan_baku_id', $bahanBakuId)
            ->with(['bomJobCosting.produk'])
            ->get();

        $updatedCount = 0;

        foreach ($bomEntries as $bomEntry) {
            $hargaPerSatuanResep = $this->calculateRecipeUnitPrice(
                $newUnitPrice, 
                $bahanBaku, 
                $bomEntry->satuan
            );

            $oldSubtotal = $bomEntry->subtotal;
            $newSubtotal = $bomEntry->jumlah * $hargaPerSatuanResep;

            $this->line("     🔸 BOM Entry ID {$bomEntry->id}: " . 
                       "Rp " . number_format($bomEntry->harga_satuan, 2) . 
                       " → Rp " . number_format($hargaPerSatuanResep, 2));

            if (!$dryRun) {
                $bomEntry->update([
                    'harga_satuan' => $hargaPerSatuanResep,
                    'subtotal' => $newSubtotal
                ]);
            }

            $updatedCount++;
        }

        return ['bom_entries' => $updatedCount];
    }

    private function calculateRecipeUnitPrice($baseUnitPrice, $item, $recipeSatuan)
    {
        $satuanBase = is_object($item->satuan) ? $item->satuan->nama : ($item->satuan ?? 'unit');
        $satuanResep = $recipeSatuan ?: $satuanBase;

        // Handle common conversions
        if (strtolower($satuanBase) === 'liter' && strtolower($satuanResep) === 'mililiter') {
            return $baseUnitPrice / 1000;
        } elseif (strtolower($satuanBase) === 'kilogram' && strtolower($satuanResep) === 'gram') {
            return $baseUnitPrice / 1000;
        } elseif (strtolower($satuanBase) === 'ekor' && strtolower($satuanResep) === 'potong') {
            $conversionFactor = $item->sub_satuan_2_nilai ?? 6;
            return $baseUnitPrice / $conversionFactor;
        } elseif (strtolower($satuanBase) === 'bungkus' && strtolower($satuanResep) === 'gram') {
            $conversionFactor = $item->sub_satuan_1_nilai ?? 500;
            return $baseUnitPrice / $conversionFactor;
        } elseif (strtolower($satuanBase) === 'bungkus' && strtolower($satuanResep) === 'sendok teh') {
            $conversionFactor = $item->sub_satuan_2_nilai ?? 200;
            return $baseUnitPrice / $conversionFactor;
        }

        return $baseUnitPrice;
    }
}