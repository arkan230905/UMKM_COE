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

class FixBomFromActualStockReport extends Command
{
    protected $signature = 'bom:fix-from-actual-stock {--dry-run}';
    protected $description = 'Fix BOM prices using ACTUAL stock report final prices (ignore master data)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🚀 Fixing BOM from ACTUAL stock report prices...');

        // Get all BOM entries and fix them one by one
        $this->fixBahanPendukungBom($dryRun);
        $this->fixBahanBakuBom($dryRun);

        $this->info('🎉 BOM fix complete!');
    }

    private function fixBahanPendukungBom($dryRun)
    {
        $this->info("\n📦 FIXING BAHAN PENDUKUNG BOM...");
        
        $bomEntries = BomJobBahanPendukung::with([
            'bahanPendukung.satuanRelation',
            'bahanPendukung.subSatuan1',
            'bahanPendukung.subSatuan2'
        ])->get();
        
        foreach ($bomEntries as $bomEntry) {
            $bahanPendukung = $bomEntry->bahanPendukung;
            
            $this->line("🔧 Processing: {$bahanPendukung->nama_bahan}");
            
            // Get ACTUAL price from stock report
            $stockReportData = $this->getActualStockReportData('support', $bahanPendukung->id);
            
            if (!$stockReportData) {
                $this->line("   ❌ No stock data found, skipping");
                continue;
            }
            
            $baseUnit = $stockReportData['base_unit'];
            $basePricePerUnit = $stockReportData['final_price'];
            $recipeUnit = $bomEntry->satuan;
            
            $this->line("   📊 Stock Report: Rp " . number_format($basePricePerUnit, 2) . " per {$baseUnit}");
            $this->line("   📋 Recipe needs: {$bomEntry->jumlah} {$recipeUnit}");
            
            // Check if BOM unit needs correction
            $correctedUnit = $this->getCorrectedUnit($recipeUnit, $bahanPendukung);
            if ($correctedUnit !== $recipeUnit) {
                $this->line("   🔧 Unit correction: {$recipeUnit} → {$correctedUnit}");
                $recipeUnit = $correctedUnit;
            }
            
            // Calculate recipe unit price based on ACTUAL conversion
            $recipeUnitPrice = $this->calculateActualRecipePrice(
                $basePricePerUnit, 
                $baseUnit, 
                $recipeUnit, 
                $bahanPendukung
            );
            
            $newSubtotal = $bomEntry->jumlah * $recipeUnitPrice;
            
            $this->line("   🧮 Calculated: Rp " . number_format($recipeUnitPrice, 2) . " per {$recipeUnit}");
            $this->line("   💰 Old BOM: Rp " . number_format($bomEntry->harga_satuan, 2) . " per {$bomEntry->satuan}");
            $this->line("   💵 New Subtotal: Rp " . number_format($newSubtotal, 2));
            
            if (!$dryRun) {
                $bomEntry->update([
                    'satuan' => $recipeUnit, // Update unit if corrected
                    'harga_satuan' => $recipeUnitPrice,
                    'subtotal' => $newSubtotal
                ]);
                $this->line("   ✅ Updated!");
            } else {
                $this->line("   🔍 Would update (dry run)");
            }
            
            $this->line("");
        }
    }

    private function fixBahanBakuBom($dryRun)
    {
        $this->info("\n📦 FIXING BAHAN BAKU BOM...");
        
        $bomEntries = BomJobBbb::with('bahanBaku.satuan')->get();
        
        foreach ($bomEntries as $bomEntry) {
            $bahanBaku = $bomEntry->bahanBaku;
            
            $this->line("🔧 Processing: {$bahanBaku->nama_bahan}");
            
            // Get ACTUAL price from stock report
            $stockReportData = $this->getActualStockReportData('material', $bahanBaku->id);
            
            if (!$stockReportData) {
                $this->line("   ❌ No stock data found, skipping");
                continue;
            }
            
            $baseUnit = $stockReportData['base_unit'];
            $basePricePerUnit = $stockReportData['final_price'];
            $recipeUnit = $bomEntry->satuan;
            
            $this->line("   📊 Stock Report: Rp " . number_format($basePricePerUnit, 2) . " per {$baseUnit}");
            $this->line("   📋 Recipe needs: {$bomEntry->jumlah} {$recipeUnit}");
            
            // Calculate recipe unit price based on ACTUAL conversion
            $recipeUnitPrice = $this->calculateActualRecipePrice(
                $basePricePerUnit, 
                $baseUnit, 
                $recipeUnit, 
                $bahanBaku
            );
            
            $newSubtotal = $bomEntry->jumlah * $recipeUnitPrice;
            
            $this->line("   🧮 Calculated: Rp " . number_format($recipeUnitPrice, 2) . " per {$recipeUnit}");
            $this->line("   💰 Old BOM: Rp " . number_format($bomEntry->harga_satuan, 2) . " per {$recipeUnit}");
            $this->line("   💵 New Subtotal: Rp " . number_format($newSubtotal, 2));
            
            if (!$dryRun) {
                $bomEntry->update([
                    'harga_satuan' => $recipeUnitPrice,
                    'subtotal' => $newSubtotal
                ]);
                $this->line("   ✅ Updated!");
            } else {
                $this->line("   🔍 Would update (dry run)");
            }
            
            $this->line("");
        }
    }

    private function getActualStockReportData($itemType, $itemId)
    {
        try {
            $request = new Request([
                'tipe' => $itemType === 'support' ? 'bahan_pendukung' : 'material',
                'item_id' => $itemId
            ]);

            $controller = new LaporanController();
            $reflection = new \ReflectionClass($controller);
            $method = $reflection->getMethod('stok');
            $method->setAccessible(true);
            
            $response = $method->invoke($controller, $request);
            $viewData = $response->getData();
            
            if (!isset($viewData['dailyStock']) || empty($viewData['dailyStock'])) {
                return null;
            }

            $dailyStock = $viewData['dailyStock'];
            $lastTransaction = end($dailyStock);
            
            if ($lastTransaction && $lastTransaction['saldo_akhir_qty'] > 0) {
                $finalPrice = $lastTransaction['saldo_akhir_nilai'] / $lastTransaction['saldo_akhir_qty'];
                
                // Get base unit from item
                if ($itemType === 'support') {
                    $item = BahanPendukung::with('satuanRelation')->find($itemId);
                    $baseUnit = $item->satuanRelation->nama ?? 'unit';
                } else {
                    $item = BahanBaku::with('satuan')->find($itemId);
                    $baseUnit = $item->satuan->nama ?? 'unit';
                }
                
                return [
                    'final_price' => $finalPrice,
                    'base_unit' => $baseUnit,
                    'qty' => $lastTransaction['saldo_akhir_qty'],
                    'nilai' => $lastTransaction['saldo_akhir_nilai']
                ];
            }

            return null;

        } catch (\Exception $e) {
            $this->error("Error getting stock data for item {$itemId}: " . $e->getMessage());
            return null;
        }
    }

    private function getCorrectedUnit($currentUnit, $item)
    {
        $currentUnitLower = strtolower($currentUnit);
        
        // Get unit names from master data
        $satuanUtama = strtolower($item->satuanRelation->nama ?? '');
        $subSatuan1 = strtolower($item->subSatuan1->nama ?? '');
        $subSatuan2 = strtolower($item->subSatuan2->nama ?? '');
        
        // Special case: Bubuk Bawang Putih using "Sendok Teh" should use "Sendok Makan"
        if ($item->id == 21 && $currentUnitLower === 'sendok teh' && $subSatuan2 === 'sendok makan') {
            return 'Sendok Makan';
        }
        
        // Check if current unit exists in master data
        if ($currentUnitLower === $satuanUtama || 
            $currentUnitLower === $subSatuan1 || 
            $currentUnitLower === $subSatuan2) {
            return $currentUnit; // Unit is correct
        }
        
        // If unit doesn't match, suggest the most appropriate sub unit
        if (!empty($subSatuan1)) {
            return $item->subSatuan1->nama;
        } elseif (!empty($subSatuan2)) {
            return $item->subSatuan2->nama;
        }
        
        return $currentUnit; // Keep original if no better option
    }

    private function calculateActualRecipePrice($basePricePerUnit, $baseUnit, $recipeUnit, $item)
    {
        $baseUnitLower = strtolower($baseUnit);
        $recipeUnitLower = strtolower($recipeUnit);
        
        // Get all unit names from master data for precise matching
        $satuanUtama = strtolower($item->satuanRelation->nama ?? '');
        $subSatuan1 = strtolower($item->subSatuan1->nama ?? '');
        $subSatuan2 = strtolower($item->subSatuan2->nama ?? '');
        
        $this->line("   🔍 Unit Analysis:");
        $this->line("      Base Unit: {$baseUnit} (from stock report)");
        $this->line("      Recipe Unit: {$recipeUnit} (from BOM)");
        $this->line("      Master Units: {$satuanUtama} | {$subSatuan1} ({$item->sub_satuan_1_nilai}) | {$subSatuan2} ({$item->sub_satuan_2_nilai})");
        
        // Check if recipe unit matches any of the master data units
        if ($recipeUnitLower === $satuanUtama) {
            // Recipe uses base unit - no conversion needed
            $this->line("   ✅ Recipe unit matches base unit");
            return $basePricePerUnit;
        } elseif ($recipeUnitLower === $subSatuan1) {
            // Recipe uses sub satuan 1
            $conversionFactor = $item->sub_satuan_1_nilai;
            $this->line("   ✅ Recipe unit matches sub satuan 1, conversion: 1 {$baseUnit} = {$conversionFactor} {$recipeUnit}");
            return $basePricePerUnit / $conversionFactor;
        } elseif ($recipeUnitLower === $subSatuan2) {
            // Recipe uses sub satuan 2
            $conversionValue = $item->sub_satuan_2_nilai;
            if ($conversionValue < 1) {
                // If nilai < 1, it means 1 base unit = 1/nilai sub units
                $conversionFactor = 1 / $conversionValue;
                $this->line("   ✅ Recipe unit matches sub satuan 2, conversion: 1 {$baseUnit} = {$conversionFactor} {$recipeUnit}");
            } else {
                $conversionFactor = $conversionValue;
                $this->line("   ✅ Recipe unit matches sub satuan 2, conversion: 1 {$baseUnit} = {$conversionFactor} {$recipeUnit}");
            }
            return $basePricePerUnit / $conversionFactor;
        }
        
        // FALLBACK: Hard-coded conversions for common patterns
        if ($baseUnitLower === 'bungkus' && $recipeUnitLower === 'gram') {
            // Tepung: 1 bungkus = 500 gram (standard)
            $this->line("   🔧 Using fallback: 1 bungkus = 500 gram");
            return $basePricePerUnit / 500;
        } elseif ($baseUnitLower === 'liter' && $recipeUnitLower === 'mililiter') {
            // Liquid: 1 liter = 1000 ml
            $this->line("   🔧 Using fallback: 1 liter = 1000 ml");
            return $basePricePerUnit / 1000;
        } elseif ($baseUnitLower === 'ekor' && $recipeUnitLower === 'potong') {
            // Ayam: 1 ekor = 6 potong (from master data)
            $conversionFactor = $item->sub_satuan_1_nilai ?? 6;
            $this->line("   🔧 Using fallback: 1 ekor = {$conversionFactor} potong");
            return $basePricePerUnit / $conversionFactor;
        }
        
        // Default: no conversion
        $this->warn("   ⚠️  Unknown conversion: {$baseUnit} → {$recipeUnit}, using base price");
        return $basePricePerUnit;
    }
}