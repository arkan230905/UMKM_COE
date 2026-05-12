<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BomJobBahanPendukung;
use App\Models\BomJobBbb;
use App\Models\BahanPendukung;
use App\Models\BahanBaku;
use App\Http\Controllers\LaporanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UpdateBomFromStockReport extends Command
{
    protected $signature = 'bom:update-from-stock-report {--item-type=} {--item-id=} {--dry-run}';
    protected $description = 'Update BOM prices based on final calculated prices from stock report logic';

    public function handle()
    {
        $itemType = $this->option('item-type');
        $itemId = $this->option('item-id');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🚀 Starting BOM update from stock report calculations...');

        // Get items to process
        $items = $this->getItemsToProcess($itemType, $itemId);
        
        $this->info("📊 Found {$items->count()} items to process");

        $updatedItems = 0;
        $updatedBomEntries = 0;

        foreach ($items as $item) {
            $this->line("📦 Processing {$item['type']} ID {$item['id']}: {$item['name']}");
            
            // Get final calculated price from stock report
            $finalPrice = $this->getFinalPriceFromStockReport($item['type'], $item['id']);
            
            if ($finalPrice === null) {
                $this->line("   ⚠️  No stock data found, skipping");
                continue;
            }

            $this->line("   💰 Final calculated price: Rp " . number_format($finalPrice, 2));

            // Update BOM entries
            if ($item['type'] === 'support') {
                $result = $this->updateBahanPendukungBom($item['id'], $finalPrice, $dryRun);
            } elseif ($item['type'] === 'material') {
                $result = $this->updateBahanBakuBom($item['id'], $finalPrice, $dryRun);
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

    private function getItemsToProcess($itemType, $itemId)
    {
        $items = collect();

        if ($itemType && $itemId) {
            // Process specific item
            if ($itemType === 'support') {
                $item = BahanPendukung::find($itemId);
                if ($item) {
                    $items->push([
                        'type' => 'support',
                        'id' => $item->id,
                        'name' => $item->nama_bahan
                    ]);
                }
            } elseif ($itemType === 'material') {
                $item = BahanBaku::find($itemId);
                if ($item) {
                    $items->push([
                        'type' => 'material',
                        'id' => $item->id,
                        'name' => $item->nama_bahan
                    ]);
                }
            }
        } else {
            // Process all items that have BOM entries
            $bahanPendukungIds = BomJobBahanPendukung::distinct()->pluck('bahan_pendukung_id');
            $bahanPendukungs = BahanPendukung::whereIn('id', $bahanPendukungIds)->get();
            foreach ($bahanPendukungs as $bp) {
                $items->push([
                    'type' => 'support',
                    'id' => $bp->id,
                    'name' => $bp->nama_bahan
                ]);
            }

            $bahanBakuIds = BomJobBbb::distinct()->pluck('bahan_baku_id');
            $bahanBakus = BahanBaku::whereIn('id', $bahanBakuIds)->get();
            foreach ($bahanBakus as $bb) {
                $items->push([
                    'type' => 'material',
                    'id' => $bb->id,
                    'name' => $bb->nama_bahan
                ]);
            }
        }

        return $items;
    }

    private function getFinalPriceFromStockReport($itemType, $itemId)
    {
        try {
            // Create a mock request to get stock report data
            $request = new Request([
                'tipe' => $itemType === 'support' ? 'bahan_pendukung' : 'material',
                'item_id' => $itemId
            ]);

            // Get stock report data using the same logic as LaporanController
            $controller = new LaporanController();
            
            // Use reflection to call the stok method
            $reflection = new \ReflectionClass($controller);
            $method = $reflection->getMethod('stok');
            $method->setAccessible(true);
            
            $response = $method->invoke($controller, $request);
            
            // Extract data from response
            $viewData = $response->getData();
            
            if (!isset($viewData['dailyStock']) || empty($viewData['dailyStock'])) {
                return null;
            }

            // Get the final calculated price from the last transaction
            $dailyStock = $viewData['dailyStock'];
            $lastTransaction = end($dailyStock);
            
            if ($lastTransaction && $lastTransaction['saldo_akhir_qty'] > 0) {
                // Return the final price per BASE UNIT from stock report
                $finalPricePerBaseUnit = $lastTransaction['saldo_akhir_nilai'] / $lastTransaction['saldo_akhir_qty'];
                
                // Get item info to know the base unit
                if ($itemType === 'support') {
                    $item = \App\Models\BahanPendukung::with('satuanRelation')->find($itemId);
                    $baseUnit = $item->satuanRelation->nama ?? 'unit';
                } else {
                    $item = \App\Models\BahanBaku::with('satuan')->find($itemId);
                    $baseUnit = $item->satuan->nama ?? 'unit';
                }
                
                Log::info("Stock Report Final Price", [
                    'item_id' => $itemId,
                    'item_type' => $itemType,
                    'final_price' => $finalPricePerBaseUnit,
                    'base_unit' => $baseUnit,
                    'qty' => $lastTransaction['saldo_akhir_qty'],
                    'nilai' => $lastTransaction['saldo_akhir_nilai']
                ]);
                
                return $finalPricePerBaseUnit;
            }

            return null;

        } catch (\Exception $e) {
            Log::error("Error getting stock report data: " . $e->getMessage());
            return null;
        }
    }

    private function updateBahanPendukungBom($bahanPendukungId, $finalPrice, $dryRun = false)
    {
        $bahanPendukung = BahanPendukung::find($bahanPendukungId);
        if (!$bahanPendukung) {
            $this->error("   ❌ Bahan Pendukung ID {$bahanPendukungId} not found");
            return null;
        }

        // Find BOM entries
        $bomEntries = BomJobBahanPendukung::where('bahan_pendukung_id', $bahanPendukungId)
            ->with(['bomJobCosting.produk'])
            ->get();

        $updatedCount = 0;

        foreach ($bomEntries as $bomEntry) {
            $hargaPerSatuanResep = $this->calculateRecipeUnitPrice(
                $finalPrice, 
                $bahanPendukung, 
                $bomEntry->satuan
            );

            $oldSubtotal = $bomEntry->subtotal;
            $newSubtotal = $bomEntry->jumlah * $hargaPerSatuanResep;

            $this->line("     🔸 BOM Entry ID {$bomEntry->id}: " . 
                       "Rp " . number_format($bomEntry->harga_satuan, 2) . 
                       " → Rp " . number_format($hargaPerSatuanResep, 2) .
                       " (Subtotal: Rp " . number_format($newSubtotal, 2) . ")");

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

    private function updateBahanBakuBom($bahanBakuId, $finalPrice, $dryRun = false)
    {
        $bahanBaku = BahanBaku::find($bahanBakuId);
        if (!$bahanBaku) {
            $this->error("   ❌ Bahan Baku ID {$bahanBakuId} not found");
            return null;
        }

        // Find BOM entries
        $bomEntries = BomJobBbb::where('bahan_baku_id', $bahanBakuId)
            ->with(['bomJobCosting.produk'])
            ->get();

        $updatedCount = 0;

        foreach ($bomEntries as $bomEntry) {
            $hargaPerSatuanResep = $this->calculateRecipeUnitPrice(
                $finalPrice, 
                $bahanBaku, 
                $bomEntry->satuan
            );

            $oldSubtotal = $bomEntry->subtotal;
            $newSubtotal = $bomEntry->jumlah * $hargaPerSatuanResep;

            $this->line("     🔸 BOM Entry ID {$bomEntry->id}: " . 
                       "Rp " . number_format($bomEntry->harga_satuan, 2) . 
                       " → Rp " . number_format($hargaPerSatuanResep, 2) .
                       " (Subtotal: Rp " . number_format($newSubtotal, 2) . ")");

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

        // Handle common conversions based on master data
        if (strtolower($satuanBase) === 'liter' && strtolower($satuanResep) === 'mililiter') {
            // Minyak Goreng: 1 liter = 1000 ml
            return $baseUnitPrice / 1000;
        } elseif (strtolower($satuanBase) === 'kilogram' && strtolower($satuanResep) === 'gram') {
            // Standard: 1 kg = 1000 gram
            return $baseUnitPrice / 1000;
        } elseif (strtolower($satuanBase) === 'ekor' && strtolower($satuanResep) === 'potong') {
            // Ayam Kampung: use sub_satuan_1_nilai (1 ekor = 6 potong)
            $conversionFactor = $item->sub_satuan_1_nilai ?? 6;
            return $baseUnitPrice / $conversionFactor;
        } elseif (strtolower($satuanBase) === 'bungkus' && strtolower($satuanResep) === 'sendok teh') {
            // Lada: use sub_satuan_2_nilai (1 bungkus = 100 sendok teh berdasarkan 0.01)
            $conversionValue = $item->sub_satuan_2_nilai ?? 0.01;
            $conversionFactor = 1 / $conversionValue; // 1 / 0.01 = 100
            return $baseUnitPrice / $conversionFactor;
        } elseif (strtolower($satuanBase) === 'kilogram' && strtolower($satuanResep) === 'sendok teh') {
            // Bubuk Bawang Putih: use sub_satuan_2_nilai (1 kg = 70 sendok teh)
            $conversionFactor = $item->sub_satuan_2_nilai ?? 200;
            return $baseUnitPrice / $conversionFactor;
        } elseif (strtolower($satuanBase) === 'bungkus' && strtolower($satuanResep) === 'gram') {
            // Standard bungkus to gram conversion
            $conversionFactor = $item->sub_satuan_1_nilai ?? 500;
            return $baseUnitPrice / $conversionFactor;
        }

        return $baseUnitPrice;
    }
}